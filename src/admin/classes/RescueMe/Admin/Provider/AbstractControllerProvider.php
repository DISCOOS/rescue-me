<?php
/**
 * File containing: Abstract page controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 2. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;


use RescueMe\Admin\Controller\JsonController;
use RescueMe\Admin\Controller\PageController;
use RescueMe\Admin\Controller\PostController;
use RescueMe\Admin\Core\RequestFactory;
use RescueMe\Admin\Security\Accessible;
use RescueMe\User;
use Silex\Application;
use Silex\Controller;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Abstract controller provider class
 *
 * @package RescueMe\Admin\Controller
 */
abstract class AbstractControllerProvider implements ControllerProviderInterface
{
    /**
     * Page read access type
     * @var string
     */
    const READ = Accessible::READ;

    /**
     * Page write access type
     * @var string
     */
    const WRITE = Accessible::WRITE;


    /**
     * Uri to parent controller
     * @var string
     */
    private $parent;

    /**
     * Constructor
     * @param $parent
     */
    function __construct($parent = '/')
    {
        $this->parent = trim($parent, '/');
    }

    /**
     * Get route name from pattern (variable are converted to path elements)
     * @param string $pattern
     * @return string
     */
    public function getRouteName($pattern)
    {
        // Remove variables from pattern
        $path = preg_replace('/(\/*\{.*\})/i', '/id', $pattern);

        return trim($this->parent . '/' . $path, '/');
    }


    /**
     * Allow read access to given object
     * @param Application $app Silex application
     * @param boolean|string $name Accessible object name
     * @param string $class Accessible object class
     * @param boolean|object|callable Accessible object resolver
     * @return Accessible
     */
    protected function read($app, $name, $class, $object = false) {
        $read = Accessible::read($name, $class, $object);
        AccessServiceProvider::get($app)->register($read);
        return $read;
    }

    /**
     * Allow any read access to silex application
     * @param Application $app Silex application
     * @return Accessible
     */
    protected function readAny($app) {
        $read = Accessible::readAny();
        AccessServiceProvider::get($app)->register($read);
        return $read;
    }

    /**
     * Allow write access to given object
     * @param Application $app Silex application
     * @param boolean|string $name Accessible object name
     * @param string $class Accessible object class
     * @param boolean|object|callable $object Accessible object resolver
     * @return Accessible
     */
    protected function write($app, $name, $class, $object = false) {
        $write = Accessible::write($name, $class, $object);
        AccessServiceProvider::get($app)->register($write);
        return $write;
    }

    /**
     * Allow write access to given object
     * @param Application $app Silex application
     * @return Accessible
     */
    protected function writeAny($app) {
        $write = Accessible::writeAny();
        AccessServiceProvider::get($app)->register($write);
        return $write;
    }


    /**
     * Get route pattern
     * @param string $uri Uri relative to root (or parent controller)
     * @param Accessible $object Match page id with given object. Callable are invoked with argument $id.
     * @return string
     */
    protected function getPattern($uri, $object) {
        return $object->isResolvable() ? trim($uri, '/') . '/{id}' : trim($uri, '/');
    }


    /**
     * Check if current user is authenticated
     * @param Application $app
     * @return boolean
     */
    public function isSecure($app) {
        return $app['security']->isGranted('IS_AUTHENTICATED_FULLY')
        || $app['security']->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    /**
     * Get current user
     * @param Application $app Silex application instance
     * @return User|boolean
     */
    final public function getUser($app) {
        // Symfony 2.6+
        //$token = $app['security.token_storage']->getToken();
        // Symfony 2.3/2.5
        $token = $app['security']->getToken();
        return (null !== $token ? $token->getUser() : false);
    }


    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (fully authenticated user)
     *   a string               (anonymously authenticated user)
     *
     * @param Application $app Silex application
     * @param string $attribute Access attribute
     * @param object $object Access to object
     * @param UserInterface|array|string $user
     * @return mixed
     */
    public function isGranted($app, $attribute, $object, $user)
    {
        if(is_array($user)) {
            $minimal = new User();
            $minimal->id = $user['id'];
            $user = $minimal;
        }

        return $app['security']->isGranted($attribute, $object, $user);
    }


    /**
     * Route path to page controller with given access rights
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param Accessible $object Accessible object
     * @param array|callable $context Page context.
     * @return Controller
     */
    protected function page($controllers, $pattern, $object, $context = array())
    {
        // Get controller instance
        $controller = $controllers->get($pattern, new PageController($this, $pattern, $object, $context));

        return $controller->bind($this->getRouteName($pattern));
    }


    /**
     * Route path to form post controller
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param callable $to Callback that returns the response when matched
     * @param Accessible $object Accessible object
     * @param array|callable $context POST context.
     * @return Controller
     */
    protected function post($controllers, $pattern, $to, $object, $context = array()) {

        // Get controller instance
        $controller = $controllers->post($pattern, new PostController($this, $pattern, $to, $object, $context));

        return $controller->bind($this->getRouteName($pattern));
    }

    /**
     * Route path to json controller
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param callable $to Callback that returns the response when matched
     * @param Accessible $object Accessible object
     * @param array|callable $context JSON context.
     * @return Controller
     */
    protected function json($controllers, $pattern, $to, $object, $context = array()) {

        // Get controller instance
        $controller = $controllers->get($pattern, new JsonController($this, $pattern, $to, $object, $context));

        return $controller->bind($this->getRouteName($pattern));
    }

    /**
     * Redirect route to given uri
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Uri relative to root (or parent controller)
     * @param $to
     * @return Controller
     */
    public function redirect($controllers, $pattern, $to)
    {
        return $controllers->get(
            $pattern,
            function (Application $app) use ($to) {
                return $app->redirect($to);
            }
        );
    }

    protected function confirmation(Application $app, $id, User $user, $object, $message, $action)
    {
        $context = array(
            'message' => $message,
            'action' => $action
        );
        return PageServiceProvider::get($app)->page(
            $app, 'confirmation.twig', $id, $user, $object, $context);
    }

    protected function error(Application $app, Request $request, $message)
    {
        return $this->alert($app, $request, $message, 'error');
    }


    protected function info(Application $app, Request $request, $message)
    {
        return $this->alert($app, $request, $message, 'info');
    }

    protected function warn(Application $app, Request $request, $message)
    {
        return $this->alert($app, $request, $message, 'warning');
    }

    protected function alert(Application $app, Request $request, $message, $type = 'info')
    {
        return $app->handle(
            RequestFactory::newInstance($app)->forward(
                $request->getRequestUri(),
                $request,
                $request->attributes->all(),
                'GET',
                array('alerts' => array($message, 'class' => "alert-$type"))
            )
        );
    }


}