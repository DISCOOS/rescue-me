<?

    use RescueMe\DB;

    require 'config.php';

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
        $json[$type] = trends($type, $user_id, $days);
    }

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
    function trends($type, $user_id = 0, $days = 7)  {

        $params = 'op_type="'.DB::escape($type).'"';
        if($user_id>0) {
            $params .= ' AND user_id='.$user_id;
        }

        $no_response = mysql_query_fetch(
            "call trend_get(NOW() + INTERVAL -$days DAY, $days, '$params AND missing_answered IS NULL', null);"
        );

        $no_location = mysql_query_fetch(
            "call trend_get(NOW() + INTERVAL -$days DAY, $days, '$params AND missing_answered IS NOT NULL AND pos_id IS NULL', null);"
        );

        $located = mysql_query_fetch(
            "call trend_get(NOW() + INTERVAL -$days DAY, $days, '$params AND pos_id IS NOT NULL', null);"
        );

        $total = mysql_query_fetch(
            "call trend_get(NOW() + INTERVAL -$days DAY, $days, '$params', null);"
        );

        // Post-processing ratios
        $attainable = array();
        $ratios = array(
            "no_response" => array(),
            "no_location" => array(),
            "located" => array(),
            "attainable" => array(),
            "total" => array(),
        );
        $count = count($total);
        for ($i=0; $i < $count; $i++) {
            $t = $total[$i];
            $nr = $no_response[$i];
            $nl = $no_location[$i];
            $l = $located[$i];
            $date = $total[$i]['date'];
            $row = $total[$i]['row'];
            $step = $total[$i]['step'];
            $attainable[] = array(
                'date' => $date,
                'row' => (int)$row,
                'step' => (int)$step,
                'daily' => (int)($t['daily'] - $nr['daily']),
                'rng' => (int)($t['rng'] - $nr['rng']),
                'cum' => (int)($t['cum'] - $nr['cum']),
            );
            $ratios["no_response"][] = array(
                'date' => $date,
                'row' => (int)$row,
                'step' => (int)$step,
                "daily" => fraction($nr['daily'], $t['daily']),
                "rng" => fraction($nr['rng'], $t['rng']),
                "cum" => fraction($nr['cum'], $t['cum']),
            );
            $ratios["no_location"][] = array(
                'date' => $date,
                'row' => (int)$row,
                'step' => (int)$step,
                "daily" => fraction($nl['daily'], $t['daily']),
                "rng" => fraction($nl['rng'], $t['rng']),
                "cum" => fraction($nl['cum'], $t['cum']),
            );
            $ratios["located"][] = array(
                'date' => $date,
                'row' => (int)$row,
                'step' => (int)$step,
                "daily" => fraction($l['daily'], $t['daily']),
                "rng" => fraction($l['rng'], $t['rng']),
                "cum" => fraction($l['cum'], $t['cum']),
            );
            $ratios["attainable"][] = array(
                'date' => $date,
                'row' => (int)$row,
                'step' => (int)$step,
                "daily" => fraction($attainable[$i]['daily'], $t['daily']),
                "rng" => fraction($attainable[$i]['rng'], $t['rng']),
                "cum" => fraction($attainable[$i]['cum'], $t['cum']),
            );

        }

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
           "ratios" => $ratios,
        );

    }




