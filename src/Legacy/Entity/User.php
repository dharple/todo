<?php

namespace App\Legacy\Entity;

class User extends BaseObject
{

    public $tableName = 'user';

    public function login($username, $password)
    {
        $query = "SELECT id FROM user WHERE username = '" . addslashes($username) . "' AND password = ENCRYPT('" . addslashes($password) . "', password)";

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        if ($row['id'] !== null) {
            $this->load($row['id']);
            return true;
        } else {
            return false;
        }
    }

    public function getUsername()
    {
        return $this->data['username'];
    }

    public function setUsername($username)
    {
        $this->data['username'] = $username;
    }

    public function getFullname()
    {
        return $this->data['fullname'];
    }

    public function setFullname($fullname)
    {
        $this->data['fullname'] = $fullname;
    }

    public function confirmPassword($password)
    {
        $check = crypt($password, $this->data['password']);
        return ($check == $this->data['password']);
    }

    public function setPassword($password)
    {
        $salt = substr(trim($this->data['password']), -2);
        if ($salt == '') {
            $salt = '69';
        }

        $this->data['password'] = crypt($password, $salt);
    }

    public function getTimezone()
    {
        return $this->data['timezone'];
    }

    public function setTimezone($timezone)
    {
        $this->data['timezone'] = $timezone;
    }
}
