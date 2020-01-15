<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Legacy\ItemStats;
use App\Legacy\ListDisplay;

?>
<html>
<head>
<title>To Do List For <?php print(date('F jS, Y')); ?></title>
<link rel="stylesheet" href="styles/printable.php" type="text/css">
</head>
<body onLoad="window.print();">
<p align=center><b><?php print(date('F jS, Y')); ?></b> - <b><?php print($user->getFullname()); ?></b></p>

<?php

/*
 * Results
 */

$itemStats = new ItemStats($db, $_SESSION['user_id']);

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

$listDisplay->setCheckClosed('y');

$listDisplay->setColumnFooter(1, $footer);

print($listDisplay->getOutput());

?>
</body>
