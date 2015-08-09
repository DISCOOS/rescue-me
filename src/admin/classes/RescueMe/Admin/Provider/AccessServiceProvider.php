<?php
/**
 * File containing: Access service provider class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 9. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;

use RescueMe\Admin\Service\AccessService;
use Silex\Application;
use Silex\ServiceProviderInterface;


/**
 * Access service provider class
 *
 * @package RescueMe\Admin\Controller
 */
class AccessServiceProvider implements ServiceProviderInterface {

    /**
     * Provider name.
     */
    const NAME = 'access_provider';

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app) {
        // Create shared service
        $app[self::NAME] = $app->share(function () use ($app) {
                $service = new AccessService();
                // Register access voter
                $app['security.voters'] = $app->extend('security.voters', function($voters) use ($service) {
                        $voters[] = $service->getVoter();
                        return $voters;
                    });
                return $service;
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
     * Get access service instance
     * @param Application $app Silex application instance
     * @return \RescueMe\Admin\Service\AccessService
     */
    public static function get($app) {
        return $app[self::NAME];
    }

}