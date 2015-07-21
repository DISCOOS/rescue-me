<?

    require(implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__),'config.php')));

    define('APP_PATH_DOMAIN_NEWS', APP_PATH.sprintf($format, 'news', $domain));

    load_domain(APP_PATH_DOMAIN_NEWS, 'news');

    $locale = RescueMe\Locale::getBrowserLocale();

    set_system_locale(DOMAIN_COMMON, $locale);
