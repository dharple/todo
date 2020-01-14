<?php

require_once('BaseObject.inc');

class User extends BaseObject {

	function User($db, $id = 0) {
		$this->db = $db;
		$this->tableName = 'user';
		$this->idField = 'id';

		if ($id) {
			$this->load($id);
		}
	}

	function login($username, $password) {
		$query = "SELECT id FROM user WHERE username = '$username' AND password = ENCRYPT('$password', password)";

		$result = $this->db->query($query);
		$row = $this->db->fetchAssoc($result);

		if($row['id'] !== NULL) {
			$this->load($row['id']);
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	function getUsername() {
		return $this->data['username'];
	}

	function setUsername($username) {
		$this->data['username'] = $username;
	}

	function getFullname() {
		return $this->data['fullname'];
	}

	function setFullname($fullname) {
		$this->data['fullname'] = $fullname;
	}

	function confirmPassword($password) {
		$check = crypt($password, $this->data['password']);
		return ($check == $this->data['password']);
	}

	function setPassword($password) {
		$salt = substr(trim($this->data['password']), -2);
		if($salt == '')
			$salt = '69';

		$this->data['password'] = crypt($password, $salt);
	}

	function getTimezone() {
		return $this->data['timezone'];
	}

	function setTimezone($timezone) {
		$this->data['timezone'] = $timezone;
	}

	function getDisplayStylesheetId() {
		return $this->data['display_stylesheet_id'];
	}

	function setDisplayStylesheetId($display_stylesheet_id) {
		$this->data['display_stylesheet_id'] = $display_stylesheet_id;
	}

	function getPrintStylesheetId() {
		return $this->data['print_stylesheet_id'];
	}

	function setPrintStylesheetId($print_stylesheet_id) {
		$this->data['print_stylesheet_id'] = $print_stylesheet_id;
	}

	function getExportStylesheetId() {
		return $this->data['export_stylesheet_id'];
	}

	function setExportStylesheetId($export_stylesheet_id) {
		$this->data['export_stylesheet_id'] = $export_stylesheet_id;
	}
}

?>
