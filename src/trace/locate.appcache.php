<?php

require_once('../config.php');

use RescueMe\Mobile;
use RescueMe\Properties;

$id = input_get_hash('id');
$mobile = ($id === false ? false : Mobile::get(decrypt_id($id)));

if($mobile !== false) {

    set_system_locale(DOMAIN_TRACE, $mobile->locale);

    $version = VERSION;
    $user_id = $mobile->user_id;
    $type = Properties::get(Properties::LOCATION_APPCACHE, $user_id);
    if($type === 'settings')
    {
        header('Content-Type: text/cache-manifest');

        $options['age'] = Properties::get(Properties::LOCATION_MAX_AGE, $user_id);
        $options['wait'] = Properties::get(Properties::LOCATION_MAX_WAIT, $user_id);   
        $options['acc'] = Properties::get(Properties::LOCATION_DESIRED_ACC, $user_id);
        $options['locale'] = Properties::get(Properties::SYSTEM_LOCALE, $user_id);
        $version .= ' ' . md5(json_encode($options));
        
        echo "CACHE MANIFEST\n# $version\n../img/loading.gif\nNETWORK:\n*";
        
    } else {
        
        // Tell browser to remove appcache
        header("HTTP/1.0 404 Not Found");
        
    }
}

?>