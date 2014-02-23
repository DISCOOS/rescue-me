<?php

    /**
     * File containing: Callback handler for SMS-providers
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 12. July 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */
    
    require('config.php');

    use RescueMe\Module;
    use Psr\Log\LogLevel;
    use RescueMe\Log\Logs;
    use RescueMe\Log\Logger;
    
    // Catch errors
    set_error_handler(function($errno , $errstr, $errfile, $errline) {
        $e = new ErrorException($errstr, $errno, $errno, $errfile, $errline);
        error(Logger::toString($e), Logs::SYSTEM, LogLevel::ERROR);
    });

    try {
    
        $method = strtoupper($_SERVER['REQUEST_METHOD']);    
        $params = ($method === 'POST' ? $_POST : $_GET); 
        $params = array_exclude($params, array('request','user'));

        if(empty($params)) {    

            error("No parameters found");
        } 
        else {

            $module = Module::get("RescueMe\SMS\Provider", $_GET['user']);

            $sms = $module->newInstance();

            if($sms === FALSE){

                error("Failed to load [RescueMe\SMS\Provider]");

            } else {

                $request = (isset($_GET['request']) ? $_GET['request'] : '');

                // Dispatch push request
                switch($request) {
                    case 'callback':

                        // Is status callback supported?
                        if ($sms instanceof RescueMe\SMS\Callback) {
                            $sms->handle($params);
                        } else {
                            error("[{$module->impl}] does not support [RescueMe/SMS/Callback]");
                        }

                        break;
                    default:
                        error("SMS request [$request] not supported");
                        break;
                }
            }
        }
    } catch(Exception $e) {
        error(Logger::toString($e));
    }
    
    function error($message, $name = Logs::SMS, $level = LogLevel::WARNING) {
        
        $context = array();
        $context['server'] = $_SERVER;
        $context['request'] = $_REQUEST;

        Logs::write($name, $level, "sms/callback: $message", $context);
        
        echo $message;

    }

?>
