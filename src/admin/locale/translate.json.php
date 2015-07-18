<?php

    /**
	 * i18next translate support
	 * 
	 * @copyright Copyright 2014 {@link http://www.onevoice.no DISCO OS} 
	 *
	 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */

    require(implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__),'config.php')));
    
    use RescueMe\Domain\User;
    use RescueMe\Properties;
    
    $id = User::currentId();
    
    $id = isset($id) ? $id : 0;
        
    set_system_locale(DOMAIN_ADMIN, Properties::get(Properties::SYSTEM_LOCALE, $id));
    
    $json = get_json_domain(DOMAIN_ADMIN);
    
    // Finish json request
    header("Content-Type: application/json; charset=utf-8");
    header("Content-Length: ".strlen($json));
    die($json);
    
    
    /**
     * Get domain as json-encoded string
     * 
     * NOTE: Only selected constants are returned as json (optimization)
     * 
     * @param string $domain
     * 
     * @return string
     */
    function get_json_domain($domain) {
        
        $file = get_path(__DIR__, array('domain', $domain.'.domain.json'));
        
        $json = file_get_contents($file);
        
        $data = json_decode($json,true);
        
        $data = prepare_json_array($data);
        
        return json_encode($data);
        
    }
    
    function prepare_json($data) {
        if(is_array($data)) {
            $data = prepare_json_array($data);
        } elseif(is_string($data) && defined($data)) {
            $data = T_(constant($data));
        }
        return $data;
    }
    
    function prepare_json_array($data) {
        foreach($data as $key => $value) {
            $data[$key] = prepare_json($value, $key);            
        }
        return $data;
    }
    
    
    
?>    
    