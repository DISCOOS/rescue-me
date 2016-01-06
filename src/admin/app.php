<?php

/**
 * File containing: Admin application
 *
 * @copyright Copyright 2016 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 5. January 2016
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

use RescueMe\Admin\Context;
use RescueMe\Admin\Menu\SystemMenu;
use RescueMe\Admin\Provider\MenuServiceProvider;
use RescueMe\Locale;


$locale = Locale::getBrowserLocale();
set_system_locale(DOMAIN_ADMIN, $locale);

$app = new Silex\Application();
$app['debug'] = DEBUG;

// Enable sessions
$app->register(new Silex\Provider\SessionServiceProvider());

// Enable twig as template provider
$app->register(new Silex\Provider\TwigServiceProvider(),
    array('twig.path' => Context::getAdminPath().'views',
        'twig.options' => array('cache' => Context::getDataPath() . 'twig.cache')
    ));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Add I18n support
$app['twig']->addExtension(new \RescueMe\Twig\Extension\I18n());

// Register internal service  (note: order below matters)
$app->register(new \RescueMe\Admin\Provider\AccessServiceProvider());
$app->register(new \RescueMe\Admin\Provider\TemplateServiceProvider());
$app->register(new \RescueMe\Admin\Provider\PageServiceProvider());
$app->register(new \RescueMe\Admin\Provider\RowServiceProvider());
$app->register(new \RescueMe\Admin\Provider\MenuServiceProvider());
//$app->register(new \RescueMe\Admin\Provider\EditorServiceProvider());

// Register common menus
$menus = MenuServiceProvider::get($app);
//$menus->register(PageMenu::NAME, new PageMenu());
$menus->register(SystemMenu::NAME, new SystemMenu());


// Mount page controllers
$app->mount('/', new \RescueMe\Admin\Provider\AdminControllerProvider());
$app->mount('/missing', new \RescueMe\Admin\Provider\TraceControllerProvider());
$app->mount('/user', new \RescueMe\Admin\Provider\UserControllerProvider());
$app->mount('/password', new \RescueMe\Admin\Provider\PasswordControllerProvider());

//$app->mount('/setup', new RescueMe\Admin\Controller\Setup());
//$app->mount('/logs', new RescueMe\Admin\Controller\Logs());
//$app->mount('/user', new RescueMe\Admin\Controller\User());
//$app->mount('/role', new RescueMe\Admin\Controller\Role());
//$app->mount('/property', new RescueMe\Admin\Controller\Property());
//$app->mount('/alert', new RescueMe\Admin\Controller\Alert());
//$app->mount('/issue', new RescueMe\Admin\Controller\Issue());
//$app->mount('/trace', new RescueMe\Admin\Controller\Trace());
//$app->mount('/operation', new RescueMe\Admin\Controller\Operation());


$app->error(function (\Exception $e, $code) use ($app) {
        if ($app['debug']) {
            return;
        }
        // TODO: Map exception to response
    });

//    // Verify logon information
//    $user = User::verify();
//    $_SESSION['logon'] = ($user instanceof User);
//
//	$TWIG = array(
//        'APP_TITLE' => TITLE,
//        'APP_URI' => APP_URI,
//        'APP_ADMIN_URI' => ADMIN_URI,
//        'LOGIN' => $_SESSION['logon'],
//        'SMS_TEXT_MISSING' => T::_(T::ALERT_SMS),
//        'SMS_TEXT_GUIDE'  => T::_(T::ALERT_SMS_COARSE_LOCATION)
//	);
//
//	$app = new Silex\Application();
//	$app['debug'] = true;
//	$app->register(new Silex\Provider\TwigServiceProvider(),
//		array('twig.path' =>ADMIN_PATH.'views',
//			  #'twig.options' => array('cache' => APP_PATH. 'tmp/twig.cache')
//			  ));
//    $app['twig']->addExtension(new Twig_Extensions_Extension_I18n());
//
//   	// Force logon?
//	if($_SESSION['logon'] == false) {
//		$app->match('/', function () use ($app) {
//			global $TWIG;
//			require_once(ADMIN_PATH.'controllers/logon.controller.php');
//			return $app['twig']->render('login.twig', $TWIG);
//		});
//	}
//
//	// Main actions
//	$app->match('/{module}', function ($module) use ($app, $user) {
//		global $TWIG;
//		if($_SESSION['logon']==true) {
//            if($module == 'logon') {
//                $module = 'start';
//            } elseif($module == 'logout') {
//                $user->logout();
//                return $app->redirect(APP_URI);
//            }
//        }
//
//		$controller = ADMIN_PATH."controllers/$module.controller.php";
//		if(file_exists($controller))
//			require_once($controller);
//
//		$TWIG['VIEW'] = T_('Dashboard');
//	    return $app['twig']->render("$module.twig", $TWIG);
//
//	})->value('module', 'start')->assert('module', "logon|start|logout");
//
//	// Manager actions
//	$app->match('/{module}/{action}/{id}', function ($module, $action, $id) use ($app, $user) {
//		global $TWIG;
//		$view = rtrim("$module.$action",".");
//		$controller = ADMIN_PATH."controllers/$view.controller.php";
//		if(file_exists($controller))
//			require_once($controller);
//
//        $TWIG['VIEW'] = trim("$action $module");
//	    return $app['twig']->render("$view.twig", $TWIG);
//
//	})->value('id', false);

return $app;
