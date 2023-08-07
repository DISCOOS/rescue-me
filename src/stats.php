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
$debug = isset_get($_GET,'debug') !==null;

// Fetch statistics for given types
foreach($types as $type) {
	$json[$type] = type_stats($conn, $type, $days, $debug);
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
$stmt = $conn->prepare("SELECT MAX(positions.timestamp) FROM operations, missing, positions WHERE missing.missing_id = positions.missing_id AND missing.op_id = operations.op_id AND op_type=?");
$stmt->bind_param("s", $type) or die("Failed to bind param");
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
 * @param string $days
 * @param bool $debug
 * @return array
 */
function type_stats($conn, $type, $days, $debug)  {
	$period = isset($days) ? "AND `missing_reported` > NOW() + INTERVAL -".(int)$days." DAY" : "";
	$select_m = "SELECT COUNT(distinct missing_mobile), COUNT(*) FROM missing, operations WHERE missing.op_id=operations.op_id";
	$select_p = "SELECT * FROM positions WHERE missing.missing_id = positions.missing_id";
	$no_response = stage_stats(
		$conn,
		"$select_m $period AND NOT EXISTS ($select_p) AND missing_answered IS NULL AND op_type=?",
		$type,
		$debug
	);
	$no_location = stage_stats(
		$conn,
		"$select_m $period AND NOT EXISTS ($select_p) AND missing_answered IS NOT NULL AND op_type=?",
		$type,
		$debug
	);
	$located = stage_stats(
		$conn,
		"$select_m $period AND EXISTS($select_p) AND op_type=?",
		$type,
		$debug
	);
	$total = stage_stats(
		$conn,
		"$select_m $period AND op_type=?",
		$type,
		$debug
	);

	$attainable = array(
		"all" => $total['all']-$no_response['all'],
		"unique" => $total['unique']-$no_response['unique'],
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
				"unique" => fraction($no_response['unique'],$total['unique']),
			),
			"no_location" => array(
				"all" => fraction($no_location['all'],$total['all']),
				"unique" => fraction($no_location['unique'],$total['unique']),
			),
			"located" => array(
				"all" => fraction($located['all'],$total['all']),
				"unique" => fraction($located['unique'],$total['unique']),
			),
			"attainable" => array(
				"all" => fraction($located['all'],$attainable['all']),
				"unique" => fraction($located['unique'],$attainable['unique']),
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
 * @param string $type
 * @param bool $debug
 * @return array
 */
function stage_stats($conn, $select, $type, $debug) {
	$stmt = $conn->prepare($select);
	$stmt->bind_param("s", $type);
	$stmt->bind_result($distinct, $traces) or die ("Failed to bind result");
	$stmt->execute() or die("Failed to execute stmt");
	$stmt->fetch() or die("Failed to fetch data");
	$stmt->store_result() or die("Failed to store result");
	$stmt->close();
	$result = array(
		"all" => $traces,
		"unique" => $distinct,
	);
	if($debug) {
		$result["query"] = $select;
	}
	return $result;
}



?>

