<? 
require_once('../config.php');

use RescueMe\Missing;

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['phone'])) { 
     $response = _('Ugyldig link!');
} 
else {
    
    $m = Missing::getMissing($_GET['id'], $_GET['phone']);
    if($m !== false)
    {
        $acc = (int)$_GET['acc'];
        $m->addPosition($_GET['lat'], $_GET['lon'], $acc, $_GET['alt'], $_SERVER['HTTP_USER_AGENT']);
        $response  = _("Din posisjon er mottatt (&#177;$acc m)").'<br/>';
        if ($_GET['acc'] > 500) {
            $response .= _('Hold deg i ro, vi er på vei. <br>Prøv å gjøre deg mest mulig synlig fra lufta og bakken!');
        } else {
            $response .= _('Hold deg i ro, vi er på vei. <br>Prøv å gjøre deg mest mulig synlig fra lufta og bakken!');
        }
    }
    else {
        $response = _('Ugyldig id!');
    }
}
echo $response;
?>