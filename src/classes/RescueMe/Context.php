<?php
/**
 * File containing: Application context definition class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. February 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe;

/**
 * Application Context definition class
 * @package RescueMe
 * @method static string getAppPath() Get application root path
 * @method static string getDataPath() Get application data path
 * @method static string getVendorPath() Get path to libraries which application depends
 * @method static string getLocalePath() Get application locale path
 * @method static string getUri() Get application root uri
 * @method static string getTitle() Get application title
 * @method static string getVersion() Get application version
 * @method static string getDbHost() Get database host
 * @method static string getDbName() Get database name
 * @method static string getDbPassword() Get database password
 * @method static string getDbUsername() Get database username
 * @method static string getSecuritySalt() Salt used to encrypt sensitive data
 * @method static string getSecurityPasswordLength() Required Password length
 */
class Context extends AbstractContext {

    /**
     * Path to application root
     */
    const APP_PATH = 'app_path';

    /**
     * Path to application data
     */
    const DATA_PATH = 'data_path';

    /**
     * Path to libraries managed by composer
     */
    const VENDOR_PATH = 'vendor_path';

    /**
     * Path to application locale path
     */
    const LOCALE_PATH = 'locale_path';

    /**
     * Uri to application
     */
    const URI = 'uri';

    /**
     * Application title
     */
    const TITLE = 'title';

    /**
     * Application version
     */
    const VERSION = 'version';

    /**
     * Uri to application
     */
    const DB_HOST = 'db_host';

    /**
     * Uri to application
     */
    const DB_NAME = 'db_name';

    /**
     * Uri to application
     */
    const DB_USERNAME = 'db_username';

    /**
     * Uri to application
     */
    const DB_PASSWORD = 'db_password';

    /**
     * Security salt used to encrypt sensitive data
     */
    const SECURITY_SALT = 'security_salt';

    /**
     * Required Password length
     */
    const SECURITY_PASSWORD_LENGTH = 'security_password_length';


}