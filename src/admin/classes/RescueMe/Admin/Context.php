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

/**
 * Admin application context class
 * @package RescueMe\Admin
 * @method static string getAdminPath() Get admin application root path
 * @method static string getAdminUri() Get admin application root uri
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

}