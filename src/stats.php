<?

require 'config.php';

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die("Connection failed: " . mysqli_connect_error());

// Query stats
if (!isset($_GET['type']) || !$_GET['type'] || 'all' === strtolower($_GET['type'])) {
	$types = array('trace', 'test', 'exercise');
} else {
	$types = array($_GET['type']);
}

$days = isset_get($_GET,'days');
$user_id = (int)isset_get($_GET,'user_id', 0);

// Fetch statistics for given types
foreach($types as $type) {
	$json[$type] = type_stats($conn, $type, $user_id, $days);
}


// Status - users
$state = "active";
$stmt = $conn->prepare("SELECT COUNT(*) FROM users where state = ?");
$stmt->bind_param("s", $state) or die("Failed to bind param");
$stmt->bind_result($users) or die ("Failed to bind result");
$stmt->execute() or die("Failed to execute stmt");
$stmt->fetch() or die("Failed to fetch data");
$stmt->store_result() or die("Failed to store result");

// Status - last successful trace
$type = "trace";
$select_max = "SELECT MAX(positions.timestamp) FROM operations, missing, positions WHERE missing.missing_id = positions.missing_id AND missing.op_id = operations.op_id";
if($user_id>0) {
	$stmt = $conn->prepare(
		"$select_max AND op_type=? AND operations.user_id=?"
	);
	$stmt->bind_param("si", $type, $user_id) or die("Failed to bind param");
} else {
	$stmt = $conn->prepare(
		"$select_max AND op_type=?"
	);
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
 * @param false|mysqli $conn
 * @param string $type
 * @param int $user_id
 * @param string $days
 * @return array
 */
function type_stats($conn, $type, $user_id, $days)  {
	$types = $user_id < 1 ? 's' : 'si';
	$values = $user_id < 1 ? array(&$type) : array(&$type, &$user_id);
	$params = $user_id < 1 ? "op_type=?" : "o.op_type=? AND o.user_id=?";
	$select = "SELECT COUNT(distinct m.missing_id) FROM missing m";
	$join = "INNER JOIN operations o on o.op_id = m.op_id LEFT OUTER JOIN positions p on p.missing_id = m.missing_id";
	$period = isset($days) ? "m.missing_reported > NOW() + INTERVAL -".(int)$days." DAY" : "";

	$no_response = stage_stats(
		$conn,
		"$select $join WHERE $period AND p.missing_id is NULL AND m.missing_answered IS NULL AND $params",
		$types,
		$values
	);
	$no_location = stage_stats(
		$conn,
		"$select $join WHERE $period AND p.missing_id is NULL AND m.missing_answered IS NOT NULL AND $params",
		$types,
		$values
	);
	$located = stage_stats(
		$conn,
		"$select $join WHERE $period AND p.missing_id is NOT NULL AND $params",
		$types,
		$values
	);
	$total = stage_stats(
		$conn,
		"$select $join WHERE $period AND $params",
		$types,
		$values
	);

	$attainable = array(
		"all" => $total['all']-$no_response['all'],
//		"unique" => $total['unique']-$no_response['unique'],
	);

	return array(
		"type" => $type,
		"days" => $days,
		"count" => array(
			"no_response" => $no_response,
			"no_location" => $no_location,
			"located" => $located,
			"attainable" => $attainable,
			"total" => $total,
		),
		"rates" => array(
			"no_response" => array(
				"all" => fraction($no_response['all'],$total['all']),
//				"unique" => fraction($no_response['unique'],$total['unique']),
			),
			"no_location" => array(
				"all" => fraction($no_location['all'],$total['all']),
//				"unique" => fraction($no_location['unique'],$total['unique']),
			),
			"located" => array(
				"all" => fraction($located['all'],$total['all']),
//				"unique" => fraction($located['unique'],$total['unique']),
			),
			"attainable" => array(
				"all" => fraction($attainable['all'],$total['all']),
//				"unique" => fraction($attainable['unique'],$total['unique']),
			),
		)

	);

}

function fraction($num, $denom) {
	return $denom>0 ? round($num / $denom, 4) : 0;
}

/**
 * @param false|mysqli $conn
 * @param string $select
 * @param string $types
 * @param mixed $values
 * @return array
 */
function stage_stats($conn, $select, $types, $values) {
    /*
    var_dump($select); echo "/n";
    var_dump($types); echo "/n";
    var_dump($values); echo "/n";
    die();
    */
	$stmt = $conn->prepare($select);

    array_unshift($values, $types);
    call_user_func_array(array($stmt, 'bind_param'), $values);
	$stmt->bind_result($traces) or die ("Failed to bind result");
	$stmt->execute() or die("Failed to execute stmt");
	$stmt->fetch() or die("Failed to fetch data");
	$stmt->store_result() or die("Failed to store result");
	$stmt->close();
	return array(
		"all" => $traces,
//		"unique" => $distinct,
	);
}
