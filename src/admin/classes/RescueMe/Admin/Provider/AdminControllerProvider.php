<?php
/**
 * File containing: Admin pages controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 29. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;

use RescueMe\Admin\Context;
use RescueMe\Admin\Core\LegacyPasswordEncoder;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin pages application controller class
 * @package RescueMe\Controller
 */
class AdminControllerProvider extends AbstractControllerProvider {

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        // Enable login
        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'admin' => array(
                    'pattern' => '^.*$',
                    'anonymous' => true,
                    'form' => array(
                        'login_path' => '/login',
                        'check_path' => '/login/check'
                    ),
                    'logout' => array(
                        'logout_path' => '/logout'
                    ),
                    'remember_me' => array(
                        'key' => Context::getSecuritySalt(),
                        /* Other options */
                    ),
                    'users' => $app->share(function () {
                        return new UserProvider();
                    })
                )
            )
        ));

        // Enable conditional 'remember me', login.twig
        $app->register(new RememberMeServiceProvider());

        // Ensure login is allowed as anonymous user
        $app['security.access_rules'] = array(
            // Prevent redirect loop by allowing anonymous user
            array('^/login$|^/user/request$|^/continue$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
            // Only allow fully authenticated (this session) and remembered (session cookie) to access secure area
            array('^/.*$', array('IS_AUTHENTICATED_FULLY', 'IS_AUTHENTICATED_REMEMBERED')),
        );

        // TODO: Add control of backwards capability to configuration (new installations should use default encoder)
        // Use custom password encoder for backward capability
        $app['security.encoder.digest'] = $app->share(function () {
            return new LegacyPasswordEncoder();
        });

        // PHP 5.3 workaround
        $page = $this;

        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        // Register redirects
        $this->redirect($controllers, '/', 'start');

        $readAny = $this->readAny($app);

        // Handle admin/login (logout is handled automatically by the security provider)
        $this->page($controllers, 'login', $readAny, array('login' => true))->before(
            function(Request $request) use($app, $page) {
                // Already authenticated?
                if($page->isSecure($app)) {
                    return $app->redirect($request->getUriForPath('/start'));
                }
                return null;
            });

        // Handle admin/start
        $this->page($controllers, 'start', $readAny);

        return $controllers;
    }
}