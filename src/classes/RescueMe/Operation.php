<?php
/**
 * File containing: Operation class
 * 
 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
 *
 * @since 22. July 2013
 * 
 * @author Sven-Ove Bjerkan <post@sven-ove.no>
 */

namespace RescueMe;

/**
 * Operation class
 * 
 * @package RescueMe
 */
class Operation {
    
    const TABLE = "operations";

    private static $fields = array
    (
        "user_id", 
        "op_name", 
        "alert_mobile_country",
        "alert_mobile",
        "op_ref", 
        "op_opened", 
        "op_comments"
    );

    public $id = -1;
    public $user_id = -1;

    /**
     * Get Operation instance
     * 
     * @param integer $id Operation id
     * @return mixed. Instance of \RescueMe\Operation if success, FALSE otherwise.
     */
    public static function getOperation($id){
        $query = "SELECT * FROM `".self::TABLE."` WHERE `op_id`=" . (int) $id;
        $res = DB::query($query);

        if(DB::isEmpty($res)) 
            return false;

        $operation = new Operation();
        $operation->id = $id;

        $row = $res->fetch_assoc();
        foreach($row as $key => $val){
            $operation->$key = $val;
        }

        return $operation;
    }// getOperation
    

    /**
     * Check i given operation is closed
     * 
     * @param integer $id Operation id
     * @return boolean TRUE if closed (or not found), FALSE otherwise.
     */
    public static function isOperationClosed($id) {
        
        $query = "SELECT op_closed FROM `".self::TABLE."` WHERE `op_id`=" . (int) $id;
        $res = DB::query($query);

        if(DB::isEmpty($res)) 
            return false;

        $row = $res->fetch_row();
        
        return isset($row[0]) && !empty($row[0]);        
    }
    
    
    
    /**
     * Close given operation
     * 
     * @param integer $id Operation id
     * @return boolean
     */
    public static function closeOperation($id, $op_name = false) {
        
        // Close operation
        if(DB::update(self::TABLE,array('op_closed' => 'NOW()'), "`op_id`=" . (int) $id) === FALSE) {
            return false;
        }
        
        // Anonymize operation
        if (!$op_name)
            $op_name = date('Y-m-d');
        Operation::set($id, 'op_name', $op_name);
        
        return true;
        
    }
    
    /**
     * Update a field in the DB
     * @param int $id Operation ID
     * @param string $field DB-field to update
     * @param string $value New valye
     * @return boolean
     */
    public static function set($id, $field, $value) {
        return DB::update(self::TABLE,array($field => $value), "`op_id`=" . (int) $id);
    }
    

    /**
     * Reopen given operation
     * 
     * @param integer $id Operation id
     * @return boolean
     */
    public static function reopenOperation($id) {
        
        return DB::update(self::TABLE,array('op_closed' => 'NULL'), "`op_id`=" . (int) $id);
        
    }
    
    
    /**
     * Add a new operation
     * 
     * @param string $op_name Operation name
     * @param int $user_id User ID of the "owner" (Tip: often $_SESSION['user_id'])
     * @param string $alert_mobile_country Country code (ISO)
     * @param string $alert_mobile Mobilephone to alert of recieced positions, etc
     * @param string $op_ref Reference of the operation, like SAR-number or something
     * @param string $op_comments Any comments to the operation
     * @return boolean
     */
    public function addOperation(
        $op_name, $user_id, $alert_mobile_country, 
        $alert_mobile, $op_ref = '', $op_comments = ''){
        
        if(empty($op_name) || empty($user_id) || 
                empty($alert_mobile_country) || empty($alert_mobile)) {
            return false;            
        }

        $values = array((int) $user_id, (string) $op_name, (string) $alert_mobile_country, 
                (string) $alert_mobile, (string) $op_ref, "NOW()", (string) $op_comments);

        $values = prepare_values(self::$fields, $values);
        $this->id = DB::insert(self::TABLE, $values);

        if(!$this->id) {
            return false;
        }

        $operation = self::getOperation($this->id);

        return $operation;
    }// addOperation
    
    /**
     * Get all operations
     * 
     * @param string $status NULL, 'open' or 'closed'
     * @return mixed. Instance of \RescueMe\Operation if success, FALSE otherwise.
     */
    public static function getAllOperations($status='open') {
        $user = User::current();
        // Get WHERE clause
        switch( $status ) {
            case 'open': 		
                $where = " IS NULL";		
                break;
            case 'closed':		
            default:
                $where = "IS NOT NULL";
                break;
        }

        $query = "SELECT `op_id`, `op_name` FROM `".self::TABLE."`
                  WHERE `op_closed` {$where}
                  AND `".self::TABLE."`.`user_id` = ".(int)$user->id."
                  ORDER BY `op_opened` DESC";
                    
        $res = DB::query($query);
                
        if (DB::isEmpty($res)) 
            return false;
        
        $operation_ids = array();
        while ($row = $res->fetch_assoc()) {
            $operation = new Operation();
            $operation = $operation->getOperation($row['op_id']);
            $operation_ids[$row['op_id']] = $operation;
        }
        return $operation_ids;
    } // getAllOperations
    
    public function getAllMissing() {
        $query = "SELECT `missing_id`, `missing_name` FROM `missing`
                    WHERE `op_id` = ".(int)$this->id." 
                    ORDER BY `missing_name`";
        
       $res = DB::query($query);
                      
        if (DB::isEmpty($res)) 
            return false;

        $missings = array();
        while ($row = $res->fetch_assoc()) {
            $missing = Missing::getMissing($row['missing_id']);
            $missings[$row['missing_id']] = $missing;
        }
        return $missings;
    }
    
    public function getAlertMobile() {
        if (empty($this->alert_mobile))
            return false;
        
        return array('country'=>$this->alert_mobile_country, 
                    'mobile'=>$this->alert_mobile);
    }

    public function getError() {
        return DB::error();
    }
    
}// Operation