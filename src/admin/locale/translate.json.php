<?php

    /**
	 * i18next translate support
	 * 
	 * @copyright Copyright 2014 {@link http://www.onevoice.no DISCO OS} 
	 *
	 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */

    require(implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__),'config.php')));
    
    use RescueMe\User;
    use RescueMe\Properties;
    
    $id = User::currentId();
    
    $id = isset($id) ? $id : 0;
        
    set_system_locale(DOMAIN_ADMIN, Properties::get(Properties::SYSTEM_LOCALE, $id));
    
    $json = \RescueMe\Locale\Admin\get_json();
    
    // Finish json request
    header("Content-Type: application/json; charset=utf-8");
    header("Content-Length: ".strlen($json));
    die($json);

?>
