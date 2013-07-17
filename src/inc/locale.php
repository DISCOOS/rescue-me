<?php
	require_once(APP_PATH_INC.'php-gettext/gettext.inc');	

	// SHOULD BE SET WITH COOKIES
	$locale = isset($_GET['locale']) ? $_GET['locale'] : 'en';
	
	putenv("LC_ALL=$locale");
	T_setlocale(LC_MESSAGES, $locale);
	bindtextdomain("messages", APP_PATH."locale");
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain("messages");
?>