<?php

use App\Legacy\Entity\User;

$error_message = '';

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

$errors = [];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Update') {
        $user = new User($db, $user->getId());

        $user->setFullname($_POST['fullname']);
        if ($_POST['timezone'] == 'Other') {
            $user->setTimezone($_POST['timezone_other']);
        } else {
            $user->setTimezone($_POST['timezone']);
        }

        $user->save();
    } elseif ($_POST['submitButton'] == 'Change Password') {
        $user = new User($db, $user->getId());

        $ret = $user->confirmPassword($_POST['old_password']);
        if ($ret && $_POST['new_password'] == $_POST['confirm']) {
            $user->setPassword($_POST['new_password']);
            $user->save();
        } else {
            if (!$ret) {
                $errors[] = 'Incorrect password';
            } else {
                $error[] = 'New passwords do not match';
            }
        }
    }
}

$timezones = timezone_identifiers_list(DateTimeZone::PER_COUNTRY, 'US');

$twig->display('account.html.twig', [
    'errors'    => $errors,
    'timezones' => $timezones,
    'user'      => $user,
]);
