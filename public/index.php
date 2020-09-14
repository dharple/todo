<?php

use App\Legacy\DateUtils;
use App\Legacy\Entity\Item;
use App\Legacy\Entity\Section;
use App\Legacy\ItemStats;
use App\Legacy\ListDisplay;
use App\Legacy\SimpleList;

// Handle POST

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Mark Done') {
        foreach ($_POST['itemIds'] as $itemId) {
            $item = new Item($db, $itemId);
            if ($item->getId() != $itemId) {
                print('Unable to load item #' . $itemId . '<br>');
                continue;
            }

            $item->setStatus('Closed');
            $dateUtils = new DateUtils();
            $item->setCompleted($dateUtils->getNow());
            $ret = $item->save();

            if (!$ret) {
                print('An error occured while updating item #' . $itemId . ' - ' . $item->getTask() . '.<br>');
                print($item->getErrorNumber() . ': ' . $item->getErrorMessage());
                print('<br>');
                print('<hr>');
            }
        }
    } elseif ($_POST['submitButton'] == 'Add New') {
        header('Location: item_edit.php?op=add');
        die();
    } elseif ($_POST['submitButton'] == 'Bulk') {
        header('Location: item_bulk_add.php');
        die();
    } elseif ($_POST['submitButton'] == 'Edit') {
        header('Location: item_edit.php?op=edit&ids=' . urlencode(serialize($_POST['itemIds'])));
        die();
    } elseif ($_POST['submitButton'] == 'Edit Sections') {
        header('Location: section_edit.php');
        die();
    } elseif ($_POST['submitButton'] == 'Logout') {
        session_unset();
        header('Location: login.php');
        die();
    } elseif ($_POST['submitButton'] == 'My Account') {
        header('Location: account.php');
        die();
    } elseif ($_POST['submitButton'] == 'Prioritize') {
        header('Location: item_prioritize.php?ids=' . urlencode(serialize($_POST['itemIds'])));
        die();
    } elseif ($_POST['submitButton'] == 'Duplicate') {
        foreach ($_POST['itemIds'] as $itemId) {
            $item = new Item($db, $itemId);
            if ($item->getId() != $itemId) {
                print('Unable to load item #' . $itemId . '<br>');
                continue;
            }

            $dateUtils = new DateUtils();

            $newItem = new Item($db);

            $newItem->setCreated($dateUtils->getNow());
            $newItem->setUserId($_SESSION['user_id']);
            $newItem->setTask($item->getTask());
            $newItem->setSectionId($item->getSectionId());
            $newItem->setStatus('Open');
            $newItem->setPriority($item->getPriority());
            $ret = $newItem->save();

            if (!$ret) {
                print('An error occured while duplicating item #' . $itemId . ' - ' . $item->getTask() . '.<br>');
                print($newItem->getErrorNumber() . ': ' . $newItem->getErrorMessage());
                print('<br>');
                print('<hr>');
            }
        }
    }
}

// Ugly
$query = "UPDATE item SET created = completed WHERE user_id = '" . addslashes($user_id) . "' AND status = 'Closed' AND (TO_DAYS(completed) - TO_DAYS(created)) < 0";
$result = $db->query($query);

$query = "SELECT AVG(IFNULL(TO_DAYS(item.completed) - TO_DAYS(item.created) + 1, TO_DAYS(NOW()) - TO_DAYS(item.created) + 1)) FROM item LEFT JOIN section ON item.section_id = section.id WHERE item.user_id = '" . addslashes($user_id) . "' AND (item.status = 'closed' OR (item.status = 'open' AND section.status = 'active'))";
$result = $db->query($query);
$row = $db->fetchRow($result);
$avg = $row[0];
//

$listDisplay = new ListDisplay($db, $_SESSION['user_id']);
$listDisplay->setInternalPriorityLevels($todo_priority);

$listDisplay->setFilterClosed($display_filter_closed);
$listDisplay->setFilterPriority($display_filter_priority);
$listDisplay->setFilterAging($display_filter_aging);
$listDisplay->setShowInactive($display_show_inactive);
$listDisplay->setShowSection($display_show_section);
$listDisplay->setSectionLink('index.php?show_section={SECTION_ID}');
$listDisplay->setShowPriority($display_show_priority);

$itemStats = new ItemStats($db, $_SESSION['user_id']);

$listDisplay->setFooter($twig->render('partials/index/summary.php.twig', [
    'avg'       => $avg,
    'itemStats' => $itemStats,
]));

