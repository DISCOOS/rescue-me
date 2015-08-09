<?php
/**
 * File containing: JSON request controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 5. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Controller;

use RescueMe\Admin\Provider\AbstractControllerProvider;
use RescueMe\Admin\Security\Accessible;
use RescueMe\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 * JSON request controller class
 *
 * @package RescueMe\Admin\Controller
 */
class JsonController extends AbstractController {

    /**
     * Accepted request method
     */
    const ACCEPT = 'GET';

    /**
     * Constructor
     * @param AbstractControllerProvider $provider RescueMe controller provider instance.
     * @param string $pattern Route path pattern to controller.
     * @param callable $to Callback that returns the response when matched.
     * @param Accessible $object Accessible object.
     * @param boolean|array|callable $context Request context.
     */
    function __construct($provider, $pattern, $to, $object, $context = false)
    {
        parent::__construct($provider, self::ACCEPT, $pattern, $to, $object, $context);
    }


    /**
     * @param Request $request
     * @throws \LogicException If request method is not accepted by controller
     */
    protected function assertRequest(Request $request)
    {
        parent::assertRequest($request);
        if(!$request->isXmlHttpRequest()) {
            throw new \LogicException("Only XHR requests are accepted");
        }
    }


    /**
     * JSON request handler
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @param array $arguments Arguments passed to callable.
     * @param boolean|User $user Authenticated user.
     * @param boolean|object $object Resolved object.
     * @param boolean|array|callable $context Request context.
     * @throws \LogicException If response from callable is not an array
     * @return mixed
     */
    protected function forward(Application $app, Request $request, array $arguments, $user, $object, $context)
    {
        $response = call_user_func_array($this->to, $arguments);
        if(is_string($response)) {
            $response = (array)$response;
        }
        if(!is_array($response)) {
            throw new \LogicException('Response is not an array.');
        }
        return $app->json(json_encode((array)$response));
    }


}