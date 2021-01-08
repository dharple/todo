<?php

use App\Auth\Guard;
use App\Helper;

$twig = Helper::getTwig();

try {
    $em = Helper::getEntityManager();
    $user = Guard::getUser();
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}

$errors = [];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Update') {
        try {
            $user->setFullname($_POST['fullname']);
            if ($_POST['timezone'] == 'Other') {
                $user->setTimezone($_POST['timezone_other']);
            } else {
                $user->setTimezone($_POST['timezone']);
            }

            $em->persist($user);
            $em->flush();
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to update user information: %s', $e->getMessage());
        }
    } elseif ($_POST['submitButton'] == 'Change Password') {
        try {
            $ret = Guard::checkPassword($user, $_POST['old_password']);
            if ($ret && $_POST['new_password'] == $_POST['confirm']) {
                Guard::setPassword($user, $_POST['new_password']);
                $em->persist($user);
                $em->flush();
            } elseif (!$ret) {
                $errors[] = 'Incorrect password';
            } else {
                $errors[] = 'New passwords do not match';
            }
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to change password: %s', $e->getMessage());
        }
    }
}

$timezones = timezone_identifiers_list(DateTimeZone::PER_COUNTRY, 'US');

try {
    $twig->display('account.html.twig', [
        'errors' => $errors,
        'timezones' => $timezones,
        'user' => $user,
    ]);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
