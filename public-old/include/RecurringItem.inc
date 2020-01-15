<?php

require_once('BaseObject.inc');

class RecurringItem extends BaseObject
{

    function RecurringItem($db, $id = 0)
    {
        $this->db = $db;
        $this->tableName = 'recurring_item';
        $this->idField = 'id';

        if ($id) {
            $this->load($id);
        }
    }

    function getUserId()
    {
        return $this->data['user_id'];
    }

    function setUserId($user_id)
    {
        $this->data['user_id'] = $user_id;
    }

    function getTask()
    {
        return $this->data['task'];
    }

    function setTask($task)
    {
        $this->data['task'] = $task;
    }
}
