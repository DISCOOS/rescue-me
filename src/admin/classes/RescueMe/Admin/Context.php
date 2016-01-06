<?php
/**
 * File containing: Admin application context definition class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 29. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin;

use RescueMe\Admin\Security\Accessible;
use RescueMe\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin application context class
 * @package RescueMe\Admin
 * @method static string getAdminPath() Get admin application root path
 * @method static string getAdminUri() Get admin application root uri
 * @method static Application getApp() Get admin application instance
 * @method static Request getRequest() Get Request instance
 * @method static string getRouteName() Get route name
 * @method static string getRouteAccess() Get route access mode
 * @method static string getRoutePattern() Get route pattern
 * @method static boolean|integer getId() Get request id {\d+}
 * @method static boolean|User getUser() Get authenticated user instance
 * @method static boolean|mixed getObject() Get object resolved from request
 * @method static Accessible getAccessible() Get accessible instance from request
 */
class Context extends \RescueMe\Context {

    /**
     * Path to admin application root path
     */
    const ADMIN_PATH = 'admin_path';

    /**
     * Path to admin application root uri
     */
    const ADMIN_URI = 'admin_uri';

    /**
     * Route name
     */
    const ROUTE_NAME = 'route_name';

    /**
     * Route access mode
     */
    const ROUTE_ACCESS = 'route_access';

    /**
     * Route pattern
     */
    const ROUTE_PATTERN = 'route_pattern';

    /**
     * Authenticated user
     */
    const USER = 'user';

    /**
     * Request id
     */
    const ID = 'id';

    /**
     * Accessible instance
     */
    const ACCESSIBLE = 'accessible';

    /**
     * Object resolved from request
     */
    const OBJECT = 'object';
}