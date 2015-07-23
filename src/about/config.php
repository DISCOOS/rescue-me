<?

    require(implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__),'config.php')));

    define('APP_PATH_DOMAIN_ABOUT', APP_PATH.sprintf($format, 'about', $domain));

    load_domain(APP_PATH_DOMAIN_ABOUT, 'about');

    $locale = RescueMe\Locale::getBrowserLocale();

    set_system_locale(DOMAIN_COMMON, $locale);
