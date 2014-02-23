<?php

    /**
     * File containing: Roles class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOS Open Source Association} 
     *
     * @since 20. February 2014
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    /**
     * Permissions class
     * 
     * @package 
     */
    class Roles
    {
        /**
         * Grant role to user
         * 
         * @param integer $user_id User id
         * 
         * @return boolean
         */
        public static function grant($role, $user_id) {
            
            $res = false;
            
            if(self::has($role, $user_id) === false)
            {
                $res = DB::insert("roles", "role", "user_id = ".(int)$user_id);

                $res = DB::isEmpty($res) !== false;
                
            }
            
            return $res;            
        }        
        
        /**
         * Check if given user is administrator
         * 
         * @param integer $user_id User id
         * 
         * @return boolean
         */
        public static function has($role, $user_id) {
            
            $filter = '`user_id` = '.(int)$user_id." AND `role_name` ='$role'";
            
            $res = DB::count('roles', $filter);
            
            return $res !== FALSE;
        }        
        
        
        /**
         * Revoke role from user
         * 
         * @param integer $user_id
         * 
         * @return boolean
         */
        public static function revoke($role, $user_id) {
            
            $res = false;
            
            if(self::has($role, $user_id) === false)
            {
                $res = DB::delete("roles", "role = '$role' AND user_id = ".(int)$user_id);
            }
            
            return $res;
            
        }

    }// Roles
