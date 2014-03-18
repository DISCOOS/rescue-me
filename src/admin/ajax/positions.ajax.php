<?php
ob_start();

use RescueMe\Missing;
use RescueMe\Properties;

$num = (int)$_GET['num'];
$missing = Missing::get((int)$_GET['id']);
$positions = $missing->getAjaxPositions($num);

$format = Properties::get(Properties::MAP_DEFAULT_FORMAT, $user_id);
foreach ($positions as $key=>$value) {
    $posText = format_pos($value, $format);
    $posTextClean = format_pos($value, $format, false);

    $arr = array('lat' => $value->lat, 'lon' => $value->lon, 'acc' => $value->acc,
                 'alt' => $value->alt, 'dtg' => format_dtg($value->timestamp),
                 'posText' => $posText, 'posTextClean' => $posTextClean,
                 'timeSince' => format_since($value->timestamp));
     
    echo json_encode($arr);
}
if (sizeof($positions) > 0) {
    return create_ajax_response(ob_get_clean());
}
else {
    return '';
} 
?>