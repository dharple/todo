<?php

require_once('config.inc');

require_once($database_class . '.inc');
require_once('SimpleList.inc');
require_once('Section.inc');
require_once('Item.inc');
require_once('User.inc');
require_once('Session.inc');

$db = new $database_class();
$db->connect($database_host, $database_user, $database_password, $database_instance);

if(strpos($_SERVER['REQUEST_URI'], '/styles/') !== FALSE) {
	session_cache_expire(30);
	session_cache_limiter('public');
}
else {
	session_cache_limiter('nocache');
}
$session_handler = new Session($db, $session_max_lifetime);
$session_handler->use_me();
session_name('cwci'); // cookie in Welsh
session_start();

if($session_handler->regenerate) {
	session_regenerate_id();
}

if($_SESSION['user_id'] == '' && !$is_login) {
	header('Location: login.html');
	exit();
}

$user = new User($db, $_SESSION['user_id']);
$user_id = $_SESSION['user_id'];

if($user->getTimezone() != '')
	putenv('TZ=' . $user->getTimezone());

$todo_priority['normal'] = intval((($todo_priority['low'] - $todo_priority['high']) / 2) + $todo_priority['high']);

?>
