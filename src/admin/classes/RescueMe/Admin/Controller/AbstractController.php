<?php
/**
 * File containing: Abstract route controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 5. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Controller;

use LogicException;
use ReflectionMethod;
use RescueMe\Admin\Core\CallableResolver;
use RescueMe\Admin\Provider\AbstractControllerProvider;
use RescueMe\Admin\Security\Accessible;
use RescueMe\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Abstract route controller
 * @package RescueMe\Admin\Controller
 */
abstract class AbstractController extends CallableResolver {

    /**
     * Abstract RescueMe controller provider instance
     * @var AbstractControllerProvider
     */
    protected $provider;

    /**
     * Callable that returns the response matched with request routed to this controller
     * @var callable
     */
    protected $to;

    /**
     * Accessible object
     * @var Accessible $object
     */
    protected $object;

    /**
     * @var mixed
     */
    protected $context;

    /**
     * Array of ReflectionMethod of callable
     * @var ReflectionMethod
     */
    protected $methods = array();

    /**
     * Route path
     * @var string
     */
    protected $pattern;

    /**
     * Accepted request method
     * @var string
     */
    protected $accept;

    /**
     * Constructor
     *
     * @param AbstractControllerProvider $provider RescueMe controller provider instance.
     * @param string $accept Accept request method
     * @param string $pattern Route pattern to controller.
     * @param boolean|callable $to Callback that returns the response when matched.
     * @param Accessible $object Accessible object.
     * @param boolean|array|callable $context Request context.
     */
    function __construct($provider, $accept, $pattern, $to, $object, $context = false)
    {
        $this->pattern = $pattern;
        $this->provider = $provider;
        $this->to = $to;
        $this->object = $object;
        $this->context = $context;

        // Perform reflection only once
        $this->methods['to'] = $this->getMethod($to);
        $this->methods['object'] = $this->getMethod($object->getResolver());
        $this->methods['context'] = $this->getMethod($context);
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
     * @param UserInterface|string $user
     * @return mixed
     */
    private function isGranted($app, $attribute, $object, $user)
    {
        return $app['security']->isGranted($attribute, $object, $user);
    }

    /**
     * Assert access to given page
     * @param Application $app Silex application instance
     * @param string $access Page access type
     * @param object $object Access to object
     * @param UserInterface|string $user
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    final public function assertAccess($app, $access, $object, $user)
    {
        if (!$this->isGranted($app, $access, $object, $user)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Resolve user, object and context and assert access rights
     *
     * @param Application $app Silex application
     * @param Request $request Request object
     * @param mixed $id Object id
     * @throws \LogicException
     * @return array array($user, $object, $context)
     */
    final public function resolve(Application $app, Request $request, $id)
    {
        // Get current user
        $user =  $this->getUser($app);

        // Initialize default context
        $default = array('id' => $id, 'path' => $this->pattern, 'access' => $this->object->getMode());

        // Use accessible object as resolved object
        $object = $this->object;

        // Lazy object creation?
        if ($this->methods['object']) {
            $arguments = $this->getArguments($this->methods['object'], $app, $request, $user, $default);
            $object = call_user_func_array($object->getResolver(), $arguments);
        }

        // Only check access permissions for authenticated users (anonymous users are not allowed)
        if($user) {
            $this->assertAccess($app, $this->object->getMode(), $object, $user);
        }

        // Anonymous user?
        if(is_string($user)) {
            $user = false;
        }

        // Add resolved object to default context
        $default['object'] = $object;

        $context = $this->context;

        // Lazy context creation?
        if ($this->methods['context']) {
            $arguments = $this->getArguments($this->methods['context'], $app, $request, $user, $default);
            $context = call_user_func_array($context, $arguments);
        }

        // Merge context with default context
        $context = array_merge((array)$context, $default);

        return array($user, $object, $context);

    }

    /**
     * @param Request $request
     * @throws LogicException If request method is not accepted by controller
     */
    protected function assertRequest(Request $request)
    {
        // Assert request
        if(preg_match("#{$this->accept}#i", $request->getMethod()) === 0)
            throw new LogicException("Only method {$this->accept} accepted (found: {$request->getMethod()}");
    }

    /**
     * Route handle callback
     * @param Application $app Silex application
     * @param Request $request Request object
     * @throws LogicException If request is not accepted by controller
     * @return mixed
     */
    final function __invoke(Application $app, Request $request) {

        $this->assertRequest($request);

        $id = $request->attributes->getInt('id', false);

        // Resolve authenticated user, accessible object and request context
        list($user, $object, $context) = $this->resolve($app, $request, $id);

        // Ensure context contains required data (resolved values precedence over values in context)
        $context = array_merge($context, array(
                'id' => $id,
                'user' => $user,
                'object' => $object,
                'route' => $this->provider->getRouteName($this->pattern)
            ));

        // Forward to callable?
        if($this->methods['to']) {
            $arguments = $this->getArguments($this->methods['to'], $app, $request, $user, $context);
            return $this->forward($app, $request, $arguments, $user, $object, $context);
        }

        // Forward to implemented handle
        return $this->handle($app, $request, $id, $user, $object, $context);

    }

    /**
     * Forward request to handle callback.
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @param array $arguments Arguments passed to callable.
     * @param boolean|User $user Authenticated user.
     * @param boolean|object $object Resolved object.
     * @param boolean|array|callable $context Request context.
     * @throws LogicException If called but not implemented.
     * @return mixed
     */
    protected function forward(Application $app, Request $request, array $arguments, $user, $object, $context) {
        throw new LogicException('Not implemented, "$to" is callable.');
    }

    /**
     * Handle request implementation
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @param boolean|mixed $id Request id.
     * @param boolean|User $user Authenticated user.
     * @param boolean|object $object Resolved object.
     * @param boolean|array|callable $context Request context.
     * @throws LogicException If called but not implemented.
     * @return mixed
     */
    protected function handle(Application $app, Request $request, $id, $user, $object, $context) {
        throw new LogicException('Not implemented, "$to" is not callable.');
    }

}