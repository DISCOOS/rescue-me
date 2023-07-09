<?php
use RescueMe\User;

$user = User::get($id); 

// Process form?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = User::safe($_POST['email']);
    if(empty($username)) {
        $TWIG['message']['header'] = T_('Your changes was not saved!');
        $TWIG['message']['body'] = T_('You entered an invalid e-mail address. Please enter a correct e-mail address');
    } else {	    
	    if($user->update($_POST['name'], $_POST['email'], $_POST['mobile'])) {
	        header("Location: ".ADMIN_URI.'user/list');
	        exit();
	    }
		$TWIG['message']['header'] = T_('An error ocurred!');
		$TWIG['message']['message'] = T_('An unknown error ocurred while saving your user data. The system reported');
	    $TWIG['message']['data'] = RescueMe\DB::errno() ? RescueMe\DB::error() : T_('Sorry');
	}  
}
$TWIG['user'] = $user;
?>