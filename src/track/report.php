<? 
require_once('../config.php');

use RescueMe\Missing;

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['phone'])) { 
     $response = 'Ugyldig link!';
} 
else {
    
    $m = Missing::getMissing($_GET['id'], $_GET['phone']);
    if($m !== false)
    {
        $acc = (int)$_GET['acc'];
        $m->addPosition($_GET['lat'], $_GET['lon'], $acc, $_GET['alt'], $_SERVER['HTTP_USER_AGENT']);
        $response  = "Vi har funnet din posisjon med $acc m n&oslash;yaktighet.<br/>";
        if ($_GET['acc'] > 500) {
            $response .= 'Vi er på vei, men pr&oslash;v å gj&oslash;re deg mest mulig synlig fra lufta og bakken!';
        } else {
            $response .= 'Hold deg i ro, vi er p&aring; vei!<br />Pr&oslash;v &aring; gj&oslash;re deg mest mulig synlig fra lufta og bakken!';
        }
    }
    else {
        $response = "Ugyldig id!";
    }
}
echo $response;
?>