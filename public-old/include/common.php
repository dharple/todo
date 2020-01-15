<?php

require_once('config.php');

require_once($database_class . '.php');
require_once('SimpleList.php');
require_once('Section.php');
require_once('Item.php');
require_once('User.php');
require_once('Session.php');

$db = new $database_class();
$db->connect($database_host, $database_user, $database_password, $database_instance);

if (strpos($_SERVER['REQUEST_URI'], '/styles/') !== false) {
    session_cache_expire(30);
    session_cache_limiter('public');
} else {
    session_cache_limiter('nocache');
}
$session_handler = new Session($db, $session_max_lifetime);
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

$user = new User($db, $_SESSION['user_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if ($user->getTimezone() != '') {
    putenv('TZ=' . $user->getTimezone());
}

$todo_priority['normal'] = intval((($todo_priority['low'] - $todo_priority['high']) / 2) + $todo_priority['high']);
