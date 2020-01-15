<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use App\Legacy\Entity\UserStylesheet;

header('Content-type: text/css');

$stylesheet_id = $user->getDisplayStylesheetId();
$stylesheet = new UserStylesheet($db, $stylesheet_id);

print($stylesheet->getContents());
