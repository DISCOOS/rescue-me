<? 
require_once('../config.php');

use RescueMe\Missing;

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['phone']) || strlen($_GET['phone']) != 8) { 
     $response = 'Ugyldig link!';
} 
else {
    
    $m = Missing::getMissing($_GET['id'], $_GET['phone']);
    if($m !== false)
    {
        $acc = (int)$_GET['acc'];
        $m->addPosition($_GET['lat'], $_GET['lon'], $acc, $_GET['alt'], time(), $_SERVER['HTTP_USER_AGENT']);
        $response  = "Vi har funnet din posisjon med $acc nøyaktighet.<br/>";
        if ($_GET['acc'] > 500) {
            $response .= 'Vi er på vei, men prøv å gjøre deg mest mulig synlig fra lufta og bakken!';
        } else {
            $response .= 'Hold deg i ro, vi er på vei!<br />Prøv å gjøre deg mest mulig synlig fra lufta og bakken!';
        }
    }
    else {
        $response = "Ugyldig id!";
    }
}
insert_message($response);
?>