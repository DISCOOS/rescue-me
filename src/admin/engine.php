<?php
require_once(BASEPATH_INC.'common.inc.php');
require_once(ADMINPATH_CLASS.'user.class.php');
if(!isset($_GET['SAVNET_module'])) {
	$_GET['SAVNET_module'] = 'dash';
}

define('BASEURL', 'http://savnet.ntrkh.no/admin/');

$user = new user();
$logon = $user->verify();

if($logon && isset($_POST['savnet_user']) && !empty($_POST['savnet_user']) && !empty($_POST['savnet_pass'])) {
#	redir(BASEURL);
} elseif($logon) {
	
} else {
	$_GET['SAVNET_module'] = 'logon';
}

function redir($url) {
	header("Location: $url");
	exit();
}

switch($_GET['SAVNET_module']) {
	case 'start':
	case 'dash':
		$_SAVN['name'] = 'Dashboard';
		$_SAVN['file'] = 'dash';
		break;
	case 'brukere':
		$_SAVN['name'] = 'Brukere';
		$_SAVN['file'] = 'brukere';
		break;
	case 'om':
		$_SAVN['name'] = 'Om';
		$_SAVN['file'] = 'om';
		break;
	case 'ny_savnet':
		if(isset($_POST['mb_name'])) {
			require_once(BASEPATH_INC.'common.inc.php');
			require_once(BASEPATH_CLASS.'missing.class.php');
			$missing = new Missing();
			$status = $missing->addMissing($_POST['mb_name'], $_POST['mb_mail'], $_POST['mb_mobile'], 
										   $_POST['m_name'], $_POST['m_mobile']);
			if($status) {
				header("Location: ".ADMIN_URL.'savnet/'.$missing->missing_id);
				exit();
			}
			$_SAVN['message'] = 'En feil oppstod ved registrering, prøv igjen';
		}
		$_SAVN['name'] = 'Start sporing av savnet';
		$_SAVN['file'] = 'ny_savnet';
		break;
	case 'savnede':
		$_SAVN['name'] = 'Alle savnede';
		$_SAVN['file'] = 'savnede';
		require_once('../class/all_missing.class.php');
		break;
	case 'savnet':
		$_SAVN['name'] = 'Savnet person';
		$_SAVN['file'] = 'savnet';
		require_once('../class/missing.class.php');
		break;
	case 'logon':
		$_SAVN['name'] = 'Logg inn';
		$_SAVN['file'] = 'logon';
		break;
}