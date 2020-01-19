<?php

require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
include dirname(dirname(dirname(__DIR__))) . '/config.php';

use App\Legacy\Entity\Session;
use App\Legacy\Entity\User;
use App\Legacy\MySQLiDatabase;

$GLOBALS['db'] = new MySQLiDatabase();
$GLOBALS['db']->connect($database_host, $database_user, $database_password, $database_instance);

if (isset($_SERVER['REQUEST_URI'])) {
    if (strpos($_SERVER['REQUEST_URI'], '/styles/') !== false) {
        session_cache_expire(30);
        session_cache_limiter('public');
    } else {
        session_cache_limiter('nocache');
    }
    $GLOBALS['session_handler'] = new Session($GLOBALS['db'], $session_max_lifetime);
    $GLOBALS['session_handler']->initialize();
    session_name('cwci'); // cookie in Welsh
    session_start();

    if ($GLOBALS['session_handler']->regenerate) {
        session_regenerate_id();
    }

    if (empty($_SESSION['user_id']) && !preg_match('/login.php/', $_SERVER['SCRIPT_NAME'])) {
        header('Location: /login.php');
        exit();
    }

    $GLOBALS['user'] = new User($GLOBALS['db'], $_SESSION['user_id'] ?? 0);
    $GLOBALS['user_id'] = $_SESSION['user_id'] ?? 0;

    if ($GLOBALS['user']->getTimezone() != '') {
        putenv('TZ=' . $GLOBALS['user']->getTimezone());
    }
}

$todo_priority['normal'] = intval((($todo_priority['low'] - $todo_priority['high']) / 2) + $todo_priority['high']);
$GLOBALS['todo_priority'] = $todo_priority;

require __DIR__ . '/display_settings.php';
