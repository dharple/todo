<?php

require_once("Database.inc");

class BaseObject {

	var $db;
	var $data;
	var $tableName;
	var $idField;

	function load($id) {
		$query = "SELECT * FROM " . $this->tableName . " WHERE " . $this->idField . " = '" . $id . "'";
    // print_r($query);
		$result = $this->db->query($query);
		$row = $this->db->fetchAssoc($result);

		$this->data = $row;
	}

	function save() {
		$queryElements = array();

		foreach($this->data as $field => $value) {
			if ($field == $this->idField) {
				continue;
			}

			if ($value == 'NOW()') {
				//
				// XXX - Expand this
				//
				array_push($queryElements, $field . "=" . $value);
			}
			else {
				array_push($queryElements, $field . "='" . addslashes($value) . "'");
			}
		}

		if ($this->data[$this->idField] == 0) {
			$query = "INSERT INTO " . $this->tableName . " SET " . implode(",", $queryElements);
		}
		else {
			$query = "UPDATE " . $this->tableName . " SET " . implode(",", $queryElements) . " WHERE " . $this->idField . " = '" . $this->data[$this->idField] . "'";
		}
		// print_r($query);

		$result = $this->db->query($query);

		if (!$result) {
			return false;
		}

		if ($this->data[$this->idField] == 0) {
			$this->data[$this->idField] = $this->db->lastInsertId();
		}

		return true;
	}

	function delete() {
		if ($this->data[$this->idField] > 0) {
			$query = "DELETE FROM " . $this->tableName . " WHERE " . $this->idField . " = '" . $this->data[$this->idField] . "'";
			$result = $this->db->query($query);
			if ($result) {
				return true;
			}
		}

		return false;
	}

	function setData($data) {
		$this->data = $data;
	}

	function getId() {
		return $this->data[$this->idField];
	}

	function clearId() {
		$this->data[$this->idField] = 0;
	}

	function getErrorMessage() {
		return $this->db->getErrorMessage();
	}

	function getErrorNumber() {
		return $this->db->getErrorNumber();
	}


}

?>
