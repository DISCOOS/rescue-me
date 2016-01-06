<?php
/**
 * File containing: Template service provider class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 29. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;


use RescueMe\Admin\Context;
use RescueMe\Admin\Service\TemplateService;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Template service provider class
 * @package RescueMe\Admin\Provider
 */
class TemplateServiceProvider implements ServiceProviderInterface {

    /**
     * Page provider name.
     */
    const NAME = 'template_provider';

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app) {
        // Service is shared to minimize footprint
        $app[self::NAME] = $app->share(function () {
            return new TemplateService(get_path(Context::getAppPath(), 'gui', true));
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
     * @return \RescueMe\Admin\Service\TemplateService
     */
    public static function get($app) {
        return $app[self::NAME];
    }
}