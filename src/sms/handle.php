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

    use RescueMe\Manager;
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

            error(T_('No parameters found'));
        } 
        else {

            $module = Manager::get(Provider::TYPE, $_GET['user']);

            $sms = $module->newInstance();

            if($sms === FALSE){

                error(sprintf(T_('Failed to load [%1$s]'),Provider::TYPE));

            } else {

                $request = (isset($_GET['request']) ? $_GET['request'] : '');

                // Dispatch push request
                switch($request) {
                    case 'callback':

                        // Is status callback supported?
                        if ($sms instanceof RescueMe\SMS\Callback) {
                            $sms->handle($params);
                        } else {
                            error(sprintf(T_('[%1$s] does not support [%2$s]'), $module->impl, 'RescueMe/SMS/Callback'));
                        }

                        break;
                    default:
                        error(sprintf(T_('SMS request [%1$s] not supported'), $request));
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
