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
         * @param integer $role_id
         * @param integer $user_id
         * @return integer Number of inserted permissions
         */
        public static function prepare($role_id, $user_id) {
            
            $count = 0;
            
            switch($role_id) {
                
                case 1:
                    // Grant administrator default permissions
                    if(Permissions::grant($role_id, $user_id, 'read', 'logs')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'user.all')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'user.all')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'roles')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'roles')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'alert.all')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'alert.all')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'setup.all')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'setup.all')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'operations.all')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'operations.all')) $count++;
                    break;                    
                case 2:
                    // Grant operator default permissions
                    if(Permissions::grant($role_id, $user_id, 'read', 'user')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'user')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'setup')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'setup')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'operations')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'write', 'operations')) $count++;
                    break;                    
                case 3:
                    // Grant personell default permissions
                    if(Permissions::grant($role_id, $user_id, 'read', 'user')) $count++;
                    if(Permissions::grant($role_id, $user_id, 'read', 'operations')) $count++;
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
         * @param integer|string $role Role id or name
         * @param integer $user_id User id
         * 
         * @return boolean
         */
        public static function grant($role, $user_id) {

            if (is_int($role)) {
                $role = self::$roles[$role];
            }
            
            if (in_array($role, self::$roles) === FALSE) {
                return Roles::error("Failed grant user $user_id unknown role $role", self::$roles);
            }
            
            $res = true;
            
            if(self::has($role, $user_id) === FALSE)
            {
                $res = DB::delete(self::TABLE, 'user_id = '.(int)$user_id);
                
                $role_id = array_search($role, self::$roles);
                
                $values = prepare_values(Roles::$fields,array((int)$user_id, $role_id, $role));
                
                // Primary key is not an integer, returns 0 on success, FALSE otherwise
                $res = DB::insert(self::TABLE, $values);
                
                if($res !== 0) {
                    return Roles::error("Failed grant user $user_id role $role", $values);
                }                
                
                Roles::log("Granted user $user_id role $role", $res);
            }
            
            return $res;
        }        
        
        /**
         * Check if given user has a given role
         * 
         * @param integer|string $role Role id or name
         * @param integer $user_id User id
         * 
         * @return boolean
         */
        public static function has($role, $user_id) {

            if (is_int($role)) {
                $role = self::$roles[$role];
            }

            $filter = '`user_id` = '.(int)$user_id." AND `role_name` ='$role'";
            
            $res = DB::count(self::TABLE, $filter);
            
            return $res !== FALSE && $res > 0;
        }        
        
        
        /**
         * Revoke role from user
         * 
         * @param string $role Role
         * @param integer $user_id User ID
         * 
         * @return boolean
         */
        public static function revoke($role, $user_id) {
            
            if (in_array($role, self::$roles) === false) {
                return Roles::error("Failed revoke unknown role $role from user $user_id", self::$roles);
            }
            
            $res = false;
            
            if(self::has($role, $user_id) === false)
            {
                $res = DB::delete("roles", "role = '$role' AND user_id = ".(int)$user_id);
            }
            
            if($res === FALSE) {
                Roles::error("Failed revoke role $role from user $user_id", func_get_args());
            }            
            
            return Roles::log("Role $role revoked from user $user_id", $res);
            
        }

        /**
         * Get permissions for a role
         *
         * @param $role_id
         * @internal param string $role Role
         * @return boolean|array
         */
        public static function getPermissionsForRole($role_id) {
            $filter = '`role_id` = '.(int)$role_id."";
            
            $res = DB::select('permissions', array('resource', 'access'), $filter, 'resource');
            
            $perms = array();
            while ($row = $res->fetch_assoc()) {
                $perms[$row['resource'].':'.$row['access']] = true;
            }
            return $perms;
        }

        /**
         * Get permissions for a role
         *
         * @param $user_id
         * @internal param string $role Role
         * @return boolean|array
         */
        public static function getPermissionsForUser($user_id) {
            $filter = '`user_id` = '.(int)$user_id."";
            
            $res = DB::select('permissions', array('resource', 'access'), $filter, 'resource');
            
            $perms = array();
            while ($row = $res->fetch_assoc()) {
                $perms[$row['resource'].':'.$row['access']] = true;
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
                
                $perm = explode(':', $key);
                
                $res = DB::insert("permissions", array('resource' => $perm[0], 'access' => $perm[1], 'role_id' => (int)$role_id));
                
                if ($res === false) {
                    Roles::error("Failed update role $role_id permissions", func_get_args());
                }
            }
            
            return Roles::log("Role $role_id permissions updated", $res);
        }
        

        private static function log($message, $level = LogLevel::INFO, $result = true)
        {
            $context['code'] = DB::errno();
            $context['error'] = DB::error();
            Logs::write(
                Logs::ACCESS, 
                $level, 
                $message
            );
                
            return $result;
        }        
        

        private static function error($message, $context = array())
        {
            $context['code'] = DB::errno();
            $context['error'] = DB::error();
            Logs::write(
                Logs::SYSTEM, 
                LogLevel::ERROR, 
                $message, 
                $context
            );
                
            return false;
        }        

    }// Roles
