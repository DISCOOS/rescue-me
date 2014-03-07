<?php
    
require('../config.php');
require(APP_PATH_INC.'locale.php'); // TODO: Move to ../config.php?


use RescueMe\User;
       
if(defined('USE_SILEX') && USE_SILEX) {
    
    // Verify logon information
    $user = User::verify();
    $_SESSION['logon'] = ($user instanceof User);
    
	$TWIG = array(
        'APP_TITLE' => TITLE,
        'APP_URI' => APP_URI,
        'APP_ADMIN_URI' => ADMIN_URI,
        'GOOGLE_API_KEY' => GOOGLE_API_KEY,
        'LOGIN' => $_SESSION['logon'],
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
        
		$TWIG['VIEW'] = _('Dashboard');
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
} else 
    
    require('router.php');

    // Was ajax request?
    if(is_ajax_request()) {
        die();
    }
    
    $user = User::current();
    if($user instanceof User) {
        $id = $user->id;
    }
    
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= $_ROUTER['name']." (".TITLE.")" ?></title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link rel="shortcut icon" href="<?=APP_URI?>img/favicon.ico" >
        <link href="<?=APP_URI?>css/admin.css" rel="stylesheet">
        <? if(GOOGLE_API_KEY !== '') { ?>
        <script src="//maps.googleapis.com/maps/api/js?key=<?=GOOGLE_API_KEY?>&sensor=false"></script>
        <? } ?>
        <script src="<?=APP_URI?>js/admin.js"></script>            
    </head>

    <body>
        
        <div class="container-narrow">
            
            <div class="masthead">
                <a class="lead no-wrap" href="<?=APP_URI?>"><b><?= TITLE ?></b></a>                    
                <ul class="nav nav-pills pull-right" style="display: <?= isset($_SESSION['logon']) ? 'block' : 'none' ?>;">
            <?php 
                
                if(($logon = isset($_SESSION['logon']) && $_SESSION['logon']) === true) {

             ?>
                    <li class="dropdown">
                        <a id="drop1" class="dropdown-toggle" data-toggle="dropdown"><?= _('Sporing') ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <? if($user->allow('read', 'operations') || $user->allow('read', 'operations.all')) { ?>
                            <li id="new-missing"><a role="menuitem" href="<?= ADMIN_URI ?>missing/new"><b class="icon icon-plus-sign"></b><?= NEW_TRACE ?></a></li>
                            <? } if ($user->allow('write', 'operations') || $user->allow('write', 'operations.all')) { ?>
                             <li class="divider"></li>
                            <li id="missing"><a role="menuitem" href="<?= ADMIN_URI ?>missing/list"><b class="icon icon-th-list"></b><?= TRACES ?></a></li>
                            <? } ?>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a id="drop3" class="dropdown-toggle no-wrap" data-toggle="dropdown">
                            <span class="visible-desktop"><?= $user->name ?><b class="caret"></b></span>
                            <span class="visible-phone"><?= _('System') ?><b class="caret"></b></span>
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
                            <? if ($user->allow('write', 'user', $id) || $user->allow('write', 'user.all')) { ?>
                            <li id="user"><a role="menuitem" href="<?= ADMIN_URI ?>user/edit/<?=$user->id?>"><b class="icon icon-user"></b><?=_('Konto')?></a></li>
                            <li id="passwd"><a role="menuitem" href="<?= ADMIN_URI ?>password/change/<?=$user->id?>"><b class="icon icon-lock"></b><?=_('Endre passord')?></a></li>
                            <li class="divider"></li>
                            <? } if ($user->allow('write', 'user.all')) { 
                                insert_item(NEW_USER, ADMIN_URI."user/new", "icon-plus-sign"); ?>
                            <li class="divider"></li>
                            <? } if ($user->allow('read', 'user.all')) { ?>
                            <li id="users"><a role="menuitem" href="<?= ADMIN_URI ?>user/list"><b class="icon icon-th-list"></b><?= USERS ?></a></li>
                            <? } if ($user->allow('read', 'roles')) { ?>
                            <li id="roles"><a role="menuitem" href="<?= ADMIN_URI ?>role/list"><b class="icon icon-th-list"></b><?= _('Roles') ?></a></li>
                            <? } if ($user->allow('read', 'logs')) { ?>
                            <li id="settings"><a href="<?= ADMIN_URI ?>logs"><b class="icon icon-list"></b><?= _('Logs') ?></a></li>
                            <li class="divider"></li>
                            <? } if ($user->allow('write', 'setup', $id) || $user->allow('write', 'setup.all')) { ?>
                            <li id="settings"><a href="<?= ADMIN_URI ?>setup"><b class="icon icon-wrench"></b><?= SETUP ?></a></li>
                            <li class="divider"></li>                            
                            <? } ?>
                            <li id="logout"><a data-toggle="modal" href="#confirm"><b class="icon icon-eject"></b><?= LOGOUT ?></a></li>
                        </ul>
                    </li>
                    
            <?php  } else { ?>  
                    
                    <li id="logout"><a href="<?=ADMIN_URI?>logon"><?= LOGIN ?></a></li>
                    
            <?php } ?>         
                    
                </ul>
            </div>
            
            <div>
            
            <?php
                
                if($logon) {
                    
                    // Insert modal confirmations
                    insert_dialog_confirm("confirm", "Bekreft", "Vil du logge ut?", ADMIN_URI."logout");
                    
                }                
                
                require('gui/' . str_replace("/",".",$_ROUTER['view']) . '.gui.php'); 
                
            ?>
                
            </div>                
        </div>
        
        <!-- Modal container filled by bootstrap.js -->
        <? 
            
//            insert_form_dialog("dialog", isset($dialog) ? $dialog : "", insert_progress(100, false));
            
        ?>

    </body>
</html>