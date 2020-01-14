<?php

require_once("BaseObject.inc");

class Item extends BaseObject {

	function Item($db, $id = 0) {
		$this->db = $db;
		$this->tableName = "item";
		$this->idField = "id";

		if ($id) {
			$this->load($id);
		}
	}

	function getSectionId() {
		return $this->data["section_id"];
	}

	function setSectionId($section_id) {
		$this->data["section_id"] = $section_id;
	}

	function getTask() {
		return $this->data["task"];
	}

	function setTask($task) {
		$this->data["task"] = $task;
	}

	function getStatus() {
		return $this->data["status"];
	}

	function setStatus($status) {
		$this->data["status"] = $status;
	}

	function getCreated() {
		return $this->data["created"];
	}

	function setCreated($created) {
		$this->data["created"] = $created;
	}

	function getCompleted() {
		return $this->data["completed"];
	}

	function setCompleted($completed) {
		$this->data["completed"] = $completed;
	}

	function getPriority() {
		return $this->data["priority"];
	}

	function setPriority($priority) {
		$this->data["priority"] = $priority;
	}

	function getEstimate() {
		return $this->data["estimate"];
	}

	function setEstimate($estimate) {
		$this->data["estimate"] = $estimate;
	}

	function getUserId() {
		return $this->data["user_id"];
	}

	function setUserId($user_id) {
		$this->data["user_id"] = $user_id;
	}

}

?>
