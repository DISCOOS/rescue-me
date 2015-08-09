<?php
/**
 * File containing: Response factory class
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
use Symfony\Component\HttpFoundation\Response;

/**
 * Response factory class
 * @package RescueMe\Admin\Core
 */
class ResponseFactory {

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
     * @return ResponseFactory
     */
    static function newInstance($app) {
        return new ResponseFactory($app);
    }

    /**
     * Create 403 'Forbidden' for given request
     * @param Request $request
     * @return Response
     */
    function failed($request) {
        $elements = array(
            sprintf(T_('Action [%1$s] not executed'), $request->getRequestUri()),
            sprintf('<a href="%1$s/logs">%2$s</a>',$request->getBasePath(), T_('Check logs'))
        );
        return $this->forbidden(sentences($elements));
    }

    /**
     * Create 200 'OK' response
     * @param $message
     * @return Response
     */
    function ok($message = '') {
        return new Response($message);
    }


    /**
     * Create 403 'Forbidden' response
     * @param string $message
     * @return Response
     */
    function forbidden($message='') {
        return new Response($message, 403);
    }


    /**
     * Generate url
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    function generateURL($name, $arguments = array()) {
        return $this->app['url_generator']->generate($name, $arguments);
    }

} 