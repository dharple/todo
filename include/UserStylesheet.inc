<?php

require_once('BaseObject.inc');

class UserStylesheet extends BaseObject {

	function UserStylesheet($db, $id = 0) {
		$this->db = $db;
		$this->tableName = 'user_stylesheet';
		$this->idField = 'id';

		if ($id) {
			$this->load($id);
		}
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($user_id) {
		$this->data['user_id'] = $user_id;
	}

	function getSheetName() {
		return $this->data['sheet_name'];
	}

	function setSheetName($sheet_name) {
		$this->data['sheet_name'] = $sheet_name;
	}

	function getSheetType() {
		return $this->data['sheet_type'];
	}

	function setSheetType($sheet_type) {
		$this->data['sheet_type'] = $sheet_type;
	}

	function getPublic() {
		return $this->data['public'];
	}

	function setPublic($public) {
		$this->data['public'] = $public;
	}

	function getContents() {
		return $this->data['contents'];
	}

	function setContents($contents) {
		$this->data['contents'] = $contents;
	}

}

?>
