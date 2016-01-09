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


use RescueMe\Admin\Controller\BaseController;
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
     * Route read access type
     * @var string
     */
    const READ = Accessible::READ;

    /**
     * Route write access type
     * @var string
     */
    const WRITE = Accessible::WRITE;

    /**
     * GET route method type
     * @var string
     */
    const GET = 'get';

    /**
     * PUT route method type
     * @var string
     */
    const PUT = 'put';

    /**
     * PATCH route method type
     * @var string
     */
    const PATCH = 'patch';

    /**
     * DELETE route method type
     * @var string
     */
    const DELETE = 'delete';

    /**
     * OPTIONS route method type
     * @var string
     */
    const OPTIONS = 'options';

    /**
     * POSt route method type
     * @var string
     */
    const POST = PostController::TYPE;

    /**
     * PAGE route method type
     * @var string
     */
    const PAGE = PageController::TYPE;

    /**
     * JSON route method type
     * @var string
     */
    const JSON = JsonController::TYPE;

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
     * Get route name from type and pattern (variable are converted to path elements)
     * @param string $type Route type
     * @param string $pattern
     * @return string
     */
    public function getRouteName($type, $pattern)
    {
        // Remove variables from pattern
        $path = preg_replace('/(\/*\{.*\})/i', '/id', $pattern);

        return $type . ":" . trim($this->parent . '/' . $path, '/');
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
     * Route 'get' path to new BaseController instance with given access rights
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param callable $to Callback that returns the response when matched
     * @param Accessible $object Accessible object
     * @param array|callable $context POST context.
     * @return Controller
     */
    protected function get($controllers, $pattern, $to, $object, $context = array()) {

        // Get controller instance
        $controller = $controllers->get($pattern,
            new BaseController($this, self::GET, self::GET, $pattern, $to, $object, $context));

        return $controller->bind($this->getRouteName(self::GET, $pattern));
    }

    /**
     * Route 'put' path to new BaseController instance with given access rights
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param callable $to Callback that returns the response when matched
     * @param Accessible $object Accessible object
     * @param array|callable $context POST context.
     * @return Controller
     */
    protected function put($controllers, $pattern, $to, $object, $context = array()) {

        // Get controller instance
        $controller = $controllers->get($pattern,
            new BaseController($this, self::PUT, self::PUT, $pattern, $to, $object, $context));

        return $controller->bind($this->getRouteName(self::PUT, $pattern));
    }

    /**
     * Route 'patch' path to new BaseController instance with given access rights
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param callable $to Callback that returns the response when matched
     * @param Accessible $object Accessible object
     * @param array|callable $context POST context.
     * @return Controller
     */
    protected function patch($controllers, $pattern, $to, $object, $context = array()) {

        // Get controller instance
        $controller = $controllers->get($pattern,
            new BaseController($this, self::PATCH, self::PATCH, $pattern, $to, $object, $context));

        return $controller->bind($this->getRouteName(self::PATCH, $pattern));
    }

    /**
     * Route 'delete' path to new BaseController instance with given access rights
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param callable $to Callback that returns the response when matched
     * @param Accessible $object Accessible object
     * @param array|callable $context POST context.
     * @return Controller
     */
    protected function delete($controllers, $pattern, $to, $object, $context = array()) {

        // Get controller instance
        $controller = $controllers->get($pattern,
            new BaseController($this, self::DELETE, self::DELETE,$pattern, $to, $object, $context));

        return $controller->bind($this->getRouteName(self::DELETE, $pattern));
    }

    /**
     * Route 'options' path to new BaseController instance with given access rights
     * @param ControllerCollection $controllers Controller collection
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param callable $to Callback that returns the response when matched
     * @param Accessible $object Accessible object
     * @param array|callable $context POST context.
     * @return Controller
     */
    protected function options($controllers, $pattern, $to, $object, $context = array()) {

        // Get controller instance
        $controller = $controllers->get($pattern,
            new BaseController($this, self::OPTIONS, self::OPTIONS, $pattern, $to, $object, $context));

        return $controller->bind($this->getRouteName(self::OPTIONS, $pattern));
    }

    /**
     * Route 'post' path to new PostController instance with given access rights
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

        return $controller->bind($this->getRouteName(self::POST, $pattern));
    }

    /**
     * Route 'page' path to new PageController instance with given access rights
     * @param ControllerCollection $controllers Controller collection
     * @param string $template Template name
     * @param string $pattern Matched route pattern relative to root (or parent controller)
     * @param Accessible $object Accessible object
     * @param array|callable $context Page context.
     * @return Controller
     */
    protected function page($controllers, $template, $pattern, $object, $context = array())
    {
        // Get controller instance
        $controller = $controllers->get($pattern, new PageController($this, $template, $pattern, $object, $context));

        return $controller->bind($this->getRouteName(self::PAGE, $pattern));
    }

    /**
     * Route 'json' path to new JsonController instance with given access rights
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

        return $controller->bind($this->getRouteName(self::JSON, $pattern));
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