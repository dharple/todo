<?php
require_once('../include/common.php');
require_once('../include/UserStylesheet.php');

header('Content-type: text/css');

$stylesheet_id = $user->getPrintStylesheetId();
$stylesheet = new UserStylesheet($db, $stylesheet_id);

print($stylesheet->getContents());
