<?php
    use RescueMe\User;
    use RescueMe\Locale;

// Process form?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(User::recover($_POST['email'], $_POST['country'], $_POST['mobile'])) {
        header("Location: ".ADMIN_URI.($_SESSION['logon'] ? 'user/list' : 'logon'));
        exit();
    }
	$TWIG['message'] = array('header' => _('Could not reset password:'),
							 'body' => RescueMe\DB::errno() ? RescueMe\DB::error() : _('User does not exist')
							 );
}   

// Get requested user (only when logged in)
$user = $_SESSION['logon'] && isset($_GET['id']) ? User::get($_GET['id']) : null; 

$TWIG['countries'] = Locale::getCountryNames();
$TWIG['selected_country'] = isset($user) ? $user->mobile_country : Locale::getCurrentCountryCode();