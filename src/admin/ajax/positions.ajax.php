<?php
ob_start();

use RescueMe\User;
use RescueMe\Mobile;
use RescueMe\Properties;

$num = (int)$_GET['num'];
$user_id = User::currentId();
$mobile = Mobile::get((int)$_GET['id']);
$params = Properties::getAll($user_id);

// Close the session prematurely to avoid usleep() from locking other requests
session_write_close();
// If a user exits the page, make sure the webserver closes after a while
set_time_limit(120);
// Counter to manually keep track of time elapsed (PHP's set_time_limit() is unrealiable while sleeping)
$endtime = time() + 110;    
while(time() <= $endtime){
    $positions = $mobile->getAjaxPositions($num);

    if (sizeof($positions) > 0) {
        break;
    }
    // We don't run "live" against the DB - check every 1 sec
    // TODO: Config-value?
    usleep(1000000);
}
foreach ($positions as $key=>$value) {
    $posText = format_pos($value, $params);
    $posTextClean = format_pos($value, $params, false);

    $arr = array('lat' => $value->lat, 'lon' => $value->lon, 'acc' => $value->acc,
                 'alt' => $value->alt, 'posText' => $posText, 'posTextClean' => $posTextClean,
                 'timestamp' => format_tz($value->timestamp));

    echo json_encode($arr);
}
if (sizeof($positions) > 0) {
    return create_ajax_response(ob_get_clean());
}
else {
    return '';
} 
?>
