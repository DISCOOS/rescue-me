<?php
    /**
     * File containing: Database class
     *
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 13. June 2013
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;

    use Exception;

    /**
     * Error class
     *
     * @package RescueMe
     */
    class DBException extends \Exception {

        const FATAL = 255;

        public function __construct($message = "", $code = 0, Exception $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }

        /**
         * Create fatal error
         * @param string $message
         * @param Exception $previous
         * @return DBException
         */
        public static function asFatal($message = "", Exception $previous = null) {
            return new static($message, DBException::FATAL, $previous);
        }

    }