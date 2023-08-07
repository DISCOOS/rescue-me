<?
    require('config.php');

    use Psr\Log\LogLevel;
    use RescueMe\Device;
    use RescueMe\Log\Logs;
    use RescueMe\Missing;
    use RescueMe\Properties;

    $id = input_get_hash('id');
    $code = input_get_int('code');
    $desc = input_get_string('desc');

    if ($id === false || $code === false || $desc === false) {

        $response = ILLEGAL_ARGUMENTS;

    } else {

        $missing = Missing::get(decrypt_id($id));

        if($missing !== false) {

            set_system_locale(DOMAIN_TRACE, $missing->locale);

            $type = Properties::get(Properties::LOCATION_APPCACHE, $missing->user_id);
            $version = Properties::get(Properties::LOCATION_SCRIPT_VERSION, $missing->user_id);

            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $manifest = ($type !== 'none' ? 'manifest="locate.appcache"' : '');
            $is_mobile = Device::isMobile($user_agent);

            $missing->setLastError($code, $desc);

            Logs::write(
                Logs::TRACE,
                LogLevel::ERROR,
                "Error received from missing $id [$code: $desc]",
                $_GET
            );
        }

        $response = "OK";

    }

    # Prevent Chrome Data Compression Proxy
    header('Cache-Control: no-store,no-transform');
    echo $response;
