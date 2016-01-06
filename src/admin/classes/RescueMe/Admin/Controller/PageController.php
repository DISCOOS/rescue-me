<?php
/**
 * File containing: Page request controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 5. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Controller;

use RescueMe\Admin\Provider\AbstractControllerProvider;
use RescueMe\Admin\Provider\PageServiceProvider;
use RescueMe\Admin\Security\Accessible;
use RescueMe\Admin\Service\PageService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 * Page request controller class
 *
 * @package RescueMe\Admin\Controller
 */
class PageController extends AbstractController {

    /**
     * Accepted request method
     */
    const ACCEPT = 'GET';

    /**
     * Constructor
     * @param AbstractControllerProvider $provider RescueMe controller provider instance.
     * @param string $pattern Route path to controller.
     * @param Accessible $object Accessible object.
     * @param boolean|array|callable $context Request context.
     */
    function __construct($provider, $pattern, $object, $context = false)
    {
        parent::__construct($provider, self::ACCEPT, $pattern, false, $object, $context);
    }


    /**
     * GET request handler
     * @param Application $app Silex application.
     * @param Request $request Request object.
     * @return mixed
     */
    protected function handle(Application $app, Request $request)
    {
        $context = $app['context'];

        // Get template from route path without id variable
        $template = str_replace('/', '.', rtrim($context['route']['name'],'/id')) . '.twig';

        // Check for alerts
        $contents = $request->getContent();
        if(is_array($contents) && $alert = isset_get($contents, 'alert', false)) {
            $context[PageService::ALERTS] = (array)$alert;
        }

        return PageServiceProvider::get($app)->page($app, $request, $template, $context);

    }


}