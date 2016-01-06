<?php
/**
 * File containing: Editor service provider class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 6. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;

use RescueMe\Admin\Service\MenuService;
use Silex\Application;
use Silex\ServiceProviderInterface;


/**
 * Editor service provider class
 *
 * @package RescueMe\Admin\Controller
 */
class EditorServiceProvider implements ServiceProviderInterface {

    /**
     * Provider name.
     */
    const NAME = 'editor_provider';

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app) {
        // Create shared menu service
        $app[self::NAME] = $app->share(function ($app) {
                return new MenuService($app[TemplateServiceProvider::NAME], 'editor.twig');
            });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app) {
        // TODO: Prepare view for rendering
    }

    /**
     * Get editor service instance
     * @param Application $app Silex application instance
     * @return \RescueMe\Admin\Service\MenuService
     */
    public static function get($app) {
        return $app[self::NAME];
    }

}