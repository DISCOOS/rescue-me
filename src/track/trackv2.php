<?php
require_once('../config.php');
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['num']) || strlen($_GET['num']) != 8)
	die('Ugyldig link!');
	
#############################
if(isset($_GET['forsok']) && (int)$_GET['forsok'] >= 10) { ?>
<html><head><title>Savnet</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
</head><body><h4>Klarte ikke Ã¥ posisjonere akkurat nÃ¥</h4>PÃ¥ denne siden vil det komme noen gode rÃ¥d</body></html>
<?php
die();
}
?>

<!DOCTYPE html>
<head><title>Savnet</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" /><script src="geo.js"></script><script><?php
	$js = file_get_contents('js/track.v2.js');
	echo str_replace(array('#ID','#NUM'), array($_GET['id'], $_GET['num']), $js);
?></script></head><body onLoad="getLocation();">
<div id="f">Beregner posisjon...</div>
</body></html>
