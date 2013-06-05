<?php
    session_start();

    // RescueMe application URI
    define('APP_URI', get_rescueme_uri());
    
    // RescueMe application URL
    define('APP_URL', get_rescueme_url());

    // RescueMe administration URO
    define('ADMIN_URI', APP_URI.'admin/');
    
    // ResueuMe application paths
    define('APP_PATH', dirname(__FILE__).'/');
    define('APP_PATH_INC', APP_PATH.'inc/');
    define('APP_PATH_CLASS', APP_PATH.'class/');

    // ResueuMe administration paths
    define('ADMIN_PATH', APP_PATH.'admin/');
    define('ADMIN_PATH_INC', ADMIN_PATH.'inc/');
    define('ADMIN_PATH_GUI', ADMIN_PATH.'gui/');
    define('ADMIN_PATH_CLASS', ADMIN_PATH.'class/');
    
    // RescueMe salt value
    define('SALT', 'SALT');

    // SMS integration constants
    define('SMS_ACCOUNT', '');
    define('SMS_FROM', 'RescueMe');

    // Google API key
    define('GOOGLE_API_KEY', 'GOOGLE_API_KEY');

    // RescueMe message constants
    define('TITLE', 'Rescue Me!');
    define('START', 'Start');
    define('LOGON', 'Logg inn');
    define('LOGOUT', 'Logg ut');
    define('ALERT', 'Varsle');
    define('MISSING', 'Savnede');
    define('NEW_MISSING', 'Finn savnet');
    define('USERS', 'Brukere');
    define('NEW_USER', 'Ny bruker');
    define('DASHBOARD', 'Dashboard');
    define('ABOUT', 'Om '.TITLE);
    define('SMS_TEXT', 'TEST: Du er savnet! <br /> Trykk på lenken for at vi skal se hvor du er: <br /> '.APP_URL.'#missing_id');
    define('SMS_NOT_SENT', 'OBS: Varsel ble ikke sendt til "#mb_name"');
    define('SMS2_TEXT', 'Om du har GPS på telefonen, anbefaler vi at du aktiverer dette. Vanligvis finner du dette under Innstillinger -> Generelt, eller Innstillinger -> Plassering');
    
    
    /**
     * Get application URL
     * 
     * @return string URL
     */
    function get_rescueme_url() 
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
        return $protocol . "://" . $_SERVER['SERVER_NAME'] . "/" . get_rescueme_uri();
    }// get_rescueme_url
    

    /**
     * Get application path relative to $_SERVER['SERVER_NAME'];
     * 
     * @return string URI
     * 
     */
    function get_rescueme_uri()
    {
        // Get document root
        $root = dirname(__FILE__);
        
        // Get current path
        $name = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
        $path = str_replace($name, '', $_SERVER['PHP_SELF']);
        
        // Get root folders
        $folders = array();
        foreach(scandir($root) as $file) {            
            if($file === '.' || $file === '..') {
                continue;
            }
            if(is_dir($root . '/' . $file)) { 
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
     * Get js install object as json string.
     */
    function get_rescueme_install()
    {
        $app = array("url" => APP_URI);
        $admin = array("url" => ADMIN_URI);
        
        return str_replace('\\/', '/',json_encode(array("app" => $app, "admin" => $admin)));        

    }// get_rescueme_install
    
    
    /**
     * Get js wrapped inside self-invoking function.
     * 
     * @param string $content Script content
     * 
     * @return string
     */
    function get_rescueme_js($content)
    {
        $install = get_rescueme_install();
        
        return "(function(window,document,install,undefined){".$content."}(window,document,$install));";
    }// get_rescueme_js    

    
    
