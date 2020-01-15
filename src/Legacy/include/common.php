<?php

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

$GLOBALS['db'] = new \App\Legacy\MySQLiDatabase();
$GLOBALS['db']->connect($database_host, $database_user, $database_password, $database_instance);

if (strpos($_SERVER['REQUEST_URI'], '/styles/') !== false) {
    session_cache_expire(30);
    session_cache_limiter('public');
} else {
    session_cache_limiter('nocache');
}
$GLOBALS['session_handler'] = new \App\Legacy\Session($GLOBALS['db'], $session_max_lifetime);
$GLOBALS['session_handler']->use_me();
session_name('cwci'); // cookie in Welsh
session_start();

if ($GLOBALS['session_handler']->regenerate) {
    session_regenerate_id();
}

if (empty($_SESSION['user_id']) && empty($GLOBALS['is_login'])) {
    header('Location: /login.php');
    exit();
}

$GLOBALS['user'] = new \App\Legacy\User($GLOBALS['db'], $_SESSION['user_id'] ?? 0);
$GLOBALS['user_id'] = $_SESSION['user_id'] ?? 0;

if ($GLOBALS['user']->getTimezone() != '') {
    putenv('TZ=' . $GLOBALS['user']->getTimezone());
}

$todo_priority['normal'] = intval((($todo_priority['low'] - $todo_priority['high']) / 2) + $todo_priority['high']);
$GLOBALS['todo_priority'] = $todo_priority;

require_once __DIR__ . '/display_settings.php';