$user_id = $_SESSION['user_id'];

$twig->display('partials/page/header.html.twig', [
    'title' => 'To Do List For ' . date('F jS, Y')
]);

?>

<div class="noprint">
<table width=100%>
<tr>
<td align=left valign=top>
<b>To Do List For <?php print($user->getFullname()); ?></b>
<br>

Items Done:<br>
&nbsp;&nbsp; Today: <a href="show_done.php?view=today">
<?php
print($itemStats->doneToday());
?></a>,
Yesterday: <a href="show_done.php?view=yesterday">
<?php
print($itemStats->doneYesterday());
?></a>
<br>

&nbsp;&nbsp; This Week: <a href="show_done.php?view=week">
<?php
print($itemStats->doneThisWeek());
?></a>,
Last Week: <a href="show_done.php?view=lastweek">
<?php
print($itemStats->doneLastWeek());
?></a>
<br>

&nbsp;&nbsp; This Month: <a href="show_done.php?view=month">
<?php
print($itemStats->doneThisMonth());
?></a>,
Last Month: <a href="show_done.php?view=lastmonth">
<?php
print($itemStats->doneLastMonth());
?></a>
<br>

&nbsp;&nbsp; 3 / 6 / 9 / 12 Months: <a href="show_done.php?view=month3">
<?php
print($itemStats->donePreviousMonths(3));
?></a> /
<a href="show_done.php?view=month6">
<?php
print($itemStats->donePreviousMonths(6));
?></a> /
<a href="show_done.php?view=month9">
<?php
print($itemStats->donePreviousMonths(9));
?></a> /
<a href="show_done.php?view=month12">
<?php
print($itemStats->donePreviousMonths(12));
?></a>
<br>

Items Done Since Start: <a href="show_done.php">
<?php
print($itemStats->doneTotal());
?></a>
<br>

Average Turnaround: <?= number_format($avg, 1) ?> days
<br>
</td>
<td align=right valign=top>
Filter Closed Items:&nbsp;&nbsp;
<?php


foreach ($closed_display as $value => $display) {
    print(' ');
    if ($display_filter_closed == $value) {
        print($display);
    } else {
        print('<a href="index.php?filter_closed=' . $value . '">' . $display . '</a>');
    }
}

?>
<br>

Filter Priority:&nbsp;&nbsp;
<?php

foreach ($priority_display as $value => $display) {
    print(' ');
    if ($display_filter_priority == $value) {
        print($display);
    } else {
        print('<a href="index.php?filter_priority=' . $value . '">' . $display . '</a>');
    }
}

?>
<br>

Show Priority:&nbsp;&nbsp;
<?php

foreach ($show_priority_display as $value => $display) {
    print(' ');
    if ($display_show_priority == $value) {
        print($display);
    } else {
        print('<a href="index.php?show_priority=' . $value . '">' . $display . '</a>');
    }
}

?>
<br>

Filter Aging:&nbsp;&nbsp;
<?php

foreach ($aging_display as $value => $display) {
    print(' ');
    if ($display_filter_aging == $value) {
        print($display);
    } else {
        print('<a href="index.php?filter_aging=' . $value . '">' . $display . '</a>');
    }
}

?>
 days old
<br>

Show Inactive Sections:&nbsp;&nbsp;
<?php

if ($display_show_inactive == 'y') {
    print('Yes <a href="index.php?show_inactive=n">No</a>');
} else {
    print('<a href="index.php?show_inactive=y">Yes</a> No');
}

?>
<br>
Display Settings: <a href="index.php?reset_display_settings=1">Reset</a>

</td>
</tr>
</table>

<hr>

</div>

<div class="print">
<p align=center><b><?php print(date('F jS, Y')); ?></b> - <b><?php print($user->getFullname()); ?></b></p>
</div>

<form method=POST>

<?php

print($listDisplay->getOutput());

$itemCount = $listDisplay->getOutputCount();

$sectionList = new SimpleList($db, Section::class);
$sectionCount = $sectionList->count("WHERE user_id = '" . addslashes($user_id) . "' AND status = 'Active'");

?>

<div class="noprint">
<hr>
<br>

<?php

$twig->display('partials/index/footer.html.twig', [
    'hasItems'      => ($itemCount > 0),
    'hasSections'   => ($sectionCount > 0),
    'showDuplicate' => ($display_filter_closed != 'none'),
]);

?>
</div>

</form>

<?php

$twig->display('partials/page/footer.html.twig');
