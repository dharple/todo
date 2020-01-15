<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

header('Content-type: text/css');

$stylesheet_id = $user->getPrintStylesheetId();
$stylesheet = new \App\Legacy\UserStylesheet($db, $stylesheet_id);

print($stylesheet->getContents());
