<?php
    use RescueMe\Domain\User;
    use RescueMe\Locale;

// Process form?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(User::recover($_POST['email'], $_POST['country'], $_POST['mobile'])) {
        header("Location: ".ADMIN_URI.($_SESSION['logon'] ? 'user/list' : 'logon'));
        exit();
    }
	$TWIG['message'] = array('header' => T_('Could not reset password:'),
							 'body' => RescueMe\DB::errno() ? RescueMe\DB::error() : T_('User does not exist')
							 );
}   

// Get requested user (only when logged in)
$id = input_get_int('id');
$user = $_SESSION['logon'] && $id ? User::get($id) : null; 

$TWIG['countries'] = Locale::getCountryNames();
$TWIG['selected_country'] = isset($user) ? $user->mobile_country : Locale::getCurrentCountryCode();