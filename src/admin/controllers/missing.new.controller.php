<?php
$missing = array();
if(!RescueMe\Module::exists("RescueMe\SMS\Provider"))
	$missing[] = 'RescueMe\SMS\Provider';

#if(class_exists('\RescueMe\Missing'))
#	$missing[] = '\RescueMe\Missing';

if(sizeof($missing) > 0) {
	$TWIG['error'] = array('header' => sizeof($missing) > 1 
										? _('Missing modules!') 
										: _('Missing module!'),
						   'body' => sizeof($missing) > 1 
						   				? _('The system is missing following modules!') 
						   				: _('The system is missing following module!'),
						   'data' => implode(', ', $missing));
} else {
	if(isset($_POST['mb_name'])) {
        require_once(APP_PATH_INC.'common.inc.php');
        
        $missing = RescueMe\Missing::addMissing($_POST['mb_name'], $_POST['mb_mail'], $_POST['mb_mobile'], 
                                       $_POST['m_name'], $_POST['m_mobile']);
        if($missing) {
            header("Location: ".ADMIN_URI.'missing/'.$missing->id);
            exit();
        }
        $TWIG['data']	= $_POST;
        $TWIG['message']['header'] = _('Oops! Could not initiate trace');
        $TWIG['message']['body']   = _('When initiating trace, an error ocurred. The system reported: ');
        $TWIG['message']['data']   = $missing->getError();
    }
}