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
        $json[$type] = rank($type, $user_id, $days);
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
    function rank($type, $user_id, $days)  {
        $op_type = 'op_type="'.DB::escape($type).'"';
        $rs_total = mysql_query_fetch(
            "call stats_get(NOW() + INTERVAL -$days DAY, '$op_type', 'user_id', null);"
        );
        $rs_located = mysql_query_fetch(
            "call stats_get(NOW() + INTERVAL -$days DAY, '$op_type AND pos_id is NOT NULL', 'user_id', null);"
        );
        $max = 0;
        $total = array();
        $located = array();
        for ($i=0; $i < count($rs_total); $i++) {
            $total[$rs_total[$i]['user_id']] = array('sum' => $rs_total[$i]['sum']);
        }
        for ($i=0; $i < count($rs_located); $i++) {
            $sum = $rs_located[$i]['sum'];
            $located[$rs_located[$i]['user_id']] = array('sum' => $sum);
            $max = max($max, $sum);
        }

        $ranks = array();
        foreach ($total as $user_id => $stats) {
            $sum = isset($located[$user_id])
                ? isset_get($located[$user_id],'sum',0)
                : 0;
            $experience = $max > 0 ? $sum / $max : 0;
            $success = fraction($sum, $stats['sum']);
            $performance = $success * $experience;
            $ranks[] = array(
                // for sorting only
                $user_id => $performance,
                "user_id" => $user_id,
                "total" => $stats['sum'],
                "located" => $sum,
                "success" => $success,
                "experience" => $experience,
                "performance" => $performance,
            );
        }

        $keys = array_map(function($v){ return reset($v); }, $ranks);
        array_multisort($keys, SORT_DESC, $ranks);

        $i = 0;
        $users = array();
        $count = count($ranks)-1;
        foreach ($ranks as $rank) {
            $i++;
            $user_id = $rank["user_id"];
            $users[$user_id] = array(
                "user_id" => $user_id,
                "rank" => $i,
                "total" => $rank["total"],
                "located" => $rank["located"],
                "success" => $rank["success"],
                "experience" => $rank["experience"],
                "performance" => $rank["performance"],
                "better" => $count > 0 ? ($count - $i + 1) / $count : 0,
            );
        }
        $best = reset($users);
        return array(
            "users" => $users,
            "count" => $count,
            "best" => $best['user_id'],
        );

    }