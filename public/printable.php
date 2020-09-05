<?php

use App\Legacy\ItemStats;
use App\Legacy\ListDisplay;

?>
<html>
<head>
<title>To Do List For <?php print(date('F jS, Y')); ?></title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<link rel="stylesheet" href="styles/basic.css" type="text/css">
</head>
<body onLoad="window.print();">
<div class="container">
<div>
<p align=center><b><?php print(date('F jS, Y')); ?></b> - <b><?php print($user->getFullname()); ?></b></p>
</div>

<?php

/*
 * Results
 */

$itemStats = new ItemStats($db, $_SESSION['user_id']);

$footer = '<i>Items Done Today: ' . $itemStats->doneToday() . ', Yesterday: ' . $itemStats->doneYesterday();
$footer .= ', This Week: ' . $itemStats->doneThisWeek() . ', Last Week: ' . $itemStats->doneLastWeek();
$footer .= ', This Month: ' . $itemStats->doneThisMonth() . ', Last Month: ' . $itemStats->doneLastMonth();
$footer .= ', Since Start: ' . $itemStats->doneTotal();

$query = "SELECT AVG(IFNULL(TO_DAYS(item.completed) - TO_DAYS(item.created) + 1, TO_DAYS(NOW()) - TO_DAYS(item.created) + 1)) FROM item LEFT JOIN section ON item.section_id = section.id WHERE item.user_id = '" . addslashes($user_id) . "' AND (item.status = 'closed' OR (item.status = 'open' AND section.status = 'active'))";
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
$listDisplay->setInternalPriorityLevels($todo_priority);

$listDisplay->setFilterClosed($display_filter_closed);
$listDisplay->setFilterPriority($display_filter_priority);
$listDisplay->setFilterAging($display_filter_aging);
$listDisplay->setShowEstimate($display_show_estimate);
$listDisplay->setShowInactive($display_show_inactive);
$listDisplay->setShowSection($display_show_section);
$listDisplay->setShowPriority($display_show_priority);

$listDisplay->setPrintable(true);

$listDisplay->setFooter($footer);

print($listDisplay->getOutput());

?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
