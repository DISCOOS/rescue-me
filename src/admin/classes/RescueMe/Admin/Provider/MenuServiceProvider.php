<?php
/**
 * File containing: Menu service provider class
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
 * Menu service provider class
 *
 * @package RescueMe\Admin\Controller
 */
class MenuServiceProvider implements ServiceProviderInterface {

    /**
     * Provider name.
     */
    const NAME = 'menu_provider';

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app) {

        // Create shared menu service
        $app[self::NAME] = $app->share(function ($app) {
                return new MenuService($app[TemplateServiceProvider::NAME]);
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
     * Create get menu service instance
     * @param Application $app Silex application instance
     * @return \RescueMe\Admin\Service\MenuService
     */
    public static function get($app) {
        return $app[self::NAME];
    }

}