<?php

require_once(APP_PATH_INC.'common.inc.php');

class Position {
	public $pos_id = -1;
	public $lat = -1;
	public $lon = -1;
	public $acc = -1;
	public $alt = -1;
	public $timestamp = -1;
	public $human = 'Aldri posisjonert';

	function __construct($pos_id = -1) {
		$this->pos_id = (int)$pos_id;
		$this->loadData();
	}

	function loadData() {
		global $mysqli;
		if ($this->pos_id === -1)
			return false;

		$query = "SELECT * FROM `positions` WHERE `pos_id` = ".(int)$this->pos_id;
		$res = mysqli_query($mysqli,$query);

		if (!$res)
			return false;

		$row = $res->fetch_assoc();
		$this->lat = $row['lat'];
		$this->lon = $row['lon'];
		$this->acc = $row['acc'];
		$this->alt = $row['alt'];
		$this->timestamp = $row['timestamp'];
		$this->human = date('Y-m-d H:i:s', $row['timestamp']);
	}
}

?>