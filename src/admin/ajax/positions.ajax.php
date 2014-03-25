<?php
use RescueMe\Missing;
use RescueMe\Properties;

ob_start();
$num = (int)$_GET['num'];
$missing = Missing::get((int)$_GET['id']);
$format = Properties::get(Properties::MAP_DEFAULT_FORMAT, $user_id);

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
    $posText = format_pos($value, $format);
    $posTextClean = format_pos($value, $format, false);

    $arr = array('lat' => $value->lat, 'lon' => $value->lon, 'acc' => $value->acc,
                 'alt' => $value->alt, 'posText' => $posText, 'posTextClean' => $posTextClean,
                 'timestamp' => $value->timestamp);

    echo json_encode($arr);
}
if (sizeof($positions) > 0) {
    return create_ajax_response(ob_get_clean());
}
else {
    return '';
} 
?>