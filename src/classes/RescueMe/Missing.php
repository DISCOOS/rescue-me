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
        "missing_mobile", 
        "missing_reported",
        "op_id"
    );

    public $id = -1;
    public $positions = array();
    public $m_name;
    public $m_mobile;
    public $alert_mobile;
    private $last_UTM;
    private $last_acc;
    private $sms2_sent;
    private $sms_mb_sent;
    private $op_id;

    /**
     * Get Missing instance
     * 
     * @param integer $id Missing id
     * @param integer $phone Missing phone number (if more than one)
     * @return mixed. Instance of \RescueMe\Missing is success, FALSE otherwise.
     */
    public static function getMissing($id, $phone = -1){
        $missing = new Missing();
        $missing->id = $id;

        $query = "SELECT * FROM `missing` WHERE `missing_id`=" . (int) $missing->id;
        if($phone !== -1) $query .= " AND `missing_mobile`=" . (int) $phone;
        $res = DB::query($query);

        if(DB::isEmpty($res)) 
            return false;

        $row = $res->fetch_assoc();
        foreach($row as $key => $val){
            $property = str_replace('missing_', 'm_', $key);
            $missing->$property = $val;
        }
        
        $operation = new Operation();
        $operation = $operation->getOperation($missing->op_id);
        $missing->alert_mobile = $operation->getAlertMobile();

        return $missing;

    }// getMissing


    public function addMissing($m_name, $m_mobile, $op_id){

        if(empty($m_name) || empty($m_mobile) || empty($op_id))
            return false;

        $values = array((string) $m_name,  (int) $m_mobile, "NOW()", (int) $op_id);            
        $values = prepare_values(self::$fields, $values);

        $this->id = DB::insert(self::TABLE, $values);

        if(!$this->id) 
            return false;

        $missing = self::getMissing($this->id);            
        return $missing->sendSMS();

    }// addMissing


    public static function getAllMissing($status='open') {

        // Get WHERE clause
        switch( $status ) {
            case 'open': 		
                $where = "!= 'Closed'";		
                break;
            case 'closed':		
                $where = "= 'Closed'";		
                break;
            default:
                $where = ' NOT NULL';
        }

        $query = "SELECT `missing_id`, `missing_name` FROM `missing` WHERE `status`
                       {$where} ORDER BY `missing_reported` DESC";
        $res = DB::query($query);

        if (DB::isEmpty($res)) 
            return false;

        $missing_ids = array();
        while ($row = $res->fetch_assoc()) {
            $missing = self::getMissing($row['missing_id']);
            $missing_ids[$row['missing_id']] = $missing;
        }
        return $missing_ids;
    }


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
        }
        else {
            $this->last_pos = $this->positions[key($this->positions)];
        }

        return $this->positions;
    }// getPositions


    public function addPosition($lat, $lon, $acc, $alt, $timestamp, $useragent = ''){

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
                $this->_sendSMS($this->m_mobile, SMS2_TEXT);
                $query = "UPDATE `missing` SET `sms2_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";
                $res = DB::query($query);
                if(!$res){
                    trigger_error("Failed execute [$query]: " . DB::error(), E_USER_WARNING);
                }// if
            }
        }

        // Alert person of concern if an accurate position is logged
        else {
            if($this->sms_mb_sent == 'false') {
                $this->_sendSMS($this->alert_mobile['country'], 
                                $this->alert_mobile['mobile'], SMS_MB_TEXT);
                $query = "UPDATE `missing` SET `sms_mb_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";
                $res = DB::query($query);
                if(!$res){
                    trigger_error("Failed execute [$query]: " . DB::error(), E_USER_WARNING);
                }// if
            }
        }

        // Insert new position
        $query = "INSERT INTO `positions` (`missing_id`, `lat`, `lon`, `acc`, `alt`, `timestamp`, `user_agent`) VALUES (" . 
            (int) $this->id . ", " . DB::escape($lat) . ", " . DB::escape($lon) . ", " .
            (int) $acc . ", " . (int) $alt . ", " . (int) $timestamp . ", '" . DB::escape($useragent) . "')";

        $posID = DB::query($query);

        if(!$posID) return false;

        $this->positions[(int) $timestamp] = new Position($posID);

    }// addPosition


    public function sendSMS(){
        $res = $this->_sendSMS($this->m_mobile_country, $this->m_mobile, SMS_TEXT);
        if(!$res) {
           $res = $this->_sendSMS($this->alert_mobile['country'], 
                        $this->alert_mobile['mobile'], SMS_NOT_SENT);
        }

        else {
            $query = "UPDATE `missing` SET `sms_sent` = '".date("Y-m-d H:i:s")."',
                      `sms_provider_ref` = '".$res."'
                      WHERE `missing_id` = '" . $this->id . "';";
            if(!DB::query($query)) {
                trigger_error("Failed execute [$query]: ".DB::error(), E_USER_WARNING);
            }
        }

        return $res;

    }// sendSMS


    private function _sendSMS($country, $to, $message) {
       ## Facebook-copy fix (includes 3 invisible chars..)
        if(strlen($to) == 11 && (int) $to == 0)
            $to = substr($to, 3);

        // Create message
        $message = str_replace
        (
            array('#missing_id', '#to', '#m_name', '#acc', '#UTM'), 
            array($this->id, $to, $this->m_name, $this->last_acc, $this->last_UTM),
            $message
        );

        $module = Module::get("RescueMe\SMS\Provider");
        $sms = $module->newInstance();

        if(!$sms)
        {
            insert_error("Failed!");
            return false;
        }

        return $sms->send($country, $to, SMS_FROM, $message);

    }// _sendSMS

    public function getError() {
            return DB::error();
    }

}// Missing