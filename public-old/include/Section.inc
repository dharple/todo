<?php

require_once('BaseObject.inc');

class Section extends BaseObject
{

    function Section($db, $id = 0)
    {
        $this->db = $db;
        $this->tableName = 'section';
        $this->idField = 'id';

        if ($id) {
            $this->load($id);
        }
    }

    function getName()
    {
        return $this->data['name'];
    }

    function setName($name)
    {
        $this->data['name'] = $name;
    }

    function getUserId()
    {
        return $this->data['user_id'];
    }

    function setUserId($user_id)
    {
        $this->data['user_id'] = $user_id;
    }

    function getStatus()
    {
        return $this->data['status'];
    }

    function setStatus($status)
    {
        $this->data['status'] = $status;
    }
}
