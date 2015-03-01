<?php

    /**
     * File containing: AbstractLookup lookup implementation
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. February 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Device;

    use Psr\Log\LogLevel;
    use RescueMe\Configuration;
    use RescueMe\Log\Logger;
    use RescueMe\Log\Logs;

    /**
     * Lookup base implementation
     *
     * @package RescueMe\Device
     */
    abstract class AbstractLookup implements Lookup {

        /**
         * Lookup configuration
         *
         * @var \RescueMe\Configuration
         */
        protected $config;


        /**
         * Description of last error
         * @var array
         */
        protected $error;


        /**
         * Constructor
         *
         * @param $config Configuration Configuration
         *
         * @since 29. September 2013
         *
         */
        protected function __construct($config) {
            $this->config = $config;
        }


        /**
         * Get Lookup configuration
         * @return \RescueMe\Configuration
         */
        public function config() {
            return clone($this->config);
        }


        /**
         * Set last error from exception.
         *
         * @param \Exception $e Exception
         * @param boolean $value Return value
         *
         * @return boolean
         */
        protected function exception(\Exception $e, $value = false) {
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
        protected function fatal($message) {
            $this->error['code'] = Lookup::FATAL;
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
        protected function critical($message, $context = array()) {
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
        public function errno()
        {
            return isset($this->error) ? $this->error['code'] : 0;
        }


        /**
         * Returns a string description of the last error.
         *
         * @return string A string that describes the error. An empty string if no error occurred.
         */
        public function error()
        {
            return isset($this->error) ? $this->error['message'] : '';
        }

    }