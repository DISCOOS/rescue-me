<?php
    
	require(__DIR__.DIRECTORY_SEPARATOR.'php-gettext/gettext.inc');
    
    define('DOMAIN_SMS', 'sms');
    define('DOMAIN_ADMIN', 'admin');
    define('DOMAIN_TRACE', 'trace');
    define('DOMAIN_COMMON', 'common'); 
    define('DOMAIN_LOCALES', 'locales');
    define('ENCODING', 'UTF-8');

    $locale = 'en_US'.ENCODING;

T_locale();
    
    // Ensure period in floats
    T_setLocale(LC_NUMERIC,$locale);
    
    // Set default locale
    T_setLocale(LC_TIME, $locale);
    T_setLocale(LC_MESSAGES, $locale);

    function sentence($elements, $delimiter = ' ', $ending = '') {
        return ucfirst(implode($delimiter, array_map(function($element) {
            return strtolower($element);
        },$elements))).$ending;
    }

    /**
     * Set current system domain and locale
     * 
     * @param string $domain Message domain
     * @param string $locale Locale string (without encoding)
     * 
     * @return array Previous domain and locale
     */
    function set_system_locale($domain = DOMAIN_COMMON, $locale = DEFAULT_LOCALE) {
        
        $encoding = 'UTF-8';
        if(isset($_SESSION)) {
            $previous = array(
                isset_get($_SESSION, 'domain', DOMAIN_COMMON),
                isset_get($_SESSION, 'locale', DEFAULT_LOCALE)
            );
        } else {
            $previous = array(DOMAIN_COMMON,DEFAULT_LOCALE);
        }

        // Use drop-in fallback replacement in case gettext extension is not available
        T_setLocale(LC_TIME, "$locale.$encoding");
        T_setLocale(LC_MESSAGES, "$locale.$encoding");

        // Define shared domains
        define_domain(DOMAIN_COMMON, $encoding);
        define_domain(DOMAIN_LOCALES, $encoding);

        // Define given domain
        define_domain($domain, $encoding);

        $_SESSION['domain'] = $domain;
        $_SESSION['locale'] = $locale;
        
        return $previous;        
    }
    
    /**
     * Define domain 
     * 
     * @param string $domain Domain name
     * @param string $encoding Domain encoding
     * @return boolean
     */
    function define_domain($domain, $encoding) {
        
        T_bindtextdomain($domain, APP_PATH_LOCALE);
        T_bind_textdomain_codeset($domain, $encoding);
        
        // Set current text domain
        T_textdomain($domain);
        
        if(in_array($domain, array('common'))) {
            $path = APP_PATH.(implode(DIRECTORY_SEPARATOR,array('locale', 'domain', $domain.'.domain.php')));
        } else {
            $path = APP_PATH.(implode(DIRECTORY_SEPARATOR,array($domain, 'locale', $domain.'.domain.php')));
        }

        if(realpath($path) !== false) {
            require $path;
        }
    }
    
    /**
     * Force given domain and locale
     * 
     * @param string $domain Domain name
     * @param string $locale Locale name
     * @param string $msgid Message id
     * 
     * @return string
     */
    function T_locale($domain, $locale, $msgid) {
        
        list($old_domain, $old_locale) = set_system_locale($domain, $locale);
        
        $text = T_dgettext($domain, defined($msgid) ? constant($msgid) : $msgid);
        
        set_system_locale($old_domain, $old_locale);
        
        return $text;
        
    }
    
    
?>
