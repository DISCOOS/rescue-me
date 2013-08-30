<?php
/**
 * File containing: Missing class
 * 
 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
 *
 * @since 13. June 2013
 * 
 * @author Sven-Ove Bjerkan <post@sven-ove.no>
 */

namespace RescueMe;

use \gPoint;

/**
 * Missing class
 * 
 * @package RescueMe
 */
class Missing
{
    const TABLE = "missing";

    private static $fields = array
    (
        "missing_name", 
        "missing_mobile_country", 
        "missing_mobile", 
        "missing_reported",
        "op_id"
    );

    private static $update = array
    (
        "missing_name", 
        "missing_mobile_country", 
        "missing_mobile"
    );
    
    public $id = -1;
    public $op_id;
    public $user_id;
    
    public $reported;
    
    public $name;
    public $mobile;
    public $mobile_country;
    public $alert_mobile;
    
    public $last_UTM;
    public $last_pos;
    public $last_acc;
    
    public $sms2_sent;
    public $sms_mb_sent;
    public $sms_delivery;
    public $sms_provider;
    public $sms_provider_ref;

    public $positions = array();
    
    /**
     * Get Missing instance
     * 
     * @param integer $id Missing id
     * @param integer $phone Missing phone number (if more than one)
     * @return \RescueMe\Missing|boolean. Instance of \RescueMe\Missing is success, FALSE otherwise.
     */
    public static function getMissing($id, $phone = -1){
        
        $query = "SELECT * FROM `missing` WHERE `missing_id`=" . (int) $id;
        if($phone !== -1) $query .= " AND `missing_mobile`=" . (int) $phone;
        
        $result = DB::query($query);
        
        if(DB::isEmpty($result)) {
            return false;
        }

        $row = $result->fetch_assoc();
                
        $missing = new Missing();
        return $missing->setMissing($id, $row);

    }// getMissing
    
    
    /**
     * Set missing data from mysqli_result.
     * 
     * @param integer $id Missing id.
     * @param \mysqli_result $result Recordset.
     * 
     * @return \RescueMe\Missing
     */
    private function setMissing($id, $values) {
        
        $this->id = $id;
        
        foreach($values as $key => $val){
            $property = str_replace('missing_', '', $key);
            $this->$property = $val;
        }
        
        $operation = Operation::getOperation($this->op_id);
        $this->user_id = $operation->user_id;
        $this->alert_mobile = $operation->getAlertMobile();
        
        return $this;
    }
    

    public static function addMissing($m_name, $m_mobile_country, $m_mobile, $op_id){

        if(empty($m_name) || empty($m_mobile_country) || empty($m_mobile) || empty($op_id))
            return false;

        $values = array((string) $m_name,  (string) $m_mobile_country, (int) $m_mobile, "NOW()", (int) $op_id);            
        $values = prepare_values(self::$fields, $values);

        $id = DB::insert(self::TABLE, $values);

        if(!$id) {
            return false;
        }

        // Reuse values (optimization)
        $missing = new Missing();
        $missing->setMissing($id, array_exclude($values,'missing_reported'));
        
        return $missing->sendSMS() ? $missing : false;
        
    }// addMissing


    public function updateMissing($m_name, $m_mobile_country, $m_mobile){

        if(empty($m_name) || empty($m_mobile_country) || empty($m_mobile))
            return false;

        $values = prepare_values(Missing::$update, array($m_name, $m_mobile_country, $m_mobile));
                
        $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");
        if(!$res) {
            trigger_error("Failed execute [updateMissing]: " . DB::error(), E_USER_WARNING);
        }// if
        
        return true;
        
    }// updateMissing
    
    
    public function getPositions(){
        if($this->id === -1)
            return false;

        $query = "SELECT `pos_id`, `acc`, `timestamp` FROM `positions` WHERE `missing_id` = " . (int) $this->id;
        $res = DB::query($query);

        if(!$res) return false;

        $this->positions = array();
        while($row = $res->fetch_assoc()){
            $this->positions[(int) $row['timestamp']] = new Position($row['pos_id']);
        }
        krsort($this->positions);

        if(!is_array($this->positions) || count($this->positions) == 0) {
            $this->last_pos = new Position();
            $this->last_acc = -1;
            $this->last_UTM = _('Aldri posisjoner');
        }
        else {
            $this->last_pos = $this->positions[key($this->positions)];
            $gPoint = new gPoint();
            $gPoint->setLongLat($this->last_pos->lon, $this->last_pos->lat);
            $gPoint->convertLLtoTM();
            $this->last_UTM = strip_tags($gPoint->getNiceUTM());
        }
        
        return $this->positions;
    }// getPositions


