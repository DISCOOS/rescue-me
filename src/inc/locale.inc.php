<?php
    
	require(__DIR__.DIRECTORY_SEPARATOR.'php-gettext/gettext.inc');

    define('ENCODING', 'UTF-8');

    $format = implode(DIRECTORY_SEPARATOR,array('%1$s', '%2$s'));

    $domain = sprintf($format, 'locale', 'domain');

    define('APP_PATH_DOMAIN_COMMON', APP_PATH.$domain);
    define('APP_PATH_DOMAIN_LOCALES', APP_PATH.$domain);
    define('APP_PATH_DOMAIN_ADMIN', APP_PATH.sprintf($format, 'admin', $domain));
    define('APP_PATH_DOMAIN_TRACE', APP_PATH.sprintf($format, 'trace', $domain));
    define('APP_PATH_DOMAIN_SMS', APP_PATH.sprintf($format, 'sms', $domain));

    $locale = 'en_US.'.ENCODING;

    // Ensure period in floats
    T_setLocale(LC_NUMERIC, $locale);
    
    // Set default locale
    T_setLocale(LC_TIME, $locale);
    T_setLocale(LC_MESSAGES, $locale);

    // Define shared domains using default locale
    load_domain(APP_PATH_DOMAIN_COMMON, 'common');
    load_domain(APP_PATH_DOMAIN_LOCALES, 'locales');
    load_domain(APP_PATH_DOMAIN_ADMIN, 'admin');
    load_domain(APP_PATH_DOMAIN_TRACE, 'trace');
    load_domain(APP_PATH_DOMAIN_SMS, 'sms');

    /**
     * Get sentence
     *
     * @param $elements
     * @param string $delimiter
     * @param string $ending
     * @return string
     */
    function sentence($elements, $delimiter = ' ', $ending = '') {
        return ucfirst(implode($delimiter, array_map(function($element) {
            return strtolower($element);
        },$elements))).$ending;
    }

    /**
     * Get sentences as single string
     *
     * @param $elements
     * @param string $delimiter
     * @param string $ending
     * @return string
     */
    function sentences($elements, $delimiter = '. ', $ending = '.') {
        return implode($delimiter, $elements).$ending;
    }

    /**
     * Set current system domain and locale
     * 
     * @param string $domain Message domain
     * @param string $locale Locale string (without encoding)
     * 
     * @return array|boolean Previous domain and locale or false if locale not changed
     */
    function set_system_locale($domain = DOMAIN_COMMON, $locale = DEFAULT_LOCALE) {

//        if(isset($_SESSION)) {
//            $previous = array(
//                isset_get($_SESSION, 'domain', DOMAIN_COMMON),
//                isset_get($_SESSION, 'locale', DEFAULT_LOCALE)
//            );
//        } else {
//            $previous = array(DOMAIN_COMMON,DEFAULT_LOCALE);
//        }

        $constant = 'APP_PATH_DOMAIN_'.strtoupper($domain);

        if(defined($constant)) {

            $encoding = 'UTF-8';
            $path = dirname(constant($constant));


            // Use drop-in fallback replacement in case gettext extension is not available
            T_setLocale(LC_TIME, "$locale.$encoding");
            T_setLocale(LC_MESSAGES, "$locale.$encoding");

            // Set given domain
            set_domain($domain, $encoding, $path);

//            $_SESSION['domain'] = $domain;
//            $_SESSION['locale'] = $locale;

            $previous = false;

        } else {
            $previous = false;
        }

        return $previous;
    }

    /**
     * Load domain resources
     *
     * @param string $root Domain root
     * @param string $name Domain name
     * @return boolean
     */
    function load_domain($root, $name) {

        set_domain($name, ENCODING, dirname($root));

        $file = $root.DIRECTORY_SEPARATOR."$name.domain.php";

        if(realpath($file) === false) {
            trigger_error('Domain file ' . $file . ' does not exist', E_USER_ERROR);
        }

        return require $file;
    }


    /**
     * Set domain
     *
     * @param string $domain Domain name
     * @param string $encoding Domain encoding
     * @param string $path Path to domain
     * @return boolean
     */
    function set_domain($domain, $encoding, $path) {

        T_bindtextdomain($domain, $path);
        T_bind_textdomain_codeset($domain, $encoding);

        // Set current text domain
        T_textdomain($domain);
    }


    /**
     * Force given domain and locale
     *
     * @param string $msgid Message id
     *
     * @param string $locale Locale name
     * @param string $domain Domain name, pass null for current domain (optional)
     * @return string
     */
    function T_locale($msgid, $locale, $domain = null) {

        if(is_null($domain)) {
            $domain = isset_get($_SESSION, 'domain', DOMAIN_COMMON);
        }

        // Only force if different from current
        if(isset_get($_SESSION, 'domain', DOMAIN_COMMON) !== $domain
            || isset_get($_SESSION, 'locale', DEFAULT_LOCALE) !==$locale )
        {
            list($old_domain, $old_locale) = set_system_locale($domain, $locale);

            $text = T_dgettext($domain, $msgid);

            set_system_locale($old_domain, $old_locale);

        }
        else {
            $text = T_($msgid);
        }

        return $text;
        
    }
