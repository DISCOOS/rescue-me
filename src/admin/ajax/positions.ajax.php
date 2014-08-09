<?php
ob_start();

use RescueMe\User;
use RescueMe\Missing;
use RescueMe\Properties;

$num = (int)$_GET['num'];
$user_id = User::currentId();
$missing = Missing::get((int)$_GET['id']);
$params = Properties::getAll($user_id);

// Close the session prematurely to avoid usleep() from locking other requests
session_write_close();
// If a user exits the page, make sure the webserver closes after a while
set_time_limit(120);
// Counter to manually keep track of time elapsed (PHP's set_time_limit() is unrealiable while sleeping)
$endtime = time() + 110;    
while(time() <= $endtime){
    $positions = $missing->getAjaxPositions($num);

    if (sizeof($positions) > 0) {
        break;
    }
    // We don't run "live" against the DB - check every 3 sec
    // TODO: Config-value?
    usleep(3000);
}
foreach ($positions as $key=>$value) {
    $posText = format_pos($value, $params);
    $posTextClean = format_pos($value, $params, false);

    $arr = array('lat' => $value->lat, 'lon' => $value->lon, 'acc' => $value->acc,
                 'alt' => $value->alt, 'posText' => $posText, 'posTextClean' => $posTextClean,
                 'timestamp' => $value->timestamp.\RescueMe\TimeZone::getOffset());

    echo json_encode($arr);
}
if (sizeof($positions) > 0) {
    return create_ajax_response(ob_get_clean());
}
else {
    return '';
} 
?>
