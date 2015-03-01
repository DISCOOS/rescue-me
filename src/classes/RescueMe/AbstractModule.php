<?php
    /**
     * File containing: Manager class
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 01. March 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;

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
         * Description of last error
         * @var array
         */
        protected $error;


        /**
         * Provider configuration
         *
         * @var \RescueMe\Configuration
         */
        protected $config;


        /**
         * Constructor
         *
         * @param $config Configuration Configuration
         * @param mixed $uses Uses (optional, default - empty array)
         *
         * @since 29. September 2013
         *
         */
        protected function __construct($config, $uses=array())
        {
            parent::__construct($uses);

            $this->config = $config;
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
         * @param \RescueMe\Configuration $config Account [optional, null - use current
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
         * @param \RescueMe\Configuration $config Account [optional, null - use current
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
         * Set last error from exception.
         *
         * @param \Exception $e Exception
         * @param boolean $value Return value
         *
         * @return boolean
         */
        protected final function exception(\Exception $e, $value = false) {
            $this->error['code'] = $e->getCode();
            $this->error['message'] = Logger::toString($e);

            Logs::write(
                Logs::SYSTEM,
                LogLevel::ERROR,
                $e->getMessage(),
                $this->error
            );

            return $value;
        }


        /**
         * Set fatal error
         * @param string $message
         *
         * @return boolean
         */
        protected final function fatal($message) {
            $this->error['code'] = Module::FATAL;
            $this->error['message'] = $message;
            Logs::write(
                Logs::SYSTEM,
                LogLevel::CRITICAL,
                $message,
                $this->error
            );

            return false;
        }


        /**
         * Set critical error
         *
         * @param string $message
         * @param array $context
         *
         * @return boolean
         */
        protected final function critical($message, $context = array()) {
            Logs::write(
                Logs::SYSTEM,
                LogLevel::CRITICAL,
                $message,
                $context
            );

            return false;
        }


        /**
         * Returns the error code for the most recent function call.
         *
         * @return integer An error code value for the last call, if it failed. zero means no error occurred.
         */
        public final function errno()
        {
            return isset($this->error) ? $this->error['code'] : 0;
        }


        /**
         * Returns a string description of the last error.
         *
         * @return string A string that describes the error. An empty string if no error occurred.
         */
        public final function error()
        {
            return isset($this->error) ? $this->error['message'] : '';
        }
    }