<?php

use RescueMe\Locale;
use RescueMe\Domain\Missing;
use RescueMe\SMS\Provider;

$missing_modules = array();
if(!RescueMe\Manager::exists(Provider::TYPE))
	$missing_modules[] = Provider::TYPE;

#if(class_exists('\RescueMe\Domain\Missing'))
#	$missing[] = '\RescueMe\Domain\Missing';

if(sizeof($missing_modules) > 0) {
	$TWIG['error'] = array('header' => sizeof($missing_modules) > 1 
										? T_('Missing modules!') 
										: T_('Missing module!'),
						   'body' => sizeof($missing_modules) > 1 
						   				? T_('The system is missing following modules!') 
						   				: T_('The system is missing following module!'),
						   'data' => implode(', ', $missing_modules));
} else {
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $TWIG['data']	= $_POST;
        require_once(APP_PATH_INC.'common.inc.php');
        
		$operation = new \RescueMe\Domain\Operation;
		$operation = $operation->add(
			'trace', 
			$_POST['m_name'], 
			$edit->id,
			$_POST['mb_mobile_country'], //"NO", 
			$_POST['mb_mobile']);

		if(!$operation) {
	        $TWIG['message']['header'] = T_('Could not initiate trace');
	        $TWIG['message']['body']   = T_('System error: could not initiate operation');
		} else {
			$missing = Missing::add(
				$_POST['m_name'], 
				$_POST['m_mobile_country'], 
				$_POST['m_mobile'], $operation->id);

			if($missing) {

                // Send first SMS
                $missing->sendSMS($_POST['sms_text']);

				header("Location: ".ADMIN_URI.'missing/'.$operation->id);
				exit();
			}
	        $TWIG['message']['header'] = T_('Could not initiate trace');
	        $TWIG['message']['body']   =  RescueMe\DB::errno() ? 'DB Error: '. RescueMe\DB::error() : T_('Please try again');
	    }
    }
	$TWIG['countries'] = Locale::getCountryNames();
	
	// POSSIBLE BUG IF USER IS SET
	
	$TWIG['selected_country'] = isset($edit) ? Locale::getCountryCode($edit->mobile_country) : Locale::getCurrentCountryCode();
}

