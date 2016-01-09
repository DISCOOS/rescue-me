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
use RescueMe\User;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        // Password reset is allowed outside security realm
        $write = $this->writeAny($app);

        // Handle admin/password/reset
        $this->page($controllers, 'password.reset', 'reset', $write);
        $this->post($controllers, 'reset', array($this, 'reset'), $write);

        // Handle admin/password/reset/id
        $this->put($controllers, 'reset/{id}', array($this, 'reset'), $write)->assert('id', '\d+');

        // Only allow users to change own password
        $write = $this->write($app, 'user', 'RescueMe\\User', $object);

        // Handle admin/password/change/{id}
        $this->page($controllers, 'password.change', 'change/{id}', $write)->assert('id', '\d+');
        $this->post($controllers, 'change/{id}', array($this, 'change'), $write)->assert('id', '\d+');

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

        if(!($id = $request->query->get('id', false))) {
            $response = $this->resetFromId($app, $id);
        } else if(!($email = $request->request->get('email', false))) {
            $response = $this->resetFromEmail($app, $email);
        } else {
            $response = T_('Illegal operation');
        }

        // Forward message?
        if(is_string($response)) {
            $response = $app->handle(RequestFactory::newInstance($app)->forward(
                '/admin/password/reset', $request, array('message' => $response)));

        }
        return $response;

    }

    /**
     * @param Application $app
     * @param integer $id
     * @return string|RedirectResponse
     */
    private function resetFromId(Application $app, $id) {

        // Attempt to recover password for given id
        if(($result = User::recover($id)) !== true) {
            return $app->redirect('/admin/login');
        }
        return $result;
    }

    /**
     * @param Application $app
     * @param string $email
     * @return string|RedirectResponse
     */
    private function resetFromEmail(Application $app, $email) {

        // Sanity checks
        if(!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) {
            return T_('Invalid email format');
        }
        // Attempt to recover password for given email
        elseif(User::recover($email)) {
            return $app->redirect('/admin/login');
        }
        return T_('Email not registered');
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