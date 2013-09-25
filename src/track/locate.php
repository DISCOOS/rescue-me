<!DOCTYPE html>
<? 
    
    require_once('../config.php'); 
    
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;

?>

<html><head><title><?=TITLE?></title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
        
<? if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['phone'])) { ?>
        
</head><body><?=insert_error('Ugyldig link!');?></body>

<? } else if(isset($_GET['attempt']) && (int)$_GET['attempt'] >= 10) { ?>

</head><body><h4>Klarte ikke å posisjonere akkurat nå</h4>På denne siden vil det komme noen gode råd</body>

<? 
    
} else { 
    
    $missing = Missing::getMissing($_GET['id'], $_GET['phone']);
    
    if($missing !== false) {
    
        $id = Operation::getOperation($missing->op_id)->user_id;
        $age = Properties::get(Properties::LOCATION_MAX_AGE, $id);
        $wait = Properties::get(Properties::LOCATION_MAX_WAIT, $id);
        $desiredAcc = Properties::get(Properties::LOCATION_DESIRED_ACC, $id);
        
        $missing->answered();
        
?>

<script id="track" src="<?=APP_URI?>js/track.js?id=<?=$_GET['id']?>&phone=<?=$_GET['phone']?>&wait=<?=$wait?>&age=<?=$age?>&desiredAcc=<?=$desiredAcc?>"></script></head>
<body onLoad="R.track.locate();"><div id="feedback">Beregner posisjon...</div><span id="img"></span><br /><span id="sec"></span></body>

<? } else { ?>

<? insert_alert(_("Missing not found")) ?>

<? }} ?>
</html>
