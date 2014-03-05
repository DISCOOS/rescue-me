<? 
require_once('../config.php');

use RescueMe\Missing;


$id = decrypt_id(input_get_hash('id'));
$acc = input_get_int('acc');
$lat = input_get_float('lat');
$lon = input_get_float('lon');
$alt = input_get_float('alt');
    
if ($id === false || $lat === false || $lon === false || $acc === false) { 
    
     $response = _('Illegal arguments');
     
} else {
    
    $m = Missing::getMissing($id);
    
    if($m !== false)
    {
        $m->addPosition($lat, $lon, $acc, $alt, $_SERVER['HTTP_USER_AGENT']);
        $response  = _("Din posisjon er mottatt (&#177;$acc m)").'<br/>';
        if ($acc > 500) {
            $response .= _('Hold deg i ro, vi er på vei. <br>Gjøre deg mest mulig synlig fra lufta og bakken!');
        } else {
            $response .= _('Hold deg i ro, vi er på vei. <br>Gjøre deg mest mulig synlig fra lufta og bakken!');
        }
    }
    else {
        $response = _('Unknown trace');
    }
}
echo $response;
?>