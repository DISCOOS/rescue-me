<?php

    /**
     * File containing: AbstractProvider interface
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOOS Foundation} 
     *
     * @since 15. June 2013, v. 1.00
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\SMS;

    /**
     * AbstractProvider class
     * 
     * @package 
     */
    abstract class AbstractProvider implements Provider 
    {
        /**
         * Constructor
         * 
         * @param string JSON encoded crendentials
         *
         * @since 15. June 2013, v. 7.60
         *
         */
        public final function __construct($credentials)
        {
            $this->init(json_decode($credentials)); 
        }// __construct
        
        
        /**
         * Initialize provider
         * 
         * @param array $credentials
         */
        abstract protected function init($credentials);


    }// AbstractProvider
