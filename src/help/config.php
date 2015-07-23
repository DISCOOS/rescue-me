<?

    require(implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__),'config.php')));

    define('APP_PATH_DOMAIN_HELP', APP_PATH.sprintf($format, 'help', $domain));

    load_domain(APP_PATH_DOMAIN_HELP, 'help');

    $locale = RescueMe\Locale::getBrowserLocale();

    set_system_locale(DOMAIN_COMMON, $locale);
