<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

require_once __DIR__ . '/config.php';

$db = new \App\Legacy\MySQLiDatabase();
$db->connect($database_host, $database_user, $database_password, $database_instance);

if (strpos($_SERVER['REQUEST_URI'], '/styles/') !== false) {
    session_cache_expire(30);
    session_cache_limiter('public');
} else {
    session_cache_limiter('nocache');
}
$session_handler = new \App\Legacy\Session($db, $session_max_lifetime);
$session_handler->use_me();
session_name('cwci'); // cookie in Welsh
session_start();

if ($session_handler->regenerate) {
    session_regenerate_id();
}

if (empty($_SESSION['user_id']) && empty($is_login)) {
    header('Location: /login.php');
    exit();
}

$user = new \App\Legacy\User($db, $_SESSION['user_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if ($user->getTimezone() != '') {
    putenv('TZ=' . $user->getTimezone());
}

$todo_priority['normal'] = intval((($todo_priority['low'] - $todo_priority['high']) / 2) + $todo_priority['high']);
