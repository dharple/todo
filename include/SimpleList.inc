<?php

require_once("BaseObject.inc");

class SimpleList {

	var $obj;
	var $objName;

	function SimpleList($db, $objName) {
		$this->db = $db;
		$this->objName = $objName;
		$this->obj = new $objName($db);
	}

	function load($criteria) {
		$ret = array();

		$query = "SELECT " . $this->obj->tableName . ".* FROM " . $this->obj->tableName . " " . $criteria;
		$result = $this->db->query($query);
		if(!$result){
			return $ret;
		}

		while($row = $this->db->fetchAssoc($result)) {
			$work = new $this->objName($this->db);
			$work->setData($row);
			array_push($ret, $work);
		}
		
		return $ret;
	}

	function count($criteria) {
		$query = "SELECT COUNT(*) FROM " . $this->obj->tableName . " " . $criteria;
		$result = $this->db->query($query);
		if(!$result){
			return $ret;
		}

		$row = $this->db->fetchRow($result);

		return intval($row[0]);
	}

}

?>
