<!DOCTYPE html>
<? 
    require_once('../config.php');
    require_once('../min/lib/JSMin.php');
    
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
    
    $id = $_GET['id'];
    $phone = $_GET['phone'];
    $missing = Missing::getMissing($id, $phone);
    
    if($missing !== false) {
        
        $missing->answered();                
        
        // Create minified js
        $track = JSMin::minify(file_get_contents(APP_PATH.'track/js/track.js'));
        
        $user_id = Operation::getOperation($missing->op_id)->user_id;

        // Create install options
        $options = array();
        $options['track']['id'] = $id;
        $options['track']['phone'] = $phone;
        $options['track']['age'] = Properties::get(Properties::LOCATION_MAX_AGE, $user_id);
        $options['track']['wait'] = Properties::get(Properties::LOCATION_MAX_WAIT, $user_id);
        $options['track']['acc'] = Properties::get(Properties::LOCATION_DESIRED_ACC, $user_id);
        
        $install = get_rescueme_install($options);
        
        // Get js wrapped inside self-invoking function.
        $js = "(function(window,document,install){".$track."}(window,document,$install));";
        
?>
<script id="track"><?=$js?></script></head>
<body onLoad="R.track.locate();">
<div align="center"><div style="max-width: 400px; min-height: 100px; position: relative;">
<div id="f">Beregner posisjon...</div><span id="i"></span><br /><span id="s"></span></div>
<hr /><div id="l" style="max-width: 400px; min-height: 50px; position: relative;"></div>
<div style="max-width: 400px; position: relative;">
<a href="<?=APP_URI?>l/<?=$_GET['id']?>/<?=$_GET['phone']?>" style="position: absolute; left: 0; bottom: 0;">Oppdater</a>
<a href="<?=APP_URI?>a/<?=$_GET['id']?>/<?=$_GET['phone']?>" onclick="return confirm('Er du sikker?');" style="position: absolute; right: 0; bottom: 0;">Avbryt</a>
</div>
</div>
</body>    
<? } else { ?>

<? insert_alert(_("Missing not found")) ?>

<? }} ?>
</html>