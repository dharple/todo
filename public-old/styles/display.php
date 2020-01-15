<?php
require_once('../include/common.php');

header('Content-type: text/css');

$stylesheet_id = $user->getDisplayStylesheetId();
$stylesheet = new \App\Legacy\UserStylesheet($db, $stylesheet_id);

print($stylesheet->getContents());
