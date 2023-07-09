<?php
$TWIG['VIEW'] = LOGIN;

if(isset($_POST['username']) && !$_SESSION['logon'])
	$TWIG['message'] = array('header' => T_('Incorrect email or password.'),
							 'body' => T_('Make sure your email and password are both correct.')
							 );
?>