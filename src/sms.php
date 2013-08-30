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
    
    $module = Module::get("RescueMe\SMS\Provider", $_GET['user']);
    
    $sms = $module->newInstance();

    if(!$sms){
        trigger_error("Failed to load [RescueMe\SMS\Provider]", E_USER_ERROR);
    }
    
    // Dispatch push request
    switch(isset($_GET['request']) ? $_GET['request'] : '') {
        case 'callback':
            
            // Is status callback supported?
            if ($sms instanceof RescueMe\SMS\Callback) {
                $sms->handle(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' ? $_POST : $_GET);
            } else {
                trigger_error("[{$module->impl}] does not support [RescueMe/SMS/Callback]", E_USER_ERROR);
            }
            
            break;
        default:
            trigger_error("SMS ", E_USER_ERROR);
            break;
    }
        

    
?>
