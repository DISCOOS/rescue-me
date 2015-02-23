<?php

require('config.php');

use RescueMe\User;
use RescueMe\SMS\T;

if(defined('USE_SILEX') && USE_SILEX) {
    
    // Verify logon information
    $user = User::verify();
    $_SESSION['logon'] = ($user instanceof User);
    
	$TWIG = array(
        'APP_TITLE' => TITLE,
        'APP_URI' => APP_URI,
        'APP_ADMIN_URI' => ADMIN_URI,
        'LOGIN' => $_SESSION['logon'],
        'SMS_TEXT_MISSING' => T::_(T::ALERT_SMS),
        'SMS_TEXT_GUIDE'  => T::_(T::ALERT_SMS_COARSE_LOCATION)
	);
    
	$app = new Silex\Application();
	$app['debug'] = true;
	$app->register(new Silex\Provider\TwigServiceProvider(),
		array('twig.path' =>ADMIN_PATH.'views',
			  #'twig.options' => array('cache' => APP_PATH. 'tmp/twig.cache')
			  ));
    $app['twig']->addExtension(new Twig_Extensions_Extension_I18n());
    
   	// Force logon?
	if($_SESSION['logon'] == false) {
		$app->match('/', function () use ($app) {
			global $TWIG;
			require_once(ADMIN_PATH.'controllers/logon.controller.php');
			return $app['twig']->render('logon.twig', $TWIG);
		});
	}

	// Main actions
	$app->match('/{module}', function ($module) use ($app, $user) {
		global $TWIG;
		if($_SESSION['logon']==true) {
            if($module == 'logon') {
                $module = 'start';
            } elseif($module == 'logout') {
                $user->logout();
                return $app->redirect(APP_URI);
            }
        }
        
		$controller = ADMIN_PATH."controllers/$module.controller.php";
		if(file_exists($controller))
			require_once($controller);
        
		$TWIG['VIEW'] = T_('Dashboard');
	    return $app['twig']->render("$module.twig", $TWIG);
        
	})->value('module', 'start')->assert('module', "logon|start|logout");
	
	// Module actions
	$app->match('/{module}/{action}/{id}', function ($module, $action, $id) use ($app, $user) {
		global $TWIG; 
		$view = rtrim("$module.$action",".");
		$controller = ADMIN_PATH."controllers/$view.controller.php";
		if(file_exists($controller))
			require_once($controller);

        $TWIG['VIEW'] = trim("$action $module");
	    return $app['twig']->render("$view.twig", $TWIG);
        
	})->value('id', false);
	
	$app->run();
	
	die();
    
} else {                   
    
    require('router.php');

    // Was ajax request?
    if(is_ajax_request()) {
        die();
    }
    
    $user = User::current();
    if($user instanceof User) {
        $id = $user->id;
    }
    
}?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?= $_ROUTER['name']." (".TITLE.")" ?></title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="apple-mobile-web-app-title" content="<?=TITLE?>" >
        <link rel="shortcut icon" href="<?=APP_URI?>img/favicon.ico" >
        <link rel="apple-touch-icon" href="<?=APP_URI?>img/rescueme-non-trans.png" >
        <link href="<?=APP_URI?>css/admin.css" rel="stylesheet">

        <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?sensor=false"></script>
        <script type="text/javascript" src="<?=APP_URI?>js/admin.js"></script>
    </head>

    <body>
        
        <div class="container-narrow">
            
            <div class="row-fluid masthead">
                <a class="lead no-wrap" href="<?=APP_URI?>"><b><?= TITLE ?></b></a>                    
                <ul class="nav nav-pills pull-right" style="display: <?= isset($_SESSION['logon']) ? 'block' : 'none' ?>;">
            <?
                
            if(($logon = isset($_SESSION['logon']) && $_SESSION['logon']) === true) {
                    insert_trace_menu($user);
                    insert_system_menu($user);
             } else { ?>
                    
                    <li id="logout"><a href="<?=ADMIN_URI?>logon"><?=T_('Login')?></a></li>
                    
            <? } ?>
                    
                </ul>
            </div>
            
            <div class="row-fluid">
            
            <?

                // Insert confirm dialog
                insert_dialog_confirm("confirm");


                $view = str_replace('/','.',$_ROUTER['view']);
                require(ADMIN_PATH . implode(DIRECTORY_SEPARATOR, array('gui',$view.'.gui.php')));

                //echo date('Y-m-d H:i:s').' '.date_default_timezone_get();

            ?>
                
            </div>
            <? require(APP_PATH.implode(DIRECTORY_SEPARATOR, array('gui','footer.gui.php'))); ?>
        </div>
        
    </body>
</html>