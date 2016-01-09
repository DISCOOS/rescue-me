<?php
/**
 * File containing: Base controller class
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
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 * Base controller class
 *
 * @package RescueMe\Admin\Controller
 */
class BaseController extends AbstractController {

    /**
     * Constructor
     * @param AbstractControllerProvider $provider RescueMe controller provider instance.
     * @param string $accept Accept http method
     * @param string $type Route type
     * @param string $pattern Route path pattern to controller.
     * @param callable $to Callback that returns the response when matched.
     * @param Accessible $object Accessible object.
     * @param boolean|array|callable $context Request context.
     */
    function __construct($provider, $accept, $type, $pattern, $to, $object, $context = false)
    {
        parent::__construct($provider, $accept, $type, $pattern, $to, $object, $context);
    }


    /**
     * Get request handler
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @param array $arguments Arguments passed to callable.
     * @throws \LogicException If response from callable is not an array
     * @return mixed
     */
    protected function forward(Application $app, Request $request, array $arguments)
    {
        return call_user_func_array($this->to, $arguments);
    }


}