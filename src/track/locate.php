<!DOCTYPE html>
<? 
    require_once('../config.php');
    require_once('../min/lib/JSMin.php');
    
    use Psr\Log\LogLevel;
    use RescueMe\Log\Logs;    
    use RescueMe\Locale;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    
    $delay = isset($message);
    
    if($delay === false) {
        $message = _('Beregner posisjon...');
    }
    
    $id = input_get_hash('id');
    
    $missing = ($id === false ? false : Missing::get(decrypt_id($id)));
    
    if($missing !== false) {
        
        $type = Properties::get(Properties::LOCATION_APPCACHE, $missing->user_id);
        $manifest =  ($type !== 'none' ? 'manifest="locate.appcache"' : '');
        $missing->answered();                
        
        // Create minified js
        $track = JSMin::minify(file_get_contents(APP_PATH.'track/js/track.js'));
        
        $user_id = Operation::get($missing->op_id)->user_id;

        // Create install options
        $options = array();
        $options['track']['id'] = $id;
        $options['track']['name'] = $missing->name;
        $options['track']['delay'] = $delay;
        $options['track']['msg'] = get_messages();
        
        $country = $missing->alert_mobile_country;
        if(($code = Locale::getDialCode($country)) === FALSE)
        {
            Logs::write(Logs::SMS, LogLevel::ERROR, "Failed to get country code", $_GET);
        }               
        
        $options['track']['to'] = $code . $missing->alert_mobile;
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
<div align="center"><div style="max-width: 400px; min-height: 100px;">
<div id="f" style="margin-bottom: 10px"><?=$message?></div><span id="i"></span><br /><span id="s"></span></div>
<hr />
<div id="l" style="margin-bottom: 10px"></div>
<a href="<?=APP_URI?>a/<?=$id?>" onclick="return confirm('Er du sikker?');"><?=_('Avbryt')?></a>
</div>
</body>    
<? } else {

    insert_alert(_("Missing not found"));

} ?>
</html>

<? 
    function get_messages() {
        $msg = array();
        $msg[0] = _('Lokalisering støttes ikke.');
        $msg[1] = _('Fant posisjon med &#177;{0} m nøyaktighet.');
        $msg[2] = _('Posisjon er gammel, sjekk om GPS er påslått!');
        $msg[3] = _('Søker nøyaktigere posisjon...');
        $msg[4] = _('Slå på tilgang til stedsinformasjon!');
        $msg[5] = _('Posisjon er utilgjengelig.');
        $msg[6] = _('Bekreft tillatelse til å vise posisjon raskere.');
        $msg[7] = _('Ukjent feil.');
        $msg[8] = _('Posisjon: <b>{0}</b>');
        $msg[9] = _('Beregner posisjon...');
        $msg[10] = _('Posisjon ikke sendt, sjekk datakobling.');
        $msg[11] = _('Send posisjon som');
        $msg[12] = _('Fant ingen posisjon.');
        $msg[13] = _('Oppdater');
        return $msg;
    }
    
?>

