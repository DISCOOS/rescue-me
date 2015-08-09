<?php
/**
 * File containing: Row service provider class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 6. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;

use RescueMe\Admin\Service\RowService;
use Silex\Application;
use Silex\ServiceProviderInterface;


/**
 * Row service provider class
 *
 * @package RescueMe\Admin\Controller
 */
class RowServiceProvider implements ServiceProviderInterface {

    /**
     * Provider name.
     */
    const NAME = 'row_provider';

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app) {
        // New service is created every time
        $app[self::NAME] = function ($app) {
                return new RowService($app[TemplateServiceProvider::NAME]);
            };
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
     * Create new row service instance
     * @param Application $app Silex application instance
     * @return \RescueMe\Admin\Service\RowService
     */
    public static function newInstance($app) {
        return $app[self::NAME];
    }

}