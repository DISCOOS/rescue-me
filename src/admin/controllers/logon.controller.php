<?php
$TWIG['VIEW'] = LOGON;

if(isset($_POST['username']) && !$_SESSION['logon'])
	$TWIG['message'] = "Du har oppgitt feil brukernavn eller passord";
?>