<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Legacy\Entity\Session;
use App\Legacy\Entity\User;
use App\Legacy\MySQLiDatabase;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__DIR__) . '/.env');

if (
    !isset($_ENV['DATABASE_HOST']) ||
    !isset($_ENV['DATABASE_INSTANCE']) ||
    !isset($_ENV['DATABASE_PASSWORD']) ||
    !isset($_ENV['DATABASE_USER']) ||
    !isset($_ENV['SESSION_MAX_LIFETIME'])
) {
    throw new Exception('Missing configuration values');
}

$GLOBALS['db'] = new MySQLiDatabase();
$GLOBALS['db']->connect($_ENV['DATABASE_HOST'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_INSTANCE']);

session_cache_limiter('nocache');

$GLOBALS['session_handler'] = new Session($GLOBALS['db'], $_ENV['SESSION_MAX_LIFETIME']);
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

$todo_priority = [
    'high' => 1,
    'low'  => 10,
];

$todo_priority['normal'] = intval((($todo_priority['low'] - $todo_priority['high']) / 2) + $todo_priority['high']);
$GLOBALS['todo_priority'] = $todo_priority;

$loader = new FilesystemLoader(dirname(dirname(__FILE__)) . '/templates');
$twig = new Environment($loader);

require __DIR__ . '/display_settings.php';
