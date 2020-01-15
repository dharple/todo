<?php

namespace App\Legacy;

class Section extends BaseObject
{

    public function __construct($db, $id = 0)
    {
        $this->db = $db;
        $this->tableName = 'section';
        $this->idField = 'id';

        if ($id) {
            $this->load($id);
        }
    }

    public function getName()
    {
        return $this->data['name'];
    }

    public function setName($name)
    {
        $this->data['name'] = $name;
    }

    public function getUserId()
    {
        return $this->data['user_id'];
    }

    public function setUserId($user_id)
    {
        $this->data['user_id'] = $user_id;
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function setStatus($status)
    {
        $this->data['status'] = $status;
    }
}
