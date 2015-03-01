<?php
    /**
     * File containing: Lookup interface
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. February 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;


    /**
     * Generic module exception
     *
     * @package RescueMe
     */
    class ModuleException extends \Exception {

        /**
         * Constructor
         * @param string $message Message
         * @param int $code Error code
         * @param \Exception $previous Cause
         */
        function __construct($message = "", $code = 0, \Exception $previous = null) {
            parent::__construct($message, $code, $previous);
        }

    }