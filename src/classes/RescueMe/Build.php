<?php

    /**
     * File containing: Build class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 19. August 2013, v. 8.00
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;

    /**
     * Build class
     * 
     * @package 
     */
    class Build
    {

        /**
         * constructor for Build
         *
         * @since 19. August 2013, v. 8.00
         *
         */
        public static function configure()
        {
            $root = dirname(__FILE__)."/../../..";
            
            $argv = array("rescueme", "configure", "--install-dir=$root/src");
            
            // Bootstrap build scripts
            require_once "$root/build/cli.php";
            
            
        }// configure


    }// Build
