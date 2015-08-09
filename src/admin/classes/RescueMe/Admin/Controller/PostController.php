<?php
/**
 * File containing: POST request controller class
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
use RescueMe\Admin\Security\AccessVoter;


/**
 * POST request controller class
 *
 * @package RescueMe\Admin\Controller
 */
class PostController extends AbstractController {

    /**
     * Accepted request method
     */
    const ACCEPT = 'POST';

    /**
     * Constructor (post is always 'write' access)
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
     * POST request handler
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @param array $arguments Arguments passed to callable.
     * @param boolean|User $user Authenticated user.
     * @param boolean|object $object Resolved object.
     * @param boolean|array|callable $context Request context.
     * @return mixed
     */
    protected function forward(Application $app, Request $request, array $arguments, $user, $object, $context)
    {
        return call_user_func_array($this->to, $arguments);
    }


}