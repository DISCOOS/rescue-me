<?php

    function dec_to_dem($dec)
    {
        // Converts decimal longitude / latitude to DM
        // ( decimal minutes) 
        // This is the piece of code which may appear to 
        // be inefficient, but to avoid issues with floating
        // point math we extract the integer part and the float
        // part by using a string function.

        $vars = explode(".", $dec);
        $deg = $vars[0];
        $tempma = "0." . $vars[1];

        $tempma = (float)$tempma * 3600;
        $min = floor($tempma / 60);
        $des = $tempma - ($min * 60);
        $des = explode('.', $des);
        $des = isset($des[1]) ? (int)$des[1] : 0;

        return array("deg" => $deg, "min" => $min, "des" => $des);
    }
    
    
    function dec_to_dms($dec)
    {
        // Converts decimal longitude / latitude to DMS
        // ( Degrees / minutes / seconds ) 
        // This is the piece of code which may appear to 
        // be inefficient, but to avoid issues with floating
        // point math we extract the integer part and the float
        // part by using a string function.

        $vars = explode(".", $dec);
        $deg = $vars[0];
        $tempma = "0." . $vars[1];

        $tempma = (float)$tempma * 3600;
        $min = floor($tempma / 60);
        $sec = round($tempma - ($min * 60));
        $des = $tempma - ($min * 60) - $sec;
        $des = explode('.', $des);
        $des = isset($des[1]) ? (int)$des[1] : 0;

        return array("deg" => $deg, "min" => $min, "sec" => $sec, "des" => $des);
    }
    
    /**
     * Get application path relative to $_SERVER['SERVER_NAME'];
     * 
     * @return string URI
     * 
     */
    function get_rescueme_uri()
    {
        // Get current path
        $name = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
        $path = str_replace($name, '', $_SERVER['PHP_SELF']);
        
        // Get root folders
        $folders = array();
        foreach(scandir(APP_PATH) as $file) {            
            if($file === '.' || $file === '..') {
                continue;
            }
            if(is_dir(APP_PATH . '/' . $file)) { 
                $folders[] = $file;
            }
        }
        
        // Check if script is running from one of these folders
        foreach($folders as $folder){
            $match = strstr($path, $folder);
            if($match){
                return str_replace($match, '', $path);
            }
        }

        // Finished
        return $path;
        
    }// get_rescueme_uri
    
    
    /**
     * Get application URL
     * 
     * @return string URL
     */
    function get_rescueme_url() 
    {
        $url = '';
        if(isset($_SERVER["SERVER_PROTOCOL"]))
        {
            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            $url = $protocol . "://" . $_SERVER['SERVER_NAME'] . get_rescueme_uri();
        }
        return $url;
    }// get_rescueme_url
    

    /**
     * Get js install object as json string.
     */
    function get_rescueme_install($options=array())
    {
        $options["app"] = array("url" => APP_URI);
        $options["admin"] = array("url" => ADMIN_URI);
        $options["lang"] = array("locale" => APP_LOCALE);
        
        return str_replace('\\/', '/',json_encode($options));

    }// get_rescueme_install
    
    
    function is_ajax_request() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }        
?>