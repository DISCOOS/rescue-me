<?php
use RescueMe\Domain\User;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$TWIG['user'] = $_POST;
    $username = User::safe($_POST['email']);
    if(empty($username)) {
        $TWIG['message'] = T_('Invalid e-mail. Please enter a correct e-mail address');
    } else {	    
	    $user = User::create($_POST['name'], $_POST['email'], $_POST['password'], $_POST['country'], $_POST['mobile']);
	    if($user !== false) {
	        header("Location: ".ADMIN_URI.'user/list');
	        exit();
	    }
	    $TWIG['message']['header'] = T_('Oops! Could not create user');
	    $TWIG['message']['body']   = T_('When attempting to create user, an error ocurred. The system reported: ');
	    $TWIG['message']['data']   = $status->getError();
        $TWIG['countries'] = insert_options(\RescueMe\Locale::getCountryNames(), $_POST['country']);

	}
}
?>