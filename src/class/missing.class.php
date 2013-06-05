<?php

require_once(APP_PATH_INC.'common.inc.php');
require_once(APP_PATH_CLASS.'position.class.php');

class Missing {

	public $missing_id = -1;
	public $positions = array();
	public $mb_name;
	public $mb_email;
	public $mb_mobile;
	public $m_name;
	public $m_mobile;
	public $timestamp_sms_sent;

	public function getMissing($missing_id, $missing_phone = -1) {
		global $mysqli;
		$this->missing_id = $missing_id;

		$query = "SELECT * /*`missed_by_name`, `missed_by_email`, `missed_by_mobile`, `missing_name`, `missing_mobile`, `timestamp_sms_sent`*/
				FROM `missing`
				WHERE `missing_id`=".(int)$this->missing_id;
		if ($missing_phone !== -1)
			$query .= " AND `missing_mobile`=".(int)$missing_phone;
		$res = mysqli_query($mysqli,$query);

		if (!$res)
			return false;

		$row = $res->fetch_assoc();
		foreach($row as $key => $val) {
			$s_key = str_replace(array('missing','missed_by'), array('m','mb'), $key);
			$this->$s_key = $val;
		}
/*
		$this->mb_name = $row['missed_by_name'];
		$this->mb_mail = $row['missed_by_mail'];
		$this->mb_mobile = $row['missed_by_mobile'];
		$this->m_name = $row['missing_name'];
		$this->m_mobile = $row['missing_mobile'];
		$this->
*/
	}

	public function addMissing($mb_name, $mb_email, $mb_mobile, $m_name, $m_mobile) {
		if(empty($mb_name) || empty($mb_email) || empty($mb_mobile) || empty($m_name) || empty($m_mobile))
			return false;
		global $mysqli;
		$query = "INSERT INTO `missing` (`missed_by_name`, `missed_by_email`, `missed_by_mobile`, `missing_name`, `missing_mobile`, `missing_reported`)
					VALUES ('".mysqli_real_escape_string($mysqli, $mb_name)."', '".mysqli_real_escape_string($mysqli, $mb_email)."', ".(int)$mb_mobile.", ".
							"'".mysqli_real_escape_string($mysqli, $m_name)."', ".(int)$m_mobile.", NOW())";
		$res = mysqli_query($mysqli,$query);

		if (!$res)
			return false;

		$this->missing_id = mysqli_insert_id($mysqli);
		$this->getMissing($this->missing_id);
		$this->sendSMS();
		return true;
	}

	public function getPositions() {
		global $mysqli;
		if ($this->missing_id === -1)
			return false;

		$query = "SELECT `pos_id`, `acc`, `timestamp` FROM `positions` WHERE `missing_id` = ".(int)$this->missing_id;
		$res = mysqli_query($mysqli,$query);

		if (!$res)
			return false;

		$this->positions = array();
		while ($row = $res->fetch_assoc()) {
			$this->positions[(int)$row['timestamp']] = new Position($row['pos_id']);
		}
		krsort($this->positions);
		
		if(!is_array($this->positions) || sizeof($this->positions)==0)
			$this->last_pos = new position();
		else
			$this->last_pos = $this->positions[key($this->positions)];

		return $this->positions;
	}

	public function addPosition($lat, $lon, $acc, $alt, $timestamp, $useragent = '') {
		global $mysqli;
		if ($this->missing_id === -1)
			return false;

		$query = "INSERT INTO `positions`
				(`missing_id`, `lat`, `lon`, `acc`, `alt`, `timestamp`, `user_agent`)
				VALUES
					(".(int)$this->missing_id.", ".mysqli_real_escape_string($mysqli, $lat).", ".
					mysqli_real_escape_string($mysqli, $lon).", ".
					(int)$acc.", ".(int)$alt.", ".(int)$timestamp.", '".
					mysqli_real_escape_string($mysqli, $useragent)."')";

		$res = mysqli_query($mysqli,$query);

		if((int)$acc > 500) {
			$this->getMissing($this->missing_id);
			if($this->sms2_sent == 'false') {
				$this->_sendSMS($this->m_mobile, SMS2_TEXT);
				$query = "UPDATE `missing` SET `sms2_sent` = 'true' WHERE `missing_id` = '".$this->missing_id."';";
				$res = mysqli_query(SQLcon(), $query);
			}
		}

		if (!$res)
			return false;

		$pos_id = mysqli_insert_id($mysqli);
		$this->positions[(int)$timestamp] = new Position($pos_id);
	}
	
	
	public function sendSMS() {
		$query = "UPDATE `missing` SET `sms_sent` = NOW() WHERE `missing_id` = '".$this->missing_id."';";
		mysqli_query(SQLcon(), $query);
		$missing_warned = $this->_sendSMS($this->m_mobile, SMS_TEXT);
		if($missing_warned) {
		#	$this->_sendSMS($this->mb_mobile, 'Kopi av SMS: '.SMS_TEXT);
		}else
			$this->_sendSMS($this->mb_mobile, SMS_NOT_SENT);
	}
	
	private function _sendSMS($to, $message) {
		## Facebook-copy fix (includes 3 invisible chars..)
		if(strlen($to)==11&&(int)$to==0)
			$to = substr($to,3);
			
		$smsURL = utf8_decode('http://www.sveve.no/SMS/SendSMS'
							. '?user='.SMS_ACCOUNT
							. '&to='.$to
							. '&from='.SMS_FROM
							. '&msg='.urlencode(str_replace(array('#missing_id', '#mb_name'),
															array($this->missing_id.'-'.$to, $this->mb_name),
															$message))
							);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $smsURL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($curl);
		
		## INIT XML
		$res = substr($res, strpos($res, '<sms>'));
		$response = $this->_SVEVESMS_XML2Array($res);
		$response = $response['response'];
		## FIND ERRORS
		$FATAL = isset($response['errors']['fatal']);
		if($FATAL)
			return array('error' => true);
		## NO FATAL ERRORS, CHECK FOR MINOR MULTIPLE ERRORS
		if(isset($response['errors']['error']))
			return array('error'=>true, 'message'=>$response['errors']['error']['message']);
		return array('error'=>false);
	}
	
	############################################################
	## TRANSFORM XML TO AN ARRAY
	############################################################
	private function _SVEVESMS_XML2Array($xml, $recursive=false){
	    if (!$recursive)
	        $array = simplexml_load_string($xml);
	    else
	        $array = $xml;
	   
	    $newArray = array();
	    $array = (array) $array;
	    foreach($array as $key => $value) {
	        $value = (array) $value;
	        if(isset($value[0]))
	            $newArray[$key] = trim($value[0]);
	        else
	            $newArray[$key] = $this->_SVEVESMS_XML2Array($value, true);
	    }
	    return $newArray ;
	}
}
?>