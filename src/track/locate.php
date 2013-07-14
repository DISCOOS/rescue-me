<!DOCTYPE html>
<? require_once('../config.php'); ?>

<html><head><title><?=TITLE?></title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
        
<? if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['phone']) || strlen($_GET['phone']) != 8) { ?>
        
</head><body><?=insert_error('Ugyldig link!');?></body>

<? } else if(isset($_GET['attempt']) && (int)$_GET['attempt'] >= 10) { ?>

</head><body><h4>Klarte ikke å posisjonere akkurat nå</h4>På denne siden vil det komme noen gode råd</body>

<? } else { ?>

<script id="track" src="<?=APP_URI?>js/track.js?id=<?=$_GET['id']?>&phone=<?=$_GET['phone']?>"></script></head>
<body onLoad="R.track.locate();"><div id="feedback">Beregner posisjon...</div></body>

<? } ?>
</html>
