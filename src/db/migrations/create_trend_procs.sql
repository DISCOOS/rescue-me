DELIMITER //

    -- Create trend tables
    -- ===================

    CREATE TABLE IF NOT EXISTS trend_ts (
        date DATE NOT NULL PRIMARY KEY
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS trends (
        trend_id INT(6) NOT NULL AUTO_INCREMENT,
        hash CHAR(32) NOT NULL,
        t0 DATE NOT NULL,
        head DATE,
        filter TEXT NOT NULL,
        PRIMARY KEY (trend_id),
        UNIQUE KEY hash (hash)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- Create trend update triggers
    -- ============================
    -- Following events marks trend outdated:
    -- 1. On insert {missing, position} (happens after operation is inserted)
    -- 2. On update {missing, operation, position} (any change will do)

    DROP TRIGGER IF EXISTS trend_on_tim;
    CREATE TRIGGER trend_on_tim
    AFTER INSERT ON missing FOR EACH ROW
    BEGIN
        UPDATE trends SET head = NEW.missing_reported;
        call trend_log(
            CONCAT('[Trigger]: missing:', NEW.missing_id, '@', NEW.missing_reported, ' in operation ', NEW.op_id, ' created'),
            null
        );
    END;

    DROP TRIGGER IF EXISTS trend_on_tum;
    CREATE TRIGGER trend_on_tum
    AFTER UPDATE ON missing FOR EACH ROW
    BEGIN
        UPDATE trends SET head = NEW.missing_reported;
        call trend_log(
            CONCAT('[Trigger]: missing:', NEW.missing_id, '@', NEW.missing_reported, ' in operation ', NEW.op_id, ' updated'),
            null
        );
    END;

    DROP TRIGGER IF EXISTS trend_on_tuo;
    CREATE TRIGGER trend_on_tuo
    AFTER UPDATE ON operations FOR EACH ROW
    BEGIN
        UPDATE trends SET head = NEW.op_opened;
        call trend_log(
            CONCAT('[Trigger]: operation:', NEW.op_id, '@', NEW.op_opened, ' updated'),
            null
        );
    END;

    DROP TRIGGER IF EXISTS trend_on_tip;
    CREATE TRIGGER trend_on_tip
    AFTER INSERT ON positions FOR EACH ROW
    BEGIN
        UPDATE trends SET head = NEW.timestamp;
        call trend_log(
            CONCAT('[Trigger]: position:', NEW.pos_id, '@', NEW.timestamp, ' created for missing ', NEW.missing_id),
            null
        );
    END;

    DROP TRIGGER IF EXISTS trend_on_tup;
    CREATE TRIGGER trend_on_tup
    AFTER UPDATE ON positions FOR EACH ROW
    BEGIN
        UPDATE trends SET head = NEW.timestamp;
        call trend_log(
            CONCAT('[Trigger]: position:', NEW.pos_id, '@', NEW.timestamp, ' updated for missing ', NEW.missing_id),
            null
        );
    END;

    -- Create stored procedures for trends
    -- ===================================

    DROP PROCEDURE IF EXISTS execute_stmt;
    CREATE PROCEDURE execute_stmt(IN vsql TEXT)
    BEGIN
        SET @SQL = vsql;
        PREPARE stmt FROM @SQL;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END;

    DROP PROCEDURE IF EXISTS create_view;
    CREATE PROCEDURE create_view(IN vname TEXT, IN vselect TEXT)
    BEGIN
        SET @name = vname;
        CALL execute_stmt(
            CONCAT(
                'CREATE OR REPLACE ALGORITHM = TEMPTABLE VIEW ', vname, ' AS (', vselect, ');'
            )
        );
    END;

    DROP FUNCTION IF EXISTS trend_escape;
    CREATE FUNCTION trend_escape(unsafe TEXT) RETURNS TEXT DETERMINISTIC NO SQL
    BEGIN
        RETURN REPLACE(unsafe, '"', '''');
    END;

    DROP FUNCTION IF EXISTS trend_hash;
    CREATE FUNCTION trend_hash(filter TEXT) RETURNS CHAR(32) DETERMINISTIC NO SQL
    BEGIN
        RETURN MD5(CONCAT('_', trend_escape(filter)));
    END;

    DROP FUNCTION IF EXISTS trend_name;
    CREATE FUNCTION trend_name(prefix CHAR(2), filter TEXT) RETURNS TEXT DETERMINISTIC NO SQL
    BEGIN
        SET @hash = trend_hash(filter);
        RETURN CONCAT('trend_', prefix, '_', @hash);
    END;

    DROP PROCEDURE IF EXISTS trend_prepare;
    CREATE PROCEDURE trend_prepare(IN t0 DATE, IN filter TEXT)
    BEGIN

        DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
            SHOW ERRORS;
            ROLLBACK;
        END;

        START TRANSACTION;

        -- Ensure timestamps exits in range [t0, NOW()]
        SET @t0 = t0;
        SET @tmin = (SELECT MIN(date) FROM trend_ts);
        IF (@tmin IS NULL) THEN
            SET @tmin = DATE(NOW());
        END IF;

        SET @head = 0;
        IF (@t0 < @tmin) THEN
            SET @d = 0;
            SET @head = DATEDIFF(@tmin, @t0);
            REPEAT
                INSERT INTO `trend_ts` (date) VALUES (@t0 + INTERVAL @d DAY);
                SET @d = @d + 1;
            UNTIL @d = @head END REPEAT;
        END IF;

        SET @tmax = (SELECT MAX(date) FROM trend_ts);
        IF (@tmax IS NULL) THEN
            SET @tmax = DATE(NOW());
        END IF;

        SET @tail = DATEDIFF(NOW(), @tmax);
        IF (@tail > 0) THEN
            SET @d = 0;
            REPEAT
                SET @d = @d + 1;
                INSERT INTO `trend_ts` (date) VALUES (@tmax + INTERVAL @d DAY);
            UNTIL @d = @tail END REPEAT;
        END IF;
        COMMIT;

        -- Prepare filtered views
        SET @hash = trend_hash(filter);
        SET @filter = trend_escape(filter);
        SET @trend_mv = trend_name('mv', @filter);
        SET @trend_dv = trend_name('dv', @filter);

        -- Prepare unfiltered views
        CALL create_view(
            @trend_dv,
            CONCAT('
                SELECT
                    m.missing_id as missing_id,
                    m.missing_reported as missing_reported,
                    m.missing_answered as missing_answered,
                    m.missing_locale as missing_locale,
                    m.last_error_code as last_error_code,
                    o.op_id as op_id,
                    o.user_id as user_id,
                    o.op_type as op_type,
                    o.op_closed as op_closed,
                    p.pos_id as pos_id,
                    p.lat as lat,
                    p.lon as lon,
                    p.alt as alt,
                    p.acc as acc
                FROM missing as m
                INNER JOIN operations as o
                    ON m.op_id = o.op_id
                LEFT OUTER JOIN positions as p
                    ON m.missing_id = p.missing_id '
                , IF(filter IS NOT NULL, CONCAT(' WHERE ', filter), ''), ''
            )
        );

        -- Prepare materialized views
        CALL execute_stmt(
            CONCAT('
                CREATE TABLE IF NOT EXISTS ', @trend_mv, ' (
                    date DATE PRIMARY KEY,
                    daily INT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            )
        );

        -- Update materialized view (trend_mv)
        CALL trend_update(@t0, @filter);
    END;

    DROP PROCEDURE IF EXISTS trend_update;
    CREATE PROCEDURE trend_update(IN t0 DATE, IN filter TEXT)
    BEGIN

        DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
            SHOW ERRORS;
            ROLLBACK;
        END;

        START TRANSACTION;

        SET @t0 = t0;
        SET @head = @t0;
        SET @date = @t0;

        -- Get trend names
        SET @hash = trend_hash(filter);
        SET @filter = trend_escape(filter);
        SET @trend_mv = trend_name('mv', @filter);
        SET @trend_dv = trend_name('dv', @filter);

        -- Clamp t0 to head
        SELECT head INTO @head FROM trends WHERE hash = @hash;
        IF (@head < @t0) THEN
            -- Start from (head - 1) if head < t0 to update from head
            SET @t0 = @head + INTERVAL - 1 DAY;
        END IF;

        CALL execute_stmt(
            CONCAT('
                INSERT INTO ', @trend_mv, ' SELECT
                    T.date AS date,
                    T.traced AS daily
                FROM (
                    SELECT
                        Tr.date as date,
                        COUNT(DISTINCT M.missing_id) AS traced
                    FROM trend_ts AS Tr
                    LEFT OUTER JOIN ', @trend_dv, ' AS M
                    ON DATE(M.missing_reported) = Tr.date
                    WHERE Tr.date > "', t0, '"
                    GROUP BY date
                    ORDER BY date
                ) AS T ON DUPLICATE KEY UPDATE
                    date = VALUES(date),
                    daily = VALUES(daily);'
            )
        );


        -- Update trend metadata
        CALL trend_update_meta(@hash, @t0, @filter);

        call trend_log(
            CONCAT('[trend_update]: ', @hash, ' with filter [', filter, '] from t0 [', @t0, ']'),
            CONCAT('{"t0": "', @t0, '", "head": "', @head, ', "filter": "', filter, '"}')
        );

        COMMIT;

    END;

    DROP PROCEDURE IF EXISTS trend_log;
    CREATE PROCEDURE trend_log(IN message TEXT, IN context LONGTEXT)
    BEGIN
        INSERT IGNORE INTO logs (name, date, level, message, user_id, context)
        VALUES ('insights', NOW(), 'info', message, 0, context);
    END;

    DROP PROCEDURE IF EXISTS trend_update_meta;
    CREATE PROCEDURE trend_update_meta(IN hash CHAR(32), IN t0 DATE, IN filter TEXT)
    BEGIN
        -- Update trend metadata
        INSERT INTO trends (hash, t0, head, filter)
        VALUES (hash, t0, null, filter)
        ON DUPLICATE KEY UPDATE
            hash = VALUES(hash),
            t0 = VALUES(t0),
            head = VALUES(head),
            filter = VALUES(filter);
    END;

    DROP PROCEDURE IF EXISTS trend_checked_prepare;
    CREATE PROCEDURE trend_checked_prepare(IN t0 DATE, IN filter TEXT)
    BEGIN
        DECLARE tableCount INT;

        SET @t0 = t0;
        SET @hash = trend_hash(filter);
        SET @trend_view = trend_name('mv', filter);

        -- Check if trend update is needed
        SELECT COUNT(*) INTO tableCount
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @trend_view;

        -- Prepare trend if not exist
        IF (tableCount = 0) THEN
            CALL trend_prepare(t0, filter);
        ELSE
            -- Prepare trend if head exists for given hash (change has occurred since last update)
            SET @head = null;

            -- Execute as dynamic sql as workaround for column name equal to procedure parameter t0
            CALL execute_stmt(
                'SELECT t0, head INTO @t0, @head FROM trends WHERE hash = @hash LIMIT 1;'
            );

            IF (@head IS NOT NULL OR t0 < @t0) THEN
                CALL trend_prepare(t0, filter);
            END IF;

        END IF;

    END;

    DROP PROCEDURE IF EXISTS trend_get;
    CREATE PROCEDURE trend_get(IN t0 DATE, IN days INT, IN filter TEXT, IN vlimit INT)
    BEGIN

        CALL trend_checked_prepare(t0, filter);

        SET @t0 = t0;
        SET @days = days;
        SET @hash = trend_hash(filter);
        SET @trend_mv = trend_name('mv', filter);

        -- Reset session variables
        SET @row = 0;
        SET @step = 0;
        SET @rng = 0;
        SET @cum = 0;


        CALL execute_stmt(
            CONCAT('
                SELECT
                    date,
                    @row := @row + 1 AS row,
                    IF(@step % @days = 0, @step := 1, @step := @step + 1) AS step,
                    daily,
                    IF(@row % @days = 0, @rng := daily, @rng := @rng + daily) AS rng,
                    @cum := @cum + daily as cum
                FROM ', @trend_mv, '
                WHERE date > "', @t0, '"
                ', IF(vlimit IS NOT NULL, CONCAT('LIMIT ', vlimit),''), ';'
            )
        );
    END;

    DROP PROCEDURE IF EXISTS stats_get;
    CREATE PROCEDURE stats_get(IN t0 DATE, IN filter TEXT, IN group_by TEXT, IN order_by TEXT)
    BEGIN
        CALL trend_checked_prepare(t0, filter);

        SET @t0 = t0;
        SET @hash = trend_hash(filter);
        SET @trend_dv = trend_name('dv', filter);

        CALL execute_stmt(
            CONCAT('
                SELECT
                    ', IF(group_by IS NOT NULL, CONCAT('C.', group_by, ' AS ', group_by, ','), ''),'
                    COUNT(*) as rows,
                    COUNT(DISTINCT date) as days,
                    COUNT(DISTINCT user_id) as users,
                    SUM(traced) as sum,
                    MAX(traced) as max,
                    MIN(traced) as min,
                    AVG(traced) as avg,
                    STD(traced) as std,
                    STDDEV_POP(traced) as std_pop,
                    STDDEV_SAMP(traced) as std_samp,
                    VARIANCE(traced) as var,
                    VAR_POP(traced) as var_pop,
                    VAR_SAMP(traced) as var_samp
                FROM (
                    SELECT
                        Tr.date as date,
                        M.user_id as user_id,
                        COUNT(DISTINCT M.missing_id) AS traced
                    FROM trend_ts AS Tr
                    LEFT OUTER JOIN ', @trend_dv, ' AS M
                    ON DATE(missing_reported) = date
                    WHERE date > "', @t0, '"
                    GROUP BY date ', IF(group_by IS NOT NULL, CONCAT(', ', group_by), ''),'
                    ORDER BY date
                ) AS C
                ', IF(group_by IS NOT NULL, CONCAT('GROUP BY ', group_by), ''),'
                ', IF(order_by IS NOT NULL, CONCAT('ORDER BY ', order_by), ''),';'
            )
        );

    END;

    DROP PROCEDURE IF EXISTS trend_list;
    CREATE PROCEDURE trend_list()
    BEGIN
        SELECT * FROM trends;
    END;

    DROP PROCEDURE IF EXISTS trend_drop;
    CREATE PROCEDURE trend_drop(IN filter TEXT)
    BEGIN
        SET @hash = trend_hash(filter);
        CALL trend_drop_hash(@hash);
    END;

    DROP PROCEDURE IF EXISTS trend_drop_hash;
    CREATE PROCEDURE trend_drop_hash(IN hash CHAR(32))
    BEGIN
        CALL execute_stmt(
            CONCAT('
                DROP VIEW IF EXISTS trend_dv_', hash,';'
            )
        );
        CALL execute_stmt(
            CONCAT('
                DROP TABLE IF EXISTS trend_mv_', hash,';'
            )
        );
        CALL execute_stmt(
            CONCAT('
                DELETE FROM trends WHERE hash="', hash,'";'
            )
        );
    END;


//