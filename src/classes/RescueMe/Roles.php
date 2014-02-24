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
     * Roles class
     * 
     * @package 
     */
    class Roles
    {
        const TABLE = 'roles';
        
        private static $fields = array(
            'user_id',
            'role_id',
            'role_name',
        );
        
        private static $roles = array(
            1=>'Administrator', 
            2=>'Operator', 
            3=>'Personnel'
        );
        
        
        /**
         * Insert standard permissions for given role or user
         * 
         * @return integer Number of inserted permissions
         */
        public static function prepare($role_id, $user_id) {
            
            $count = 0;
            
            switch($role_id) {
                
                case 1:
                    // Grant administrator default permissions
                    if(Permissions::grant(1, $user_id, 'read', 'logs')) $count++;
                    if(Permissions::grant(1, $user_id, 'read', 'users')) $count++;
                    if(Permissions::grant(1, $user_id, 'write', 'users')) $count++;
                    if(Permissions::grant(1, $user_id, 'read', 'roles')) $count++;
                    if(Permissions::grant(1, $user_id, 'write', 'roles')) $count++;
                    if(Permissions::grant(1, $user_id, 'read', 'settings')) $count++;
                    if(Permissions::grant(1, $user_id, 'write', 'settings')) $count++;
                    if(Permissions::grant(1, $user_id, 'read', 'operations')) $count++;
                    if(Permissions::grant(1, $user_id, 'write', 'operations')) $count++;
                    break;                    
                case 2:
                    // Grant operator default permissions
                    if(Permissions::grant(2, $user_id, 'read', 'settings')) $count++;
                    if(Permissions::grant(2, $user_id, 'write', 'settings')) $count++;
                    if(Permissions::grant(2, $user_id, 'read', 'operations')) $count++;
                    if(Permissions::grant(2, $user_id, 'write', 'operations')) $count++;
                    break;                    
                case 3:
                    // Grant personell default permissions
                    if(Permissions::grant(3, $user_id, 'read', 'operations')) $count++;
                    break;                    
            }
            
            return $count;
            
       }
        
        
       /**
         * Get all roles
         * 
         * @return array
         */
        public static function getAll() {
             
            return self::$roles ;
            
        }// getAll     

        
        /**
         * Grant role to user
         * 
         * @param string $role Role
         * @param integer $user_id User id
         * 
         * @return boolean
         */
        public static function grant($role, $user_id) {
            if (is_int($role)) {
                $role = self::$roles[$role];
            }
            
            if (!in_array($role, self::$roles)) {
                return false;
            }
            
            $res = true;
            
            if(self::has($role, $user_id) === false)
            {
                $res = DB::delete(self::TABLE, 'user_id = '.(int)$user_id);
                
                $role_id = array_search($role, self::$roles);
                
                $values = prepare_values(Roles::$fields,array((int)$user_id, $role_id, $role));
                
                $res = DB::insert(self::TABLE, $values);
                
                $res = DB::isEmpty($res) !== false;
                
            }
            
            return $res;
        }        
        
        /**
         * Check if given user has a given role
         * 
         * @param string $role Role
         * @param integer $user_id User id
         * 
         * @return boolean
         */
        public static function has($role, $user_id) {
            
            $filter = '`user_id` = '.(int)$user_id." AND `role_name` ='$role'";
            
            $res = DB::count(self::TABLE, $filter);
            
            return $res !== FALSE && $res > 0;
        }        
        
        
        /**
         * Revoke role from user
         * 
         * @param string role Role
         * @param integer $user_id User ID
         * 
         * @return boolean
         */
        public static function revoke($role, $user_id) {
            
            if (!in_array($role, self::$roles)) {
                return false;
            }
            
            $res = false;
            
            if(self::has($role, $user_id) === false)
            {
                $res = DB::delete("roles", "role = '$role' AND user_id = ".(int)$user_id);
            }
            
            return $res;
            
        }
        
        /**
         * Get permissions for a role
         * 
         * @param string $role Role
         * @return boolean|array
         * 
         */
        public static function getPermissionsForRole($role_id) {
            $filter = '`role_id` = '.(int)$role_id."";
            
            $res = DB::select('permissions', array('resource', 'access'), $filter, 'resource');
            
            $perms = array();
            while ($row = $res->fetch_assoc()) {
                $perms[$row['resource'].'.'.$row['access']] = true;
            }
            return $perms;
        }
        
        /**
         * Get permissions for a role
         * 
         * @param string $role Role
         * @return boolean|array
         * 
         */
        public static function getPermissionsForUser($user_id) {
            $filter = '`user_id` = '.(int)$user_id."";
            
            $res = DB::select('permissions', array('resource', 'access'), $filter, 'resource');
            
            $perms = array();
            while ($row = $res->fetch_assoc()) {
                $perms[$row['resource'].'.'.$row['access']] = true;
            }
            return $perms;
        }
        
        
        /**
         * Update permissions for a role
         * 
         * @param int $role_id Role ID
         * @param array $permissions Permissions the role should have
         * @return boolean
         */
        public static function update($role_id, $permissions) {
            
            $filter = '`role_id` = '.(int)$role_id."";
            
            $res = DB::delete("permissions", $filter);
            
            foreach (array_keys($permissions) as $key) {
                $perm = explode('.', $key);
                $res = DB::insert("permissions", array('resource' => $perm[0], 'access' => $perm[1], 'role_id' => (int)$role_id));
                
                $res = DB::isEmpty($res) !== false;
                
                if ($res === false) 
                    break;
            }
            return $res;
        }

    }// Roles
