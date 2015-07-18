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
    
    namespace RescueMe\Domain;
    use RescueMe\DB;

    /**
     * Permissions class
     * 
     * @package 
     */
    class Permissions
    {
        const TABLE = 'permissions';
        
        private static $fields = array(
            'role_id',
            'user_id',
            'access',
            'resource'
        );
        
        private static $permissions = array(
            'logs'=>array('read'), 
            'user'=>array('read', 'write'),
            'user.all'=>array('read', 'write'),
            'setup'=>array('read', 'write'),
            'setup.all'=>array('read', 'write'),
            'roles'=>array('read', 'write'),
            'operations'=>array('read', 'write'), 
            'operations.all'=>array('read', 'write')
        );
        
       /**
         * Get all permissions
         * 
         * @return array
         */
        public static function getAll() {
             
            return self::$permissions ;
            
        }// getAll 
        
        
        /**
         * Check if permission is granted
         * 
         * @param integer $role_id Role id
         * @param integer $user_id User id
         * @param string $access Access operation
         * @param string $resource Resource name
         * 
         * @return boolean
         */
        public static function allow($role_id, $user_id, $access, $resource) {
            $filter = "`role_id`={$role_id} AND `user_id`={$user_id} AND `access`='$access' AND `resource`='$resource'";
            $res = DB::count(self::TABLE, $filter);
            return $res !== false && $res > 0;
        }
        
        
        /**
         * Grant permission to role or user
         * 
         * @param integer $role_id Role
         * @param integer $user_id User id
         * @param string $access Access operation
         * @param string $resource Resource name
         * 
         * @return boolean
         */
        public static function grant($role_id, $user_id, $access, $resource) {
            $res = false;
            if(self::allow($role_id, $user_id, $access, $resource) === false) {
                
                $values = prepare_values(self::$fields, array($role_id, $user_id, $access, $resource));
                $res = DB::insert(self::TABLE, $values);
                $res = $res !== false;
            }
            return $res;            
        }        
        
        
        /**
         * Revoke permission from role or user
         * 
         * @param integer $role_id Role
         * @param integer $user_id User id
         * @param string $access Access operation
         * @param string $resource Resource name
         * 
         * @return boolean
         */
        public static function revoke($role_id, $user_id, $access, $resource) {
            $filter = "(user_id={$user_id} OR `role_id`={$role_id}) AND `access` = $access AND `resource`=$resource";
            return DB::delete(self::TABLE, $filter);
        }        
        
    }// Permissions
