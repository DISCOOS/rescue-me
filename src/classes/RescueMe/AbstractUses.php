<?php

    /**
     * File containing: Uses interface
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 29. September 2013
     * 
     * @author Kenneth Gulbrandsøy <discoos.org>
     */
    
    namespace RescueMe;

    /**
     * AbstractUses class
     * 
     * @package 
     */
    abstract class AbstractUses implements Uses {

        /**
         * Array of uses
         * @var array
         */
        private $uses;
        

        /**
         * Constructor
         *
         * @param string $uses 
         *
         * @since 29. September 2013
         * 
         */
        public function __construct($uses=array())
        {
            $this->uses = is_array($uses) ? $uses : array($uses);
        }// __construct        
        
        
        public function uses() {
            return $this->uses;
        }
        
    }
