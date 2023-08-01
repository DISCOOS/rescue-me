<!DOCTYPE html>
<? 

    # Prevent Chrome Data Compression Proxy
    #header('no-store, no-transform');

    require('config.php');

    use Psr\Log\LogLevel;
    use RescueMe\Device;
    use RescueMe\Log\Logs;
    use RescueMe\Locale;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;

    $id = input_get_hash('id');
    $force = input_get_boolean('force');

    $missing = ($id === false ? false : Missing::get(decrypt_id($id)));

    if($missing !== false) {

        set_system_locale(DOMAIN_TRACE, $missing->locale);

        $type = Properties::get(Properties::LOCATION_APPCACHE, $missing->user_id);
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $manifest = ($type !== 'none' ? 'manifest="locate.appcache"' : '');
        $is_mobile = Device::isMobile($user_agent);

        if($is_mobile || $force) {
            if(($delay = isset($message)) === false) {
                $message = CALCULATING;
            }

            // Set state
            $missing->answered($user_agent, $force);

            // Create minified js
            $trace = JSMin::minify(file_get_contents(APP_PATH.'trace/js/trace.js'));

            // Is iPhone?
            if (strstr($user_agent,'iPhone')) {
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
                Logs::write(Logs::SMS, LogLevel::ERROR, FAILED_TO_GET_COUNTRY_CODE, $_GET);
            }

            $options['trace']['to'] = $code . $missing->alert_mobile;
            $options['trace']['age'] = Properties::get(Properties::LOCATION_MAX_AGE, $user_id);
            $options['trace']['wait'] = Properties::get(Properties::LOCATION_MAX_WAIT, $user_id);
            $options['trace']['acc'] = Properties::get(Properties::LOCATION_DESIRED_ACC, $user_id);

            $install = get_rescueme_install($options);

            // Get js wrapped inside self-invoking function.
            $js = "(function(window,document,install){".$trace."}(window,document,$install));";

            # Prevent Chrome Data Compression Proxy
            header('Cache-Control: no-store,no-transform');

    ?>
    <html <?=$manifest?>>
        <head>
            <title><?=TITLE?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
            <script id="trace"><?=$js?></script>
    <? if (isset($extra)) { ?><script id="extra"><?=$extra?></script><?php } ?>
        </head>
        <body onLoad="R.trace.locate();">
            <div align="center">
                <div style="max-width: 400px; min-height: 100px;">
                    <div id="f" style="margin-bottom: 10px"><?=$message?></div>
                    <span id="i"></span><br /><span id="s"></span>
                </div>
                <hr />
                <div id="l" style="margin-bottom: 10px">
                </div>
                <a href="<?=APP_URI?>a/<?=$id?>"
                    onclick="return confirm('<?=ARE_YOU_SURE.' '.THIS_WILL_ABORT_THE_REQUEST_PERMANENTLY?>');"><?=CANCEL?></a>
            </div>
        </body>
    </html>

    <?  } else {

        $browser = Device::detectBrowser($user_agent);
        Logs::write(Logs::TRACE,LogLevel::WARNING,
            "Location request [$id] loaded non-mobile browser [$user_agent]",
            $_GET, $missing->user_id);

        # Prevent Chrome Data Compression Proxy
        header('Cache-Control: no-store,no-transform');
    ?>
        <html>
        <head>
            <title><?=TITLE?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
        </head>
        <body>
            <div align="center">
                <div style="max-width: 400px; min-height: 100px;">
                    <?=HAVE_YOU_OPENED_THE_LINK_ON_A_MOBILE_PHONE?>
                    <p>
                    <a href="?force=true"><?=YES_TRACE_ME?></a>
                    </p>
                </div>
                <hr />
                <div id="l" style="margin-bottom: 10px">
                <a href="<?=APP_URI?>a/<?=$id?>"
                   onclick="return confirm('<?=ARE_YOU_SURE.' '.THIS_WILL_ABORT_THE_REQUEST_PERMANENTLY?>');"><?=NO_DONT_TRACE_ME?></a>
            </div>
        </body>
    <?  }

    } else {
        set_system_locale();
	    # Prevent Chrome Data Compression Proxy
        header('Cache-Control: no-store,no-transform');
    ?>
        <html>
        <head>
            <title><?=TITLE?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" />
        </head>
        <body>
            <div align="center">
                <div style="max-width: 400px; min-height: 100px;">
                    <?=sprintf(TRACE_S_NOT_FOUND,"'$id'")?>
                </div>
            </div>
        </body>
    <? }

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
        if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone')) {
            $msg[14] = IOS_TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA;
            $msg[15] = IOS_TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA2;
            $msg[16] = IOS_TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA3;
        }
        return $msg;
    }

?>

