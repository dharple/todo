<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Auth\Guard;
use App\Helper;

session_cache_limiter('nocache');
session_start();

$user = null;
try {
    $user = Guard::getUser();
} catch (Exception $e) {
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

require __DIR__ . '/display_settings.php';
