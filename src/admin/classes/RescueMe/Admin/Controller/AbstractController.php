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
use RescueMe\Admin\Context;
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
    protected $accessible;

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
     * @param Accessible $accessible Accessible object.
     * @param boolean|array|callable $context Request context.
     */
    function __construct($provider, $accept, $pattern, $to, $accessible, $context = false)
    {
        $this->pattern = $pattern;
        $this->provider = $provider;
        $this->to = $to;
        $this->accessible = $accessible;
        $this->context = $context;

        // Perform reflection only once
        $this->methods['to'] = $this->getMethod($to);
        $this->methods['object'] = $this->getMethod($accessible->getResolver());
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
     * Assert access granted to authenticated user and resolve application context from current request
     *
     * @param Application $app Silex application
     * @param Request $request Request object
     * @param mixed $id Object id
     * @throws \LogicException
     * @return array array($user, $context)
     */
    final public function resolve(Application $app, Request $request, $id)
    {
        // Get current user
        $user =  $this->getUser($app);

        // Get route access mode from accessible object
        $mode = $this->accessible->getMode();

        // Get route name from pattern
        $name = $this->provider->getRouteName($this->pattern);

        // Initialize default context
        $default = array(
            Context::ID => $id,
            Context::ROUTE_PATTERN => $this->pattern,
            Context::ROUTE_ACCESS => $mode,
            Context::ROUTE_NAME => $name,
            Context::ACCESSIBLE => $this->accessible
        );

        // Use accessible object as resolved object
        $object = $this->accessible;

        // Lazy object creation?
        if ($this->methods['object']) {
            $arguments = $this->getArguments($this->methods['object'], $app, $request, $user, $default);
            $object = call_user_func_array($object->getResolver(), $arguments);
        }

        // Only check access permissions for authenticated users (anonymous users are not allowed)
        if($user) {
            $this->assertAccess($app, $this->accessible->getMode(), $object, $user);
        }

        // Anonymous user?
        if(is_string($user)) {
            $user = false;
        }

        // Unresolvable object?
        if($object instanceof Accessible) {
            $object = false;
        }

        // Add resolved object to default context
        $default[Context::OBJECT] = $object;

        $context = $this->context;

        // Lazy context creation?
        if ($this->methods['context']) {
            $arguments = $this->getArguments($this->methods['context'], $app, $request, $user, $default);
            $context = call_user_func_array($context, $arguments);
        }

        // Merge default context with resolved context
        $context = array_merge($default, (array)$context);

        // Ensure context contains required values
        $context = array_merge($context, array(
                Context::ID => $id,
                Context::USER => $user,
                Context::OBJECT => $object,
                Context::ACCESSIBLE => $this->accessible,
                Context::ROUTE_NAME => $name,
                Context::ROUTE_ACCESS => $mode,
                Context::ROUTE_PATTERN => $this->pattern,
            ));

        return array($user, $context);

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

        $id = $request->attributes->getInt(Context::ID, false);

        // Assert access granted to authenticated user and resolve application context from current request
        list($user, $context) = $this->resolve($app, $request, $id);

        // Update admin context
        Context::extend($context);

        $app['context'] = Context::toArray(true);

        // Forward to callable?
        if($this->methods['to']) {
            $arguments = $this->getArguments($this->methods['to'], $app, $request, $user, $context);
            return $this->forward($app, $request, $arguments);
        }

        // Forward to implemented handle
        return $this->handle($app, $request);

    }

    /**
     * Forward request to handle callback.
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @param array $arguments Arguments passed to callable.
     * @throws LogicException If called but not implemented.
     * @return mixed
     */
    protected function forward(Application $app, Request $request, array $arguments) {
        throw new LogicException('Not implemented, "$to" is callable.');
    }

    /**
     * Handle request implementation.
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @throws LogicException If called but not implemented.
     * @return mixed
     */
    protected function handle(Application $app, Request $request) {
        throw new LogicException('Not implemented, "$to" is not callable.');
    }

}