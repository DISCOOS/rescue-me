<?php

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
        $options['app'] = array('url' => APP_URI);
        $options['admin'] = array('url' => ADMIN_URI);
        $options['locale'] = DEFAULT_LOCALE;
        
        return str_replace('\\/', '/',json_encode($options));

    }// get_rescueme_install


    /**
     * Converts decimal degrees to degrees, minutes and decimal seconds
     *
     * @param $dd
     * @return array
     */
    function dd_to_dms($dd)
        {
        // Converts decimal degrees to degrees, minutes and decimal seconds
        // This is the piece of code which may appear to
        // be inefficient, but to avoid issues with floating
        // point math we extract the integer part and the float
        // part by using a string function.

        $vars = explode(".",$dd);
        $deg = $vars[0];
        $tempma = "0.".$vars[1];
        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = $tempma - ($min*60);
        return array("deg" => $deg, "min" => $min, "sec" => $sec);
    }


    /**
     * Converts decimal degrees to decimal minutes
     *
     * @param $dec
     * @return array
     */
    function dd_to_dm($dec)
    {
        // Converts decimal degrees to DM
        // ( decimal minutes) 
        // This is the piece of code which may appear to 
        // be inefficient, but to avoid issues with floating
        // point math we extract the integer part and the float
        // part by using a string function.

        $vars = explode(".",$dec);
        $deg = $vars[0];
        $tempma = "0.".$vars[1];
        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $des = $tempma - ($min*60);
        $des = explode(".",$des/60);
        $des = isset($des[1]) ? $des[1] : 0;
        return array("deg" => $deg, "min" => $min.'.'.$des);
    }
    

?>