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
    
    use \Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;
    
    /**
     * Roles class
     * 
     * @package 
     */
    class Roles
    {
        private static $roles = array(1=>'Administrator', 2=>'Operator', 3=>'Personnel');
        
        
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
            if (is_int($role))
                $role = self::$roles[$role];
            
            if (!in_array($role, self::$roles)) {
                return false;
            }
            
            $res = false;
            
            if(self::has($role, $user_id) === false)
            {
                $res = DB::delete('roles', "user_id = ".(int)$user_id);
                $res = DB::insert("roles", array('role_id'=>array_search($role, self::$roles), 'user_id' => (int)$user_id));

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
            
            $res = DB::count('roles', $filter);
            
            return $res !== FALSE;
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
            
            $res = DB::select('permissions', array('perm', 'value'), $filter, 'perm');
            
            $perms = array();
            while ($row = $res->fetch_assoc()) {
                $perms[$row['perm'].'.'.$row['value']] = true;
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
            
            $res = DB::delete("permissions", "role_id = ".(int)$role_id);
            
            foreach ($permissions as $key => $value) {
                $perm = explode('.', $key);
                $res = DB::insert("permissions", array('perm' => $perm[0], 'value' => $perm[1], 'role_id' => (int)$role_id));
                
                $res = DB::isEmpty($res) !== false;
                
                if ($res === false) 
                    break;
            }
            return $res;
        }

    }// Roles
