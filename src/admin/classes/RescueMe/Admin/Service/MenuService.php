<?php
/**
 * File containing: Menu service class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 6. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Service;

use ReflectionMethod;
use RescueMe\Admin\Core\CallableResolver;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Menu service class
 * @package RescueMe\Admin\Service
 */
class MenuService extends CallableResolver {

    /**
     * Template service
     * @var TemplateService
     */
    private $service;

    /**
     * Item data
     * @var array|callable
     */
    private $items = array();

    /**
     * Array of ReflectionMethod of callable
     * @var ReflectionMethod
     */
    protected $methods = array();


    /**
     * Constructor
     * @param TemplateService $service
     */
    function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * Connect service with data source
     * @param array|callable $items Item data
     */
    public function connect($items) {
        // Store data
        $this->items= $items;

        // Perform reflection only once
        $this->methods['items'] = $this->getMethod($items);
    }

    /**
     * Render request into menu as html
     * @param Application $app Silex application
     * @param Request $request Request instance
     * @param boolean|mixed|$user Current user
     * @return string
     */
    public function render(Application $app, Request $request, $user) {

        $items = $this->items;

        // Lazy rows creation?
        if ($method = $this->methods['items']) {
            $arguments = $this->getArguments($method, $app, $request, $user);
            $items = call_user_func_array($items, $arguments);
        }

        // Finished
        return $this->service->render($app, 'menu.twig', $items);

    }



}