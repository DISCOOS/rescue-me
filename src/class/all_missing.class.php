<?php
require_once(APP_PATH_CLASS.'missing.class.php');
class all_missing {
	public function __construct(){}
	
	
	public function getAllMissing($status='open') {
		$this->_status($status);
		global $mysqli;
		$query = "SELECT `missing_id`, `missing_name` FROM `missing` WHERE `status` {$this->where} ORDER BY `missing_reported` DESC";
		$res = mysqli_query($mysqli,$query);

		if (!$res)
			return false;

		$missing_ids = array();
		while ($row = $res->fetch_assoc()) {
			$missing = new missing();
			$missing->getMissing($row['missing_id']);

			$missing_ids[$row['missing_id']] = $missing;
		}
		return $missing_ids;
	}

	private function _status($selected) {
		switch( $selected ) {
			case 'open': 		$where = "!= 'Closed'";		break;
			case 'closed':		$where = "= 'Closed'";		break;
		}
		$this->where = $where;

	}
}