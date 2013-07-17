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

    require_once('../config.php');
    require_once(APP_PATH_INC.'common.inc.php');

    use RescueMe\Module;
    $module = Module::get("\RescueMe\SMS\Provider");
    $sms = $module->newInstance();

    if(!$sms)
    {
        trigger_error("Failed loading SMS-module!", E_USER_WARNING);
    }

    // Is Sveve
    if ($sms instanceof RescueMe\SMS\Sveve) {
        if (isset($_GET['id']) && isset($_GET['number']) && isset($_GET['status'])) {
            $sms->delivered($_GET['id'], $_GET['number'], $_GET['status'], 
                    (isset($_GET['errorDesc']) ? $_GET['errorDesc'] : ''));
        }
        else {
            trigger_error('Missing parameters...', E_USER_WARNING);
        }
    }
    // Not Sveve
    else {
        trigger_error('Not supported...', E_USER_WARNING);
    }
?>
