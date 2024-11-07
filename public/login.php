<?php

use App\Auth\Guard;
use App\Helper;

$errors = [];

try {
    $log = Helper::getLogger();
} catch (Exception $e) {
    print('An error occurred...');
    exit;
}

try {
    $twig = Helper::getTwig();
} catch (Exception $e) {
    $log->critical($e->getMessage());
    print('An error occurred...');
    exit;
}

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Login') {
        try {
            Guard::login($_POST['username'], $_POST['password']);
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            $errors[] = 'Invalid login';
        }
    }
}

try {
    $user = Guard::getUser();
} catch (Exception $e) {
    $user = null;
}

try {
    $twig->display('login.html.twig', [
        'errors' => $errors,
        'user' => $user,
    ]);
} catch (Exception $e) {
    $log->critical($e->getMessage());
    print('An error occurred...');
    exit;
}
