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
use RescueMe\Twig\Extension\I18n;


$locale = Locale::getBrowserLocale();
set_system_locale(DOMAIN_ADMIN, $locale);

$app = new Silex\Application(
    array('debug' => DEBUG)
);

// Enable sessions
$app->register(new Silex\Provider\SessionServiceProvider());

// Enable security
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\RememberMeServiceProvider());

// Enable twig as template provider
$app->register(new Silex\Provider\TwigServiceProvider(),
    array('twig.path' => Context::getAdminPath().'views',
        'twig.options' => array('cache' => Context::getDataPath() . 'twig.cache')
    ));

// Add I18n support (workaround that prevents exception thrown by the security provider)
$app['twig'] = $app->share($app->extend('twig', function(/** @var Twig_Environment */ $twig) {
    $twig->addExtension(new I18n());
    return $twig;
}));

// Enable URL generator for named routes.
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Register internal service  (note: order below matters)
$app->register(new \RescueMe\Admin\Provider\AccessServiceProvider());
$app->register(new \RescueMe\Admin\Provider\TemplateServiceProvider());
$app->register(new \RescueMe\Admin\Provider\PageServiceProvider());
$app->register(new \RescueMe\Admin\Provider\RowServiceProvider());
$app->register(new \RescueMe\Admin\Provider\MenuServiceProvider());

// Register common menus
$menus = MenuServiceProvider::get($app);
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

return $app;
