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
            $message = CALCULATING;
        }

        $type = Properties::get(Properties::LOCATION_APPCACHE, $missing->user_id);
        $manifest =  ($type !== 'none' ? 'manifest="locate.appcache"' : '');

        // Set state
        $missing->answered();                

        // Create minified js
        $trace = JSMin::minify(file_get_contents(APP_PATH.'trace/js/trace.js'));

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
            Logs::write(Logs::SMS, LogLevel::ERROR, FAILED_TO_GET_COUNTRY_CODE, $_GET);
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
    <script id="trace"><?=$js?></script></head><body onLoad="R.trace.locate();"><div align="center"><div style="max-width: 400px; min-height: 100px;">
    <div id="f" style="margin-bottom: 10px"><?=$message?></div><span id="i"></span><br /><span id="s"></span></div><hr />
    <div id="l" style="margin-bottom: 10px"></div><a href="<?=APP_URI?>a/<?=$id?>" onclick="return confirm('<?=ARE_YOU_SURE?>');"><?=CANCEL?></a></div></body> 
    <? } else {

    insert_alert(sprintf(TRACE_S_NOT_FOUND,$id)); 

    } ?>
    </html>
    <? 
        
    function get_messages() {

        /* 
         * Get messages in domain 'trace'
         * 
         * NOTE: We do not use i18next.js here because of the overhead it introduces!
         */

        $msg = array();
        $msg[0] = GEOLOCATION_NOT_SUPPORTED;
        $msg[1] = FOUND_LOCATION_WITH_D_ACCURACY;
        $msg[2] = LOCATION_IS_OLD_CHECK_IF_GPS_IS_ON;
        $msg[3] = WAITING_FOR_HIGHER_ACCURACY;
        $msg[4] = TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA;
        $msg[5] = LOCATION_IS_UNAVAILABLE;
        $msg[6] = PLEASE_APPROVE_ACCESS_TO_LOCATION_FASTER;
        $msg[7] = UNKNOWN_ERROR;
        $msg[8] = LOCATION_S;
        $msg[9] = CALCULATING;
        $msg[10] = LOCATION_NOT_SENT_CHECK_DATA_CONNECTION;
        $msg[11] = SEND_LOCATION_AS;
        $msg[12] = LOCATION_NOT_FOUND;
        $msg[13] = UPDATE;
        return $msg;
    }
    
?>

