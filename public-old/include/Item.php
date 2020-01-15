<?php

require_once('BaseObject.php');

class Item extends BaseObject
{

    public function __construct($db, $id = 0)
    {
        $this->db = $db;
        $this->tableName = 'item';
        $this->idField = 'id';

        if ($id) {
            $this->load($id);
        }
    }

    public function getSectionId()
    {
        return $this->data['section_id'];
    }

    public function setSectionId($section_id)
    {
        $this->data['section_id'] = $section_id;
    }

    public function getTask()
    {
        return $this->data['task'];
    }

    public function setTask($task)
    {
        $this->data['task'] = $task;
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function setStatus($status)
    {
        $this->data['status'] = $status;
    }

    public function getCreated()
    {
        return $this->data['created'];
    }

    public function setCreated($created)
    {
        $this->data['created'] = $created;
    }

    public function getCompleted()
    {
        return $this->data['completed'];
    }

    public function setCompleted($completed)
    {
        $this->data['completed'] = $completed;
    }

    public function getPriority()
    {
        return $this->data['priority'];
    }

    public function setPriority($priority)
    {
        $this->data['priority'] = $priority;
    }

    public function getEstimate()
    {
        return $this->data['estimate'];
    }

    public function setEstimate($estimate)
    {
        $this->data['estimate'] = $estimate;
    }

    public function getUserId()
    {
        return $this->data['user_id'];
    }

    public function setUserId($user_id)
    {
        $this->data['user_id'] = $user_id;
    }
}
