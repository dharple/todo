<?php

use App\Auth\Guard;
use App\Helper;

$errors = [];

$twig = Helper::getTwig();

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Login') {
        try {
            $user = Guard::login($_POST['username'], $_POST['password']);

            $_SESSION['user_id'] = $user->getId();
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            $errors[] = 'Invalid login';
        }
    }
}

try {
    $user = Helper::getUser();
} catch (Exception $e) {
    $user = null;
}

try {
    $twig->display('login.html.twig', [
        'errors' => $errors,
        'user' => $user,
    ]);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
