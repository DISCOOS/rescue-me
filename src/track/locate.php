<!DOCTYPE html>
<? 
    require_once('../config.php');
    require_once('../min/lib/JSMin.php');
    
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    use RescueMe\Locale;
    use Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;    
    
    $delay = isset($message);
    
    if($delay === false) {
        $message = _('Beregner posisjon...');
    }
    
    $id = $_GET['id'];
    $phone = $_GET['phone'];
    $missing = Missing::getMissing($id, $phone);
    
    if($missing !== false) {
        
        $type = Properties::get(Properties::LOCATION_APPCACHE, $missing->user_id);
        $manifest =  ($type !== 'none' ? 'manifest="locate.appcache"' : '');
        $missing->answered();                
        
        // Create minified js
        $track = JSMin::minify(file_get_contents(APP_PATH.'track/js/track.js'));
        
        $user_id = Operation::getOperation($missing->op_id)->user_id;

        // Create install options
        $options = array();
        $options['track']['id'] = $id;
        $options['track']['phone'] = $phone;
        $options['track']['name'] = $missing->name;
        $options['track']['delay'] = $delay;
        
        $country = $missing->alert_mobile['country'];
        if(($code = Locale::getDialCode($country)) === FALSE)
        {
            Logs::write(Logs::SMS, LogLevel::ERROR, "Failed to get country code", $_GET);
        }               
        
        $options['track']['to'] = $code . $missing->alert_mobile['mobile'];
        $options['track']['age'] = Properties::get(Properties::LOCATION_MAX_AGE, $user_id);
        $options['track']['wait'] = Properties::get(Properties::LOCATION_MAX_WAIT, $user_id);   
        $options['track']['acc'] = Properties::get(Properties::LOCATION_DESIRED_ACC, $user_id);
        
        $install = get_rescueme_install($options);
        
        // Get js wrapped inside self-invoking function.
        $js = "(function(window,document,install){".$track."}(window,document,$install));";
        
?>
<html <?=$manifest?>><head><title><?=TITLE?></title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
<script id="track"><?=$js?></script></head>
<body onLoad="R.track.locate();">
<div align="center"><div style="max-width: 400px; min-height: 100px; position: relative;">
<div id="f"><?=$message?></div><span id="i"></span><br /><span id="s"></span></div>
<hr /><div id="l" style="max-width: 400px; min-height: 50px; position: relative;"></div>
<div style="max-width: 400px; position: relative;">
<a href style="position: absolute; left: 0; bottom: 0;">Oppdater</a>
<a href="<?=APP_URI?>a/<?=$_GET['id']?>/<?=$_GET['phone']?>" onclick="return confirm('Er du sikker?');" style="position: absolute; right: 0; bottom: 0;">Avbryt</a>
</div>
</div>
</body>    
<? } else { ?>

<? insert_alert(_("Missing not found")) ?>

<? } ?>
</html>