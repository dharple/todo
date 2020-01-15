<?php

namespace App\Legacy;

class RecurringItem extends BaseObject
{

    public function __construct($db, $id = 0)
    {
        $this->db = $db;
        $this->tableName = 'recurring_item';
        $this->idField = 'id';

        if ($id) {
            $this->load($id);
        }
    }

    public function getUserId()
    {
        return $this->data['user_id'];
    }

    public function setUserId($user_id)
    {
        $this->data['user_id'] = $user_id;
    }

    public function getTask()
    {
        return $this->data['task'];
    }

    public function setTask($task)
    {
        $this->data['task'] = $task;
    }
}
