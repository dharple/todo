<?php

require dirname(__DIR__) . '/vendor/autoload.php';
include dirname(__DIR__) . '/config.php';

use App\Legacy\Entity\Session;
use App\Legacy\Entity\User;
use App\Legacy\MySQLiDatabase;

if (
    !isset($database_host) ||
    !isset($database_instance) ||
    !isset($database_password) ||
    !isset($database_user) ||
    !isset($session_max_lifetime) ||
    !isset($todo_priority)
) {
    throw new \Exception('Missing configuration values');
}

$GLOBALS['db'] = new MySQLiDatabase();
$GLOBALS['db']->connect($database_host, $database_user, $database_password, $database_instance);

session_cache_limiter('nocache');

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
    date_default_timezone_set($GLOBALS['user']->getTimezone());
    $query = 'SET time_zone="' . addslashes($GLOBALS['user']->getTimezone()) . '"';
    $GLOBALS['db']->query($query);
}

$todo_priority['normal'] = intval((($todo_priority['low'] - $todo_priority['high']) / 2) + $todo_priority['high']);
$GLOBALS['todo_priority'] = $todo_priority;

$loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__FILE__)) . '/templates');
$twig = new \Twig\Environment($loader);

require __DIR__ . '/display_settings.php';
