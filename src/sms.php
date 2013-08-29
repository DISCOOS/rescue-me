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

    use RescueMe\User;
    use RescueMe\Module;
    
    $module = Module::get("RescueMe\SMS\Provider", User::current()->id);
    
    $sms = $module->newInstance();

    if(!$sms){
        trigger_error("Failed to load [RescueMe\SMS\Provider]", E_USER_ERROR);
    }
    
    // Dispatch push request
    switch(isset($_GET['request']) ? $_GET['request'] : '') {
        case 'delivered':
            
            // Is status callback supported?
            if ($sms instanceof RescueMe\SMS\Callback) {
                $sms->handle(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' ? $_POST : $_GET);
            } else {
                trigger_error("[RescueMe/SMS/Provider] does not support [RescueMe/SMS/Callback]", E_USER_ERROR);
            }
            
            break;
        default:
            trigger_error("SMS ", E_USER_ERROR);
            break;
    }
        

    
?>
