<?php

class Session extends BaseObject
{

    var $regenerate;
    var $maxLifetime;

    function Session($db, $maxLifetime = null)
    {
        $this->db = $db;
        $this->tableName = 'session';
        $this->idField = 'id';

        $this->regenerate = false;

        if ($maxLifetime === null || intval($maxLifetime) < 0) {
            $this->maxLifetime = 2 * 3600; // 4 hours
        } else {
            $this->maxLifetime = intval($maxLifetime);
        }
    }

    function loadBySessionId($session_id)
    {
        $query = 'SELECT * FROM ' . $this->tableName . " WHERE session_id = '" . addslashes($session_id) . "'";
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        $this->data = $row;
    }

    function open($save_path, $session_name)
    {
        return true;
    }

    function close()
    {
        if (rand(0, 10) == 5) {
            $this->gc(0);
        }

        return true;
    }

    function read($session_id)
    {
        global $_SERVER;

        $this->loadBySessionId($session_id);

        // Stop Session Stealing //
        if ($this->data['ip'] != '' && $this->data['ip'] != $_SERVER['REMOTE_ADDR']) {
            $this->regenerate = true;
            return '';
        }

        if (!isset($this->data['contents'])) {
            return '';
        }

        return $this->data['contents'];
    }

    function write($session_id, $value)
    {
        global $_SERVER;

        $this->loadBySessionId($session_id);

        // Stop Session Stealing //
        if ($this->data['ip'] != '' && $this->data['ip'] != $_SERVER['REMOTE_ADDR']) {
            return false;
        }

        if ($this->data[$this->idField] == 0) {
            $this->data['session_id'] = $session_id;
        }

        $this->data['contents'] = $value;
        $this->data['stamp'] = 'NOW()';

        $this->data['ip'] = $_SERVER['REMOTE_ADDR'];

        $this->save();

        return true;
    }

    function destroy($session_idid)
    {
        $this->loadBySessionId($session_id);

        $this->delete();

        return true;
    }

    function gc($ignore)
    {
        $query = "DELETE FROM session WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(stamp)) > '" . $this->maxLifetime . "'";
        $this->db->query($query);

        return true;
    }

    function use_me()
    {
        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
    }
}
