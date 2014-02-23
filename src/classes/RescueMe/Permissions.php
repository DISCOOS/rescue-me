<?php

    /**
     * File containing: Permissions class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOS Open Source Association} 
     *
     * @since 23. February 2014
     * 
     * @author Sven-Ove Bjerkan
     */
    
    namespace RescueMe;
    
    /**
     * Permissions class
     * 
     * @package 
     */
    class Permissions
    {
        private static $permissions = array(
                                      'operations'=>array('read', 'write'), 
                                      'logs'=>array('read'), 
                                      'users'=>array('read', 'write'),
                                      'settings'=>array('read', 'write'),
                                      'roles'=>array('read', 'write'));
        
       /**
         * Get all permissions
         * 
         * @return array
         */
        public static function getAll() {
             
            return self::$permissions ;
            
        }// getAll 
        
    }// Permissions
