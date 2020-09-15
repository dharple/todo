<?php

use App\Legacy\Entity\User;

$errors = [];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Login') {
        $user = new User($db);
        $ret = $user->login($_POST['username'], $_POST['password']);

        if ($ret) {
            $_SESSION['user_id'] = $user->getId();
            header('Location: index.php');
            exit();
        } else {
            $errors[] = 'Invalid login';
        }
    }
}

$twig->display('login.html.twig', [
    'errors' => $errors,
    'user'   => $GLOBALS['user'],
]);
