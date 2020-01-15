<?php

require_once('include/common.php');
include_once('include/display_settings.php');

header('Content-Disposition: attachment; filename="todo-' . date('Ymd') . '.php"');

?>
<html>
<head>
<title>To Do List For <?php print(date('F jS, Y')); ?></title>
<style type="text/css">
<!--
<?php



$stylesheet_id = $user->getExportStylesheetId();
$stylesheet = new \App\Legacy\UserStylesheet($db, $stylesheet_id);

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

$itemStats = new \App\Legacy\ItemStats($db, $_SESSION['user_id']);

$footer = '<i>Items Done Today: ' . $itemStats->doneToday() . ', Yesterday: ' . $itemStats->doneYesterday();
$footer .= ', This Week: ' . $itemStats->doneThisWeek() . ', Last Week: ' . $itemStats->doneLastWeek();
$footer .= ', This Month: ' . $itemStats->doneThisMonth() . ', Last Month: ' . $itemStats->doneLastMonth();
$footer .= ', Since Start: ' . $itemStats->doneTotal();

$query = "SELECT AVG(IFNULL(TO_DAYS(item.completed) - TO_DAYS(item.created) + 1, TO_DAYS(NOW()) - TO_DAYS(item.created) + 1)) FROM item LEFT JOIN section ON item.section_id = section.id WHERE item.user_id = '$user_id' AND (item.status = 'closed' OR (item.status = 'open' AND section.status = 'active'))";
$result = $db->query($query);
$row = $db->fetchRow($result);
$footer .= ', Avg: ' . number_format($row[0], 1) . ' days';

$footer .= '<br><br>Items Shown: {GRAND_TOTAL}';

$footer .= '<br>Items Not Shown: {NOT_SHOWN}';

$footer .= '</i>';

/*
 *
 */

$listDisplay = new \App\Legacy\ListDisplay($db, $_SESSION['user_id']);
$listDisplay->setColumns($display_num_columns);
$listDisplay->setInternalPriorityLevels($todo_priority);

$listDisplay->setFilterClosed($display_filter_closed);
$listDisplay->setFilterPriority($display_filter_priority);
$listDisplay->setFilterAging($display_filter_aging);
$listDisplay->setShowEstimate($display_show_estimate);
$listDisplay->setShowInactive($display_show_inactive);
$listDisplay->setShowSection($display_show_section);
$listDisplay->setShowPriority($display_show_priority);

$listDisplay->setColumnFooter(1, $footer);

print($listDisplay->getOutput());


?>
</body>
