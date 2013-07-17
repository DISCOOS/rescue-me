<?php
    
require('../config.php');
require(APP_PATH_INC.'locale.php'); // TODO: Move to ../config.php?


use RescueMe\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
       
if(defined('USE_SILEX') && USE_SILEX) {
    
    // Verify logon information
    $user = new User();
    $_SESSION['logon'] = $user->verify();
    
	$TWIG = array(
        'APP_TITLE' => TITLE,
        'APP_URI' => APP_URI,
        'APP_ADMIN_URI' => ADMIN_URI,
        'GOOGLE_API_KEY' => GOOGLE_API_KEY,
        'LOGON' => $_SESSION['logon'],
        'SMS_TEXT_MISSING' => SMS_TEXT,
        'SMS_TEXT_GUIDE'  => SMS2_TEXT
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
		$app->get('/', function () use ($app) {
			global $TWIG;
            $controller = ADMIN_PATH.'controllers/logon.controller.php';
			if(file_exists($controller))
				require_once($controller);
			
			return $app['twig']->render('logon.twig', $TWIG);
		});
	}

	// Main actions
	$app->match('/{action}', function ($action) use ($app, $user) {
		global $TWIG;
        
		if($_SESSION['logon']==true) {
            if($action == 'logon') {
                $action = 'start';
            } elseif($action == 'logout') {
                
                $user->logout();
                
                return $app->redirect(APP_URI);                
            }
        }
        
		$controller = ADMIN_PATH."controllers/$action.controller.php";
		if(file_exists($controller))
			require_once($controller);
        
		$TWIG['VIEW'] = $action;
	    return $app['twig']->render("$action.twig", $TWIG);
        
	})->value('module', 'start')->assert('module', "login|start");
	
	// Module actions
	$app->match('/{module}/{action}/{id}', function ($action, $module, $id) use ($app) {
		global $TWIG; 
        
		$view = rtrim("$module.$action",".");
		$controller = ADMIN_PATH."controllers/$view.controller.php";
		if(file_exists($controller))
			require_once($controller);

        $TWIG['VIEW'] = trim("$action $module");
	    return $app['twig']->render("$view.twig", $TWIG);
        
	})->value('action', '')->value('id', false);
	
	$app->run();
	
	die();
} else 
    require('router.php');

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= TITLE ?> - <?= $_ROUTER['name'] ?></title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link href="<?=APP_URI?>css/admin.less" rel="stylesheet/less">
        <? if(GOOGLE_API_KEY !== '') { ?>
        <script src="//maps.googleapis.com/maps/api/js?key=<?=GOOGLE_API_KEY?>&sensor=false"></script>
        <? } ?>
        <script src="<?=APP_URI?>js/admin.js"></script>
    </head>

    <body>
        
        <div class="container-narrow">
            
            <div class="masthead">

                <ul class="nav nav-pills pull-right" style="display: <?= $_SESSION['logon'] ? 'block' : 'none' ?>;">
                <?php 
                    
                    if($_SESSION['logon']) {
                        
                 ?>
                    <li class="dropdown">
                        <a id="drop1" class="dropdown-toggle" data-toggle="dropdown"><?= MISSING_PERSONS ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <li id="new-missing"><a role="menuitem" href="<?= ADMIN_URI ?>missing/new"><b class="icon icon-plus-sign"></b><?= NEW_TRACE ?></a></li>
                            <li class="divider"></li>
                            <li id="missing"><a role="menuitem" href="<?= ADMIN_URI ?>missing/list"><b class="icon icon-th-list"></b><?= TRACES ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a id="drop2" class="dropdown-toggle" data-toggle="dropdown"><?= SYSTEM ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li id="new-user"><a role="menuitem" href="<?= ADMIN_URI ?>user/new"><b class="icon icon-plus-sign"></b><?= NEW_USER ?></a></li>
                            <li class="divider"></li>
                            <li id="users"><a role="menuitem" href="<?= ADMIN_URI ?>user/list"><b class="icon icon-th-list"></b><?= USERS ?></a></li>
                            <li class="divider"></li>
                            <li id="settings"><a href="<?= ADMIN_URI ?>setup/list"><b class="icon icon-wrench"></b><?= SETUP ?></a></li>
                        </ul>
                    </li>
                    <li id="logout"><a data-toggle="modal" data-backdrop="false" href="#confirm"><?= LOGOUT ?></a></li>
                <?php 
                    
                        insert_dialog_confirm("confirm", "Bekreft", "Vil du logge ut?", ADMIN_URI."logout");
                        
                    } else {
                        
                 ?>  
                    
                    
                    <li id="logout"><a href="<?=ADMIN_URI?>logon"><?= LOGIN ?></a></li>
                    
                <?php 
                    
                    }
                        
                 ?>         
                </ul>
                <h3 class="muted"><a href="<?=APP_URI?>"><?= TITLE ?></a></h3>
            </div>
            
            <?php 
                
                require_once('gui/' . str_replace("/",".",$_ROUTER['view']) . '.gui.php'); 
                
            ?>
        </div>
    </body>
</html>