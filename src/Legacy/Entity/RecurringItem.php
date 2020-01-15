<?php

namespace App\Legacy\Entity;

class RecurringItem extends BaseObject
{

    public $tableName = 'recurring_item';

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
