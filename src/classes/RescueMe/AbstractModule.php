<?php
/**
 * File containing: AbstractModule class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 01. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe;

use Exception;
use Psr\Log\LogLevel;
use RescueMe\Log\Logger;
use RescueMe\Log\Logs;


/**
 * Module base implementation
 *
 * @package RescueMe
 */
abstract class AbstractModule extends AbstractUses implements Module {

    /**
     * Error code unknown
     */
    const UNKNOWN = 99;


    /**
     * Description of last error
     * @var array
     */
    protected $error;


    /**
     * Provider configuration
     *
     * @var Configuration
     */
    protected $config;


    /**
     * Log name
     * @var string
     */
    protected $logger;


    /**
     * Constructor
     *
     * @param $config Configuration Configuration
     * @param mixed $uses Uses (optional, default - empty array)
     *
     * @param string $logger Log name
     * @since 29. September 2013
     */
    protected function __construct($config, $uses=array(), $logger=Logs::SYSTEM)
    {
        parent::__construct($uses);

        $this->config = $config;
        $this->logger = $logger;

    }

    /**
     * Supported by default. Override this method as needed.
     * @return boolean
     */
    public function isSupported()
    {
        return true;
    }


    /**
     * Initialize module
     *
     * Returns TRUE if initialization succeeded, FALSE otherwise.
     *
     * Check Module::errno() and Module::error() for more information if initialization does not succeed.
     *
     * @return Configuration
     */
    public final function getConfig()
    {
        return clone($this->config);
    }


    /**
     * Initialize module
     *
     * Default implementation does nothing and returns true. Override this method if module needs initialization
     *
     * @return boolean
     */
    public function init()
    {
        return true;
    }


    /**
     * Validate configuration
     *
     * @param Configuration $config Account [optional, null - use current
     * @return boolean TRUE if success, FALSE otherwise.
     */
    public final function validate($config = null) {
        if(isset($config) === FALSE){
            $config = $this->getConfig();
        }

        $params = $this->validateRequired($config);

        return $this->validateParameters($params);
    }

    /**
     * Validate that required parameters exists
     * @param Configuration $config Account [optional, null - use current]
     * @return boolean|array TRUE if success, parameters otherwise.
     */
    protected function validateRequired($config) {
        foreach($config->params() as $property => $default) {
            if($config->required($property) && empty($default)) {
                return false;
            }
        }
        return $config->params();
    }

    /**
     * Validate configuration parameters.
     *
     * Default implementation returns true for all $parameters. Override this method if validation is needed.
     *
     * @param array $params Associative array of parameters
     * @return boolean TRUE if success, FALSE otherwise.
     */
    protected function validateParameters($params) {
        return true;
    }

    /**
     * Set level from exception.
     *
     * @param Exception $e Exception
     * @param boolean $value Return value
     *
     * @return boolean
     * @throws DBException
     */
    protected final function exception(Exception $e, $value = false) {
        $message = Logger::toString($e);
        $this->set_last(
            $e->getCode(),
            $message
        );
        return $this->log(
            LogLevel::ERROR,
            $message,
            array(),
            $value
        );
    }


    /**
     * Log fatal error level
     * @param string $message
     *
     * @param array $context
     * @return boolean
     * @throws DBException
     */
    protected final function fatal($message, $context = array()) {
        $this->set_last(
            Module::FATAL,
            $message
        );
        return $this->log(
            LogLevel::CRITICAL,
            $message,
            $context,
            true
        );
    }


    /**
     * Log critical level
     *
     * @param string $message
     * @param array $context
     *
     * @return boolean
     * @throws DBException
     */
    protected final function critical($message, $context = array()) {
        $this->set_last(
            self::UNKNOWN,
            $message
        );
        return $this->log(
            LogLevel::CRITICAL,
            $message,
            $context,
            true
        );
    }

    /**
     * Log error level
     *
     * @param string $message
     * @param array $context
     *
     * @return boolean
     * @throws DBException
     */
    protected final function error($message, $context = array()) {
        $this->set_last(
            self::UNKNOWN,
            $message
        );
        return $this->log(
            LogLevel::ERROR,
            $message,
            $context,
            true
        );
    }

    /**
     * Log warning level
     *
     * @param string $message
     * @param array $context
     *
     * @return boolean
     * @throws DBException
     */
    protected final function warning($message, $context = array()) {
        return $this->log(
            LogLevel::WARNING,
            $message,
            $context,
            true
        );
    }


    /**
     * Log info level
     *
     * @param string $message
     * @param array $context
     *
     * @return boolean
     * @throws DBException
     */
    protected final function info($message, $context = array()) {
        return $this->log(
            LogLevel::INFO,
            $message,
            $context,
            true
        );
    }



    /**
     * Log debug level
     *
     * @param string $message
     * @param array $context
     *
     * @return boolean
     * @throws DBException
     */
    protected final function debug($message, $context = array()) {
        return $this->log(
            LogLevel::DEBUG,
            $message,
            $context,
            true
        );
    }


    /**
     * Log message
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @param mixed $return
     *
     * @return mixed
     * @throws DBException
     */
    protected final function log($level, $message, $context, $return) {
        Logs::write(
            $this->logger,
            $level,
            $message,
            $context
        );
        return $return;
    }


    /**
     * Set last error
     * @param $code int Error code
     * @param $message string Error messate
     */
    protected function set_last($code, $message){
        $this->error['code'] = $code;
        $this->error['message'] = $message;
    }

    /**
     * Returns last error code and message for the most recent function call.
     *
     * @return bool|array Array with error code and message if the last call failed, FALSE otherwise.
     */
    public final function last_error()
    {
        return isset($this->error) ? $this->error : false;
    }


    /**
     * Returns the error code for the most recent function call.
     *
     * @return integer An error code value for the last call, if it failed. zero means no error occurred.
     */
    public final function last_error_code()
    {
        return isset($this->error) ? $this->error['code'] : 0;
    }


    /**
     * Returns a string description of the last error.
     *
     * @return string A string that describes the error. An empty string if no error occurred.
     */
    public final function last_error_message()
    {
        return isset($this->error) ? $this->error['message'] : '';
    }
}