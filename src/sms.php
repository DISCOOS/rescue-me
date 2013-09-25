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
    
    $method = strtoupper($_SERVER['REQUEST_METHOD']);    
    $params = ($method === 'POST' ? $_POST : $_GET); 
    $params = array_exclude($params, array('request','user'));

    if(empty($params)) {    

        echo "No parameters found";        
    } 
    else {

        $module = Module::get("RescueMe\SMS\Provider", $_GET['user']);

        $sms = $module->newInstance();

        if(!$sms){
            echo "Failed to load [RescueMe\SMS\Provider]";
        }
        else {
            
            $request = (isset($_GET['request']) ? $_GET['request'] : '');

            // Dispatch push request
            switch($request) {
                case 'callback':

                    // Is status callback supported?
                    if ($sms instanceof RescueMe\SMS\Callback) {
                        $sms->handle(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' ? $_POST : $_GET);
                    } else {
                        echo "[{$module->impl}] does not support [RescueMe/SMS/Callback]";
                    }

                    break;
                default:
                    echo "SMS request [$request] not supported";
                    break;
            }
        }
    }
?>
