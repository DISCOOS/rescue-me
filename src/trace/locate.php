<!DOCTYPE html>
<? 
    require('config.php');

    use Psr\Log\LogLevel;
    use RescueMe\Log\Logs;    
    use RescueMe\Locale;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;

    $id = input_get_hash('id');

    $missing = ($id === false ? false : Missing::get(decrypt_id($id)));

    if($missing !== false) {

        set_system_locale(DOMAIN_TRACE, $missing->locale);
        
        if(($delay = isset($message)) === false) {
            $message = T_('Calculating');
        }

        $type = Properties::get(Properties::LOCATION_APPCACHE, $missing->user_id);
        $manifest =  ($type !== 'none' ? 'manifest="locate.appcache"' : '');

        // Set state
        $missing->answered();                

        // Create minified js
        $trace = JSMin::minify(file_get_contents(APP_PATH.'trace/js/trace.js'));
        
        // Is iPhone?
        if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone')) {
            $extra = JSMin::minify(file_get_contents(APP_PATH.'trace/js/iPhone.js'));
        }

        $user_id = Operation::get($missing->op_id)->user_id;

        // Create install options
        $options = array();
        $options['trace']['id'] = $id;
        $options['trace']['name'] = $missing->name;
        $options['trace']['delay'] = $delay;
        $options['trace']['msg'] = get_messages();

        $country = $missing->alert_mobile_country;
        if(($code = Locale::getDialCode($country)) === FALSE)
        {
            Logs::write(Logs::SMS, LogLevel::ERROR, T_('Failed to get country code'), $_GET);
        }               

        $options['trace']['to'] = $code . $missing->alert_mobile;
        $options['trace']['age'] = Properties::get(Properties::LOCATION_MAX_AGE, $user_id);
        $options['trace']['wait'] = Properties::get(Properties::LOCATION_MAX_WAIT, $user_id);   
        $options['trace']['acc'] = Properties::get(Properties::LOCATION_DESIRED_ACC, $user_id);

        $install = get_rescueme_install($options);

        // Get js wrapped inside self-invoking function.
        $js = "(function(window,document,install){".$trace."}(window,document,$install));";

    ?>
    <html <?=$manifest?>><head><title><?=TITLE?></title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
    <script id="trace"><?=$js?></script>
    <?php if (isset($extra)) { ?><script id="extra"><?=$extra?></script><?php } ?>
    </head><body onLoad="R.trace.locate();"><div align="center"><div style="max-width: 400px; min-height: 100px;">
    <div id="f" style="margin-bottom: 10px"><?=$message?></div><span id="i"></span><br /><span id="s"></span></div><hr />
    <div id="l" style="margin-bottom: 10px"></div><a href="<?=APP_URI?>a/<?=$id?>" onclick="return confirm('<?=T_('Are you sure?')?>');"><?=T_('Cancel')?></a></div></body>
    <? } else {

    insert_alert(sprintf(T_('Trace %1$s not found'),$id));

    } ?>
    </html>
    <? 
        
?>

