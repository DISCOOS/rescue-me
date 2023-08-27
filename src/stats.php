<?

    use RescueMe\DB;

    require 'config.php';

    // Create connection
    $conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die("Connection failed: " . mysqli_connect_error());

    // Query stats
    if (!isset($_GET['type']) || !$_GET['type'] || 'all' === strtolower($_GET['type'])) {
        $types = array('trace', 'test', 'exercise');
    } else {
        $types = array($_GET['type']);
    }

    $days = (int)isset_get($_GET,'days', 90);
    $user_id = (int)isset_get($_GET,'user_id', 0);

    // Fetch statistics for given types
    foreach($types as $type) {
        $json[$type] = stats($type, $user_id, $days);
    }

    // Status - users
    $state = "active";
    $stmt = $conn->prepare('SELECT COUNT(*) FROM users where state = ?');
    $stmt->bind_param("s", $state) or die("Failed to bind param");
    $stmt->bind_result($users) or die ("Failed to bind result");
    $stmt->execute() or die("Failed to execute stmt");
    $stmt->fetch() or die("Failed to fetch data");
    $stmt->store_result() or die("Failed to store result");

    // Status - last successful trace
    $type = "trace";
    $select_max = 'SELECT MAX(positions.timestamp) 
                   FROM operations, missing, positions 
                   WHERE missing.missing_id = positions.missing_id AND missing.op_id = operations.op_id';
    if($user_id>0) {
        $stmt = $conn->prepare("$select_max AND op_type=? AND operations.user_id=?");
        $stmt->bind_param("si", $type, $user_id) or die("Failed to bind param");
    } else {
        $stmt = $conn->prepare("$select_max AND op_type=?");
        $stmt->bind_param("s", $type) or die("Failed to bind param");
    }
    $stmt->bind_result($timestamp) or die ("Failed to bind result");
    $stmt->execute() or die("Failed to execute stmt");
    $stmt->fetch() or die("Failed to fetch data");
    $stmt->store_result() or die("Failed to store result");


    $json["status"] = array(
        "timestamp" => date('c'),
        "active_users" => $users,
        "last_success" => date('c', strtotime($timestamp))
    );

    // Cleanup
    $stmt->close();
    $conn->close();


    // Response
    header("HTTP/1.1 200 OK");
    header("Content-Type: application/json");
    echo json_encode($json);


    /**
     * @param string $type
     * @param int $user_id
     * @param string $days
     * @return array
     */
    function stats($type, $user_id, $days)  {

        $op_type = 'op_type="'.DB::escape($type).'"';
        $params = $op_type;
        if($user_id>0) {
            $params .= ' AND user_id='.$user_id;
        }

        $rs = mysql_query_fetch(
            "call stats_get(NOW() + INTERVAL -$days DAY, '$params AND missing_answered IS NULL', null, null);"
        );
        $no_response = $rs[0]['sum'];

        $rs = mysql_query_fetch(
            "call stats_get(NOW() + INTERVAL -$days DAY, '$params AND missing_answered IS NOT NULL AND pos_id IS NULL', null, null);"
        );
        $no_location = $rs[0]['sum'];

        $rs = mysql_query_fetch(
            "call stats_get(NOW() + INTERVAL -$days DAY, '$params AND pos_id is NOT NULL', null, null);"
        );
        $located = $rs[0]['sum'];

        $rs = mysql_query_fetch(
            "call stats_get(NOW() + INTERVAL -$days DAY, '$params', null, null);"
        );
        $total = $rs[0]['sum'];

        // Calculate max number of traces that could lead to location
        $attainable = (int)$total - (int)$no_response;

        return array(
            "type" => $type,
            "days" => $days,
            "counts" => array(
                "no_response" => $no_response,
                "no_location" => $no_location,
                "located" => $located,
                "attainable" => $attainable,
                "total" => $total,
            ),
            "ratios" => array(
                "no_response" => fraction($no_response, $total),
                "no_location" => fraction($no_location, $total),
                "located" => fraction($located, $total),
                "attainable" => fraction($attainable, $total),
            )

        );

    }
