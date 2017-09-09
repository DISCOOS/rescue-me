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

// Fetch statistics for given types
foreach($types as $type) {
	$json[$type] = type_stats($type, $conn);
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
$stmt = $conn->prepare("SELECT MAX(positions.timestamp) FROM traces, mobiles, positions WHERE mobiles.mobile_id = positions.mobile_id AND mobiles.trace_id = traces.trace_id AND trace_type=?");
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


function type_stats($type, $conn)  {
	$no_response = stage_stats($type, $conn, "SELECT COUNT(distinct mobile_number), COUNT(*) FROM mobiles, traces WHERE mobiles.trace_id=traces.trace_id AND NOT EXISTS (SELECT * FROM positions WHERE mobiles.mobile_id = positions.mobile_id) AND mobile_responded IS NULL AND trace_type=?");
	$no_location = stage_stats($type, $conn, "SELECT COUNT(distinct mobile_number), COUNT(*) FROM mobiles, traces WHERE mobiles.trace_id=traces.trace_id AND NOT EXISTS (SELECT * FROM positions WHERE mobiles.mobile_id = positions.mobile_id) AND mobile_responded IS NOT NULL AND trace_type=?");
	$located = stage_stats($type, $conn, "SELECT COUNT(distinct mobile_number), COUNT(*) FROM mobiles, traces WHERE EXISTS(SELECT * FROM positions WHERE mobiles.mobile_id = positions.mobile_id) AND mobiles.trace_id = traces.trace_id AND trace_type=?");
	$totals = stage_stats($type, $conn, "SELECT COUNT(distinct mobile_number), COUNT(*) FROM mobiles, traces WHERE mobiles.trace_id = traces.trace_id AND trace_type=?");

	return array(
	        "type" => $type,
	        "no_response" => $no_response,
	        "no_location" => $no_location,
	        "located" => $located,
	        "totals" => $totals,
		"rates" => array(
			"total" => array(
				"all" => round($located['all']/$totals['all'],4),
				//"unique" => round($located['unique']/$totals['unique'],4),
			),
			"attainable" => array(
                                "all" => round($located['all']/($totals['all']-$no_response['all']),4),
                                //"unique" => round($located['unique']/($totals['unique']-$no_response['unique']),4),
                        )
		)

	);

}

function stage_stats($type, $conn, $select) {

	$stmt = $conn->prepare($select);
	$stmt->bind_param("s", $type);
	$stmt->bind_result($distinct, $traces) or die ("Failed to bind result");
	$stmt->execute() or die("Failed to execute stmt");
	$stmt->fetch() or die("Failed to fetch data");
	$stmt->store_result() or die("Failed to store result");
	$stmt->close();
	return  array(
        	"all" => $traces,
        	//"unique" => $distinct
	);
}



?>

