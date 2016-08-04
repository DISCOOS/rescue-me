<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['num']) || strlen($_GET['num']) != 8)
    die('Ugyldig link!');

require_once('../config.php');
require_once(APP_PATH_INC.'common.inc.php');

$missing = new \RescueMe\Missing();
$m = $missing->getMissing($_GET['id'], $_GET['num']);
$m->addPosition($_GET['lat'], $_GET['lon'], $_GET['acc'], $_GET['alt'], time(), $_SERVER['HTTP_USER_AGENT']);

if ($_GET['acc'] > 500)
	die('Vi har funnet din posisjon med '.(int)$_GET['acc'].' m n&oslash;yaktighet.<br/>Vi er p&aring; vei, men pr&oslash;v &aring; gj&oring;r deg mest mulig synlig fra lufta og bakken!');

else 
	die('Vi har funnet din posisjon med '.(int)$_GET['acc'].' m n&oslash;yaktighet.<br/>Hold deg i ro, vi er p&aring; vei!<br />Pr&oslash;v &aring; gj&oslash;r deg mest mulig synlig fra lufta og bakken!');
?>