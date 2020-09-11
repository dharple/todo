<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Entity;

class User extends BaseObject
{

    public $tableName = 'user';

    public function login($username, $password)
    {
        $query = "SELECT id, password FROM user WHERE username = '" . addslashes($username) . "'";
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        if (empty($row)) {
            return false;
        }

        if (!password_verify($password, $row['password'])) {
            return false;
        }

        $this->load($row['id']);
        return true;
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
        return password_verify($password, $this->data['password']);
    }

    public function setPassword($password)
    {
        $this->data['password'] = password_hash($password, PASSWORD_DEFAULT);
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
