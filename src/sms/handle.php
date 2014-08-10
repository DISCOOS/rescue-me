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
    use RescueMe\SMS\Provider;

    // Catch errors
    set_error_handler(function($errno , $errstr, $errfile, $errline) {
        $e = new ErrorException($errstr, $errno, $errno, $errfile, $errline);
        error(Logger::toString($e), Logs::SYSTEM, LogLevel::ERROR);
    });
    
    set_system_locale(DOMAIN_SMS);

    try {
    
        $method = strtoupper($_SERVER['REQUEST_METHOD']);    
        $params = ($method === 'POST' ? $_POST : $_GET); 
        $params = array_exclude($params, array('request', 'user'));

        if(empty($params)) {    

            error(NO_PARAMETERS_FOUND);
        } 
        else {

            $module = Module::get(Provider::TYPE, $_GET['user']);

            $sms = $module->newInstance();

            if($sms === FALSE){

                error(sprintf(FAILED_TO_LOAD_MODULE_S,Provider::TYPE));

            } else {

                $request = (isset($_GET['request']) ? $_GET['request'] : '');

                // Dispatch push request
                switch($request) {
                    case 'callback':

                        // Is status callback supported?
                        if ($sms instanceof RescueMe\SMS\Callback) {
                            $sms->handle($params);
                        } else {
                            error(sprintf(MODULE_S1_DOES_NOT_SUPPORT_S2, $module->impl, 'RescueMe/SMS/Callback'));
                        }

                        break;
                    default:
                        error(sprintf(SMS_REQUEST_S_NOT_SUPPORTED, $request));
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
