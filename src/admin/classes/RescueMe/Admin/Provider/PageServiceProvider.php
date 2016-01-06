<?php
/**
 * File containing: Page service provider class
 *
 * @copyright Copyright 2016 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 2. January 2016
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;


use RescueMe\Admin\Service\PageService;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Page service provider class
 * @package RescueMe\Admin\Provider
 */
class PageServiceProvider implements ServiceProviderInterface {

    /**
     * Page provider name.
     */
    const NAME = 'page_provider';

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app) {
        // Service is shared to minimize footprint
        $app[self::NAME] = $app->share(function () use($app) {
            return new PageService($app[TemplateServiceProvider::NAME]);
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
     * Get shared template service instance
     * @param Application $app Silex application instance
     * @return \RescueMe\Admin\Service\PageService
     */
    public static function get($app) {
        return $app[self::NAME];
    }
}