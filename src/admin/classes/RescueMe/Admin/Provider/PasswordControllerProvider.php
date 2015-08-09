<?php
/**
 * File containing: Password pages controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 29. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;

use RescueMe\Admin\Context;
use RescueMe\Admin\Core\RequestFactory;
use RescueMe\Admin\Security\Accessible;
use RescueMe\User;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Password pages controller class
 * @package RescueMe\Controller
 */
class PasswordControllerProvider extends AbstractControllerProvider {

    const REDIRECT = '/admin/start';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct('password');
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        // Creates a new parent controller based on the default route
        $controllers = $app['controllers_factory'];

        // Register redirects
        $this->redirect($controllers, '/', self::REDIRECT);

        // Create shared closures
        $object =  array('RescueMe\\User','get');

        $write = $this->writeAny($app);

        // Handle admin/password/reset
        $this->page($controllers, 'reset', $write);
        $this->post($controllers, 'reset', array($this, 'reset'), $write);

        $write = $this->write($app, 'user', 'RescueMe\\User', $object);

        // Handle admin/password/change/{id}
        $this->page($controllers, 'change', $write);
        $this->post($controllers, 'change', array($this, 'reset'), $write);

        return $controllers;
    }

    /**
     * Assert password.
     *
     * Returns true if valid, error message otherwise
     *
     * @param Request $request
     * @returns boolean|string
     */
    public static function assertPassword(Request $request) {

        $password = $request->request->get('password', false);

        // Sanity checks
        if ($password !== $request->request->get('repeat-pwd', false)) {
            return sentences(array(T_('Password mismatch'), T_('Enter same password twice')));
        }
        // Ensure password length
        if (strlen($password) < Context::getSecurityPasswordLength()) {
            return sentences(array(
                sprintf(T_('Minimum %1$s characters'), Context::getSecurityPasswordLength()),
                T_('Enter new password twice')
            ));
        }
        return true;
    }

    /**
     * @param Application $app Silex application
     * @param User $user Authenticated user
     * @param string $password Raw password
     * @return string
     */
    public static function encodePassword(Application $app, User $user, $password) {
        return $app['security.encoder_factory']->getEncoder($user)
            ->encodePassword($password, Context::getSecuritySalt());
    }

    /**
     * Handle password reset POST requests
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function reset(Application $app, Request $request) {

        // Sanity checks
        if(!($email = input_post_email('email', false))) {
            $response = T_('Invalid email format');
        }
        // Attempt to recover password for given email
        elseif(User::recover($email)) {
            $response = $app->redirect('/admin/login');
        }
        else {
            // Render page again and show message
            $response = T_('Email not registered');
        }

        // Forward message?
        if(is_string($response)) {
            $response = $app->handle(RequestFactory::newInstance($app)->forward(
                '/admin/password/reset', $request, array('message' => $response)));

        }
        return $response;

    }

    /**
     * Handle password change POST requests
     * @param Application $app
     * @param Request $request
     * @param User $change Change password for given user
     * @return mixed
     */
    public function change(Application $app, Request $request, User $change)  {

        $response = self::assertPassword($request);
        if(true === $response) {
            $password = $request->request->get('password', false);
            if ($change->password($this->encodePassword($app, $change, $password))) {
                $response = $app->redirect('/admin/login');
            }
            else {
                // Render page again and show message
                $response = T_('Password not changed');
            }
        }

        // Forward message?
        if (is_string($response)) {
            $response = $app->handle(
                RequestFactory::newInstance($app)->forward(
                    $request->getRequestUri(),
                    $request, array('message' => $response)
                )
            );
        }

        return $response;
    }



}