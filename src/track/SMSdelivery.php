<?php
/**
     * File containing: Callback-file for SMS-provider
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 12. July 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */

    require('../config.php');

    use RescueMe\Module;
    $module = Module::get("RescueMe\SMS\Provider", User::current()->id);
    $sms = $module->newInstance();

    if(!$sms)
    {
        trigger_error("Failed loading SMS-module!", E_USER_WARNING);
    }

    // Is Sveve
    if ($sms instanceof RescueMe\SMS\Sveve) {
        $sms->delivered($_GET['id'], $_GET['number'], $_GET['status'], 
                (isset($_GET['errorDesc']) ? $_GET['errorDesc'] : ''));
    }
    // Not Sveve
    else {
        trigger_error('Delivery notification not supported...', E_USER_WARNING);
    }
?>
