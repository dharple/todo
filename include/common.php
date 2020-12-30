<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Helper;

session_cache_limiter('nocache');
session_start();

if (empty($_SESSION['user_id']) && !preg_match('/login.php/', $_SERVER['SCRIPT_NAME'])) {
    header('Location: /login.php');
    exit();
}

try {
    if (!empty($_SESSION['user_id'])) {
        Helper::setTimezone();
    }
} catch (Exception $e) {
    // do nothing
}

require __DIR__ . '/display_settings.php';