    public function addPosition($lat, $lon, $acc, $alt, $useragent = ''){

        // Sanity check
        if($this->id === -1) return false;

        $gPoint = new gPoint;
        $gPoint->setLongLat($lon, $lat);
        $gPoint->convertLLtoTM();
        $this->last_UTM = strip_tags($gPoint->getNiceUTM());
        $this->last_acc = $acc;

        // Send SMS 2?
        if((int) $acc > 500 && sizeof($this->positions) > 1){
            // Update this object
            $this->getMissing($this->id);

            // Is SMS2 already sent
            if($this->sms2_sent == 'false'){
                
                $this->_sendSMS($this->mobile_country, $this->mobile, SMS2_TEXT);
                
                $query = "UPDATE `missing` SET `sms2_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";
                $res = DB::query($query);
                if(!$res){
                    trigger_error("Failed execute [$query]: " . DB::error(), E_USER_WARNING);
                }// if
            }
        }

        // Alert person of concern if an accurate position is logged
        else if($this->sms_mb_sent == 'false') {
                
            if(!$this->_sendSMS(
                $this->alert_mobile['country'], 
                $this->alert_mobile['mobile'], 
                SMS_MB_TEXT)) {
                return false;
            }

            $query = "UPDATE `missing` SET `sms_mb_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";
            $res = DB::query($query);
            if(!$res) {
                trigger_error("Failed execute [$query]: " . DB::error(), E_USER_WARNING);
            }// if
            
        }

        // Insert new position
        $query = "INSERT INTO `positions` (`missing_id`, `lat`, `lon`, `acc`, `alt`, `user_agent`) VALUES (" . 
            (int) $this->id . ", " . DB::escape($lat) . ", " . DB::escape($lon) . ", " .
            (int) $acc . ", " . (int) $alt . ", '" . DB::escape($useragent) . "')";

        $posID = DB::query($query);

        if(!$posID) return false;

        $this->positions[(int) time()] = new Position($posID);

    }// addPosition


    public function sendSMS(){
        
        $res = $this->_sendSMS($this->mobile_country, $this->mobile, SMS_TEXT);
        
        if(!$res) {
            
           $res = $this->_sendSMS(
               $this->alert_mobile['country'], 
               $this->alert_mobile['mobile'], 
               SMS_NOT_SENT);
        }

        else {
            
            $module = Module::get("RescueMe\SMS\Provider", $this->user_id);
            
            $query = "UPDATE `missing` 
                        SET `sms_sent` = NOW(), `sms_delivery` = NULL, 
                            `sms_provider` = '".DB::escape($module->impl)."',
                            `sms_provider_ref` = '".$res."'
                        WHERE `missing_id` = '" . $this->id . "';";
            
            if(!DB::query($query)) {
                trigger_error("Failed execute [$query]: ".DB::error(), E_USER_WARNING);
            }
        }

        return $res;

    }// sendSMS
    
    
    /**
     * Anonymize missing data
     * 
     * @return boolean
     */
    public function anonymize() {        
        
        $values = prepare_values(Missing::$update, array('', '', ''));
                
        $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");
        if(!$res) {
            trigger_error("Failed execute [anonymize]: " . DB::error(), E_USER_WARNING);
        }// if

        return true;
    }
    
    
    private function getDialCode($country) {
        
        $code = Locale::getDialCode($country);
        
        if(!$code)
        {
            trigger_error("Failed to get country dial code [$country]", E_USER_WARNING);
            return false;
        }
        return $code;
    }


    /**
     * Send SMS
     * 
     * @param string $country International phone number to sender
     * @param string $to Local phone number to recipient (without country dial code)
     * @param string $message Message string
     * 
     * @return mixed|array Message id if success, errors otherwise (array).
     */
    private function _sendSMS($country, $to, $message) {
        
        $sms = Module::get("RescueMe\SMS\Provider", $this->user_id)->newInstance();
        if(!$sms)
        {
            trigger_error("Failed to get SMS provider", E_USER_WARNING);
            return false;
        }
        
        // facebook-copy fix (includes 3 invisible chars..)
        if(strlen($to) == 11 && (int) $to == 0) {
            $to = substr($to, 3);
        }
        
        $message = str_replace
        (
            array('#missing_id', '#to', '#m_name', '#acc', '#UTM'), 
            array($this->id, $to, $this->name, $this->last_acc, $this->last_UTM),
            $message
        );
        
        $res = $sms->send(SMS_FROM, $country, $to, $message);
        if(!$res) {
            trigger_error($sms->error(), E_USER_WARNING);
        }
        return $res;

    }// _sendSMS

    public function getError() {
        return DB::error();
    }

}// Missing