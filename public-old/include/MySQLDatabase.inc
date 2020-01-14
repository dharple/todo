<?php

// require_once('Database.inc');

// class MySQLDatabase implements Database {
class MySQLDatabase {

	var $conn;
	var $error;
	var $errno;

	function connect($host, $user, $pass, $db) {
		$this->conn = mysql_connect($host, $user, $pass);
		mysql_select_db($db, $this->conn);
	}

	function query($query) {
		$resultSet = mysql_query($query, $this->conn);
		if(!$resultSet) {
			$this->error = mysql_error();
			$this->errno = mysql_errno();
			return false;
		}
		else {
			return $resultSet;
		}
	}

	function fetchRow($resultSet) {
		$row = mysql_fetch_row($resultSet);
		return $row;
	}

	function fetchAssoc($resultSet) {
		$row = mysql_fetch_assoc($resultSet);
		return $row;
	}

	function getErrorMessage() {
		return $this->error;
	}

	function getErrorNumber() {
		return $this->errno;
	}

	function lastInsertId() {
		return mysql_insert_id($this->conn);
	}

}

?>
