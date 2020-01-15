<?php

$is_login = true;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Legacy\Entity\User;
use App\Legacy\Entity\UserStylesheet;
use App\Legacy\ItemStats;
use App\Legacy\ListDisplay;

if (isset($_REQUEST['user_id'])) {
    $_SESSION['user_id'] = $_REQUEST['user_id'];
    $user = new User($db, $_SESSION['user_id']);
    $user_id = $_SESSION['user_id'];
}

?>
<html>
<head>
<title>To Do List For <?php print(date('F jS, Y')); ?></title>
<style type="text/css">
<!--
<?php



$stylesheet_id = $user->getExportStylesheetId();
$stylesheet = new UserStylesheet($db, $stylesheet_id);

print($stylesheet->getContents());

?>
-->
</style>
</head>
<body>
<p align=center><b><?php print(date('F jS, Y')); ?></b></p>
<?php

/*
 * Results
 */

$itemStats = new ItemStats($db, $_SESSION['user_id']);

$listDisplay = new ListDisplay($db, $_SESSION['user_id']);
$listDisplay->setColumns($display_num_columns);
$listDisplay->setInternalPriorityLevels($todo_priority);

$listDisplay->setFilterClosed($display_filter_closed);
$listDisplay->setFilterPriority($display_filter_priority);
$listDisplay->setFilterAging($display_filter_aging);
$listDisplay->setShowEstimate($display_show_estimate);
$listDisplay->setShowInactive($display_show_inactive);
$listDisplay->setShowSection($display_show_section);
$listDisplay->setShowPriority($display_show_priority);

print($listDisplay->getOutput());

?>
</body>
<?php
if (isset($_REQUEST['user_id'])) {
    $_SESSION['user_id'] = '';
}
?>
