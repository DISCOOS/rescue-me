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
use RescueMe\Admin\Provider\TemplateServiceProvider;
use RescueMe\Admin\Security\Accessible;
use RescueMe\User;
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
     * @param boolean|mixed $id Request id.
     * @param boolean|User $user Authenticated user.
     * @param boolean|object $object Resolved object.
     * @param boolean|array|callable $context Request context.
     * @return mixed
     */
    protected function handle(Application $app, Request $request, $id, $user, $object, $context)
    {
        $name = $this->provider->getRouteName($this->pattern);

        // Get template from route path without id variable
        $template = str_replace('/', '.', rtrim($name,'/id')) . '.twig';

        // Check for alerts
        $contents = $request->getContent();
        if(is_array($contents) && $alert = isset_get($contents, 'alert', false)) {
            $context['alerts'] = (array)$alert;
        }

        return TemplateServiceProvider::get($app)->page($app, $template, $id, $object, $user, $context);

    }


}