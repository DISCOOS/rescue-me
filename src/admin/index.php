<?php
    
    require('../config.php');
    require('router.php');
   
if(defined('USE_SILEX') && USE_SILEX) {
	$lang = "en";
	if (isset($_GET['lang'])) $lang = $_GET['lang'];
	putenv("LC_ALL=$lang");
	setlocale(LC_ALL, $lang);
	bindtextdomain("messages", "/locale");
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain("messages");

	$TWIG = array('APP_TITLE' => TITLE,
				  'APP_URI' => APP_URI,
				  'GOOGLE_API_KEY' => GOOGLE_API_KEY,
				  'ADMIN_URI' => ADMIN_URI,
				  'APP_URI' => APP_URI,
				  'LOGON' => $_SESSION['logon'],
				  
				  );
	$app = new Silex\Application();
	$app['debug'] = true;
	$app->register(new Silex\Provider\TwigServiceProvider(),
		array('twig.path' =>ADMIN_PATH.'views',
			  'twig.options' => array('cache' => APP_PATH. 'tmp/twig.cache')
			  ));
    $app['twig']->addExtension(new Twig_Extensions_Extension_I18n());
    
   	// Force logon?
	if($_SESSION['logon'] == false) {
		$app->get('/', function () use ($app) {
			global $TWIG;
			if(file_exists(ADMIN_PATH.'controllers/logon.controller.php'))
				require_once(ADMIN_PATH.'controllers/logon.controller.php');
			
			return $app['twig']->render('logon.twig', $TWIG);
		});

	}

	
	$app->get('/{module}', function ($module) use ($app) {
		global $TWIG;
		$controller = ADMIN_PATH.'controllers/'.$module.'.controller.php';
		if(file_exists($controller))
			require_once($controller);
	    return $app['twig']->render($module.'.twig', $TWIG);
	});
	$app->get('/{module}/{id}', function ($module, $id) use ($app) {
		global $TWIG;
		$controller = ADMIN_PATH.'controllers/'.$module.'.controller.php';
		if(file_exists($controller))
			require_once($controller);

	    return $app['twig']->render($module.'.twig', $TWIG);
	});
	
	$app->run();
	
	die();
}
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
                            <li id="missing"><a role="menuitem" href="<?= ADMIN_URI ?>list/missing"><b class="icon icon-th-list"></b><?= TRACES ?></a></li>
                            <li class="divider"></li>
                            <li id="new-missing"><a role="menuitem" href="<?= ADMIN_URI ?>new/missing"><b class="icon icon-plus-sign"></b><?= NEW_TRACE ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a id="drop2" class="dropdown-toggle" data-toggle="dropdown"><?= SYSTEM ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li id="users"><a role="menuitem" href="<?= ADMIN_URI ?>list/users"><b class="icon icon-th-list"></b><?= USERS ?></a></li>
                            <li class="divider"></li>
                            <li id="new-user"><a role="menuitem" href="<?= ADMIN_URI ?>new/user"><b class="icon icon-plus-sign"></b><?= NEW_USER ?></a></li>
                            <li class="divider"></li>
                            <li id="settings"><a href="<?= ADMIN_URI ?>list/setup"><b class="icon icon-wrench"></b><?= SETUP ?></a></li>
                        </ul>
                    </li>
                    <li id="logout"><a data-toggle="modal" data-backdrop="false" href="#confirm"><?= LOGOUT ?></a></li>
                <?php 
                    
                        insert_dialog_confirm("confirm", "Bekreft", "Vil du logge ut?", ADMIN_URI."logout");
                        
                    } else {
                        
                 ?>  
                    
                    <li id="logout"><a href="<?= ADMIN_URI ?>logon"><?= LOGIN ?></a></li>
                    
                <?php 
                    
                    }
                        
                 ?>         
                </ul>
                <h3 class="muted"><a href="<?= APP_URI ?>"><?= TITLE ?></a></h3>
            </div>
            
            <?php 
                
                require_once('gui/' . $_ROUTER['file'] . '.gui.php'); 
                
            ?>
        </div>
    </body>
</html>