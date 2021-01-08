<?php

use App\Auth\Guard;
use App\Helper;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(dirname(__DIR__)).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

// Legacy code

session_cache_limiter('nocache');
session_start();

$user = null;
try {
    $user = Guard::getUser();
} catch (Exception $e) {
    Helper::getLogger()->debug($e->getMessage());
    if (!preg_match('/login.php/', $_SERVER['SCRIPT_NAME'])) {
        header('Location: /login.php');
        exit();
    }
}

try {
    if ($user !== null) {
        Helper::setTimezone();
    }
} catch (Exception $e) {
    Helper::getLogger()->warning(sprintf('Failed to set timezone: %s', $e->getMessage()));
}
