<?php
/**
 * File containing: Request factory class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 4. august 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Core;


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Request factory class
 * @package RescueMe\Admin\Core
 */
class RequestFactory {

    /**
     * Silex application instance
     * @var Application $app
     */
    private $app;

    /**
     * Constructor
     * @param Application $app
     */
    protected function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Create factory instance
     * @param Application $app
     * @return RequestFactory
     */
    static function newInstance($app) {
        return new RequestFactory($app);
    }

    /**
     * Forward given request to path
     * @param string $path Path to controller or uri
     * @param Request $request Base request
     * @param boolean|array $attributes Set attributes
     * @param string $method Request method
     * @param mixed $content Request content
     * @param boolean $session Forward session
     * @return Request
     */
    function forward($path, $request, $attributes = false, $method = 'GET', $content = null, $session = true) {
        $parameters = array();
        if($request->isMethod($method)) {
            $parameters = $request->isMethod('GET') ? $request->query->all() : $request->request->all();
        }
        $subRequest = Request::create(
            $path,
            $method,
            $parameters,
            $request->cookies->all(),
            array(),
            $request->server->all(),
            $content
        );
        $subRequest->setMethod($method);
        $session = $session ? $request->getSession() : false;
        if ($session) {
            $subRequest->setSession($session);
        }
//        if($attributes) {
//            $subRequest->attributes->replace($attributes);
//        }
        return $subRequest;
    }


} 