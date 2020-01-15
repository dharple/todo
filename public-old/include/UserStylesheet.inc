<?php

require_once('BaseObject.inc');

class UserStylesheet extends BaseObject
{

    public function __construct($db, $id = 0)
    {
        $this->db = $db;
        $this->tableName = 'user_stylesheet';
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

    public function getSheetName()
    {
        return $this->data['sheet_name'];
    }

    public function setSheetName($sheet_name)
    {
        $this->data['sheet_name'] = $sheet_name;
    }

    public function getSheetType()
    {
        return $this->data['sheet_type'];
    }

    public function setSheetType($sheet_type)
    {
        $this->data['sheet_type'] = $sheet_type;
    }

    public function getPublic()
    {
        return $this->data['public'];
    }

    public function setPublic($public)
    {
        $this->data['public'] = $public;
    }

    public function getContents()
    {
        return $this->data['contents'];
    }

    public function setContents($contents)
    {
        $this->data['contents'] = $contents;
    }
}
