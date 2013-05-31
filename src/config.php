<?php
session_start();

define('PUBLIC_URL', 'http://savnet.ntrkh.no/');
define('ADMIN_URL', PUBLIC_URL.'admin/');

define('BASEPATH', dirname(__FILE__).'/');
define('BASEPATH_INC', BASEPATH.'inc/');
define('BASEPATH_CLASS', BASEPATH.'class/');

define('ADMINPATH', BASEPATH.'admin/');
define('ADMINPATH_CLASS', ADMINPATH.'class/');
define('ADMINPATH_INC', ADMINPATH.'inc/');
define('ADMINPATH_GUI', ADMINPATH.'gui/');

define('SMS_FROM', 'RodeKors');
define('SMS_ACCOUNT', '');
define('SMS_TEXT', 
'TEST: Du er savnet!
Trykk på lenken for at vi skal se hvor du er:
'.PUBLIC_URL.'#unik_id');
define('SMS_NOT_SENT',
'OBS: Varsel ble ikke sendt til "#savnetnavn"');
define('SMS2_TEXT',
'Om du har GPS på telefonen, anbefaler vi at du aktiverer dette. Vanligvis finner du dette under Innstillinger -> Generelt, eller Innstillinger -> Plassering');
?>