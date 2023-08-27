DELIMITER //
    DROP PROCEDURE IF EXISTS prepare_trend;
    CREATE PROCEDURE prepare_trend(IN t0 DATE)
    BEGIN
        DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
            ROLLBACK;
        END;

	    START TRANSACTION;

        SET @SQL = CONCAT('CREATE TABLE IF NOT EXISTS `trend_ts` (Ts DATE NOT NULL PRIMARY KEY);');
        PREPARE stmt FROM @SQL;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;

        SET @t0 = DATE(t0);
	    SET @tmin = (SELECT MIN(Ts) FROM trend_ts);
        IF (@tmin IS NULL) THEN
	        SET @tmin = DATE(NOW());
	    END IF;
	    -- SELECT @tmin AS "** DEBUG: @tmin";

	    IF (@t0 < @tmin) THEN
            SET @d = 0;
            SET @days = DATEDIFF(@tmin, t0);
            -- SELECT @days AS "** DEBUG: @days";
            REPEAT
                SET @SQL = CONCAT('INSERT INTO `trend_ts` (Ts) VALUES ("',@t0,'" + INTERVAL ',@d,' DAY);');
                PREPARE stmt FROM @SQL;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
                SET @d = @d + 1;
            UNTIL @d = @days END REPEAT;
	    END IF;

	    SET @tmax = (SELECT MAX(Ts) FROM trend_ts);
        IF (@tmax IS NULL) THEN
            SET @tmax = DATE(NOW());
        END IF;
        -- SELECT @tmax AS "** DEBUG: @tmax";

        SET @tail = DATEDIFF(NOW(), @tmax);
	    -- SELECT @tail AS "** DEBUG: @tail";

        IF (@tail > 0) THEN
            SET @d = 0;
            REPEAT
                 SET @d = @d + 1;
                 SET @SQL = CONCAT('INSERT INTO `trend_ts` (Ts) VALUES ("',@tmax,'" + INTERVAL ',@d,' DAY);');
                 PREPARE stmt FROM @SQL;
                 EXECUTE stmt;
                 DEALLOCATE PREPARE stmt;
            UNTIL @d = @tail END REPEAT;
        END IF;

	    COMMIT;
    END;

    DROP PROCEDURE IF EXISTS get_trend;
    CREATE PROCEDURE get_trend(IN t0 DATE, IN days INT, IN filter TEXT)
    BEGIN
        CALL prepare_trend(t0);

        SET @SQL = CONCAT('
        SELECT
            (@rn := @rn + 1) Row, IF(@rr = @n, @rr := 1, @rr := @rr + 1) AS Step,
            T.Date,
            T.Traced AS Daily,
            (IF(@rr = @n, @range := T.Traced, @range := @range + T.Traced)) AS Rng,
            (@total := @total + T.Traced) AS Total
        FROM (
             SELECT
                 Tr.Ts as Date,
                 COUNT(missing_reported) AS Traced
             FROM (
                SELECT Ts FROM trend_ts WHERE Ts >= "',t0,'"
             ) as Tr
             LEFT OUTER JOIN missing ON 
		DATE(missing.missing_reported) = Tr.Ts
             LEFT OUTER JOIN operations ON
		missing.op_id = operations.op_id
		AND ',filter,'
             GROUP BY Date
             ORDER BY Date
        ) AS T, (
             SELECT @rn:=0, @rr:=0, @total:=0, @range := 0, @n := ',days,'
        ) AS N;');
        PREPARE stmt FROM @SQL;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END;
//
