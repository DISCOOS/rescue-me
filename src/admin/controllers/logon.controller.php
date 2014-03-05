<?php
$TWIG['VIEW'] = LOGIN;

if(isset($_POST['username']) && !$_SESSION['logon'])
	$TWIG['message'] = array('header' => _('Incorrect email or password.'),
							 'body' => _('Make sure your email and password are both correct.')
							 );
?>