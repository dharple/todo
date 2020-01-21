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

class Section extends BaseObject
{

    public $tableName = 'section';

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
