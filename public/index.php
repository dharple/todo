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
    } elseif ($_POST['submitButton'] == 'Estimate') {
        header('Location: item_estimate.php?ids=' . urlencode(serialize($_POST['itemIds'])));
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

$listDisplay = new ListDisplay($db, $_SESSION['user_id']);
$listDisplay->setInternalPriorityLevels($todo_priority);

$listDisplay->setFilterClosed($display_filter_closed);
$listDisplay->setFilterPriority($display_filter_priority);
$listDisplay->setFilterAging($display_filter_aging);
$listDisplay->setShowEstimate($display_show_estimate);
$listDisplay->setShowInactive($display_show_inactive);
$listDisplay->setShowSection($display_show_section);
$listDisplay->setSectionLink('index.php?show_section={SECTION_ID}');
$listDisplay->setShowPriority($display_show_priority);

$itemStats = new ItemStats($db, $_SESSION['user_id']);

$user_id = $_SESSION['user_id'];

?>
<html>
<head>
<title>To Do List For <?php print(date('F jS, Y')); ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<link rel="stylesheet" href="styles/basic.css" type="text/css">
</head>
<body>

<div class="container">

<div>
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

Average Turnaround: <?php

/* Ugly */
$query = "UPDATE item SET created = completed WHERE user_id = '" . addslashes($user_id) . "' AND status = 'Closed' AND (TO_DAYS(completed) - TO_DAYS(created)) < 0";
$result = $db->query($query);

$query = "SELECT AVG(IFNULL(TO_DAYS(item.completed) - TO_DAYS(item.created) + 1, TO_DAYS(NOW()) - TO_DAYS(item.created) + 1)) FROM item LEFT JOIN section ON item.section_id = section.id WHERE item.user_id = '" . addslashes($user_id) . "' AND (item.status = 'closed' OR (item.status = 'open' AND section.status = 'active'))";
$result = $db->query($query);
$row = $db->fetchRow($result);
print(number_format($row[0], 1));

?> days
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

Show Estimates:&nbsp;&nbsp;
<?php

if ($display_show_estimate == 'y') {
    print('Yes <a href="index.php?show_estimate=n">No</a>');
} else {
    print('<a href="index.php?show_estimate=y">Yes</a> No');
}

?>
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
<br>

<?php
    print("<a href=\"\" onClick=\"window.open('printable.php','todoList','height=400,width=600'); return false;\">Printable Version</a>");
    print('<br>');
    print('<a href="export_menu.php">Exportable Version</a>');
?>

</td>
</tr>
</table>
</div>

<br>

<hr>
<br>

<form method=POST>

<?php

print($listDisplay->getOutput());

$itemCount = $listDisplay->getOutputCount();

$sectionList = new SimpleList($db, Section::class);
$sectionCount = $sectionList->count("WHERE user_id = '" . addslashes($user_id) . "' AND status = 'Active'");

?>

<hr>
<br>

<table width="100%" cellpadding=0 cellspacing=0 border=0>
    <tr>
        <td align=left>
            <input type="submit" name="submitButton" value="Edit" <?php if ($itemCount == 0) {
                print('disabled') ;
                                                                  }?>>
            <input type="submit" name="submitButton" value="Mark Done" <?php if ($itemCount == 0) {
                print('disabled') ;
                                                                       }?>>
            <input type="submit" name="submitButton" value="Estimate" <?php if ($itemCount == 0) {
                print('disabled') ;
                                                                      }?>>
            <input type="submit" name="submitButton" value="Prioritize" <?php if ($itemCount == 0) {
                print('disabled') ;
                                                                        }?>>
            <?php if ($display_filter_closed != 'none') {
                ?> <input type="submit" name="submitButton" value="Duplicate" <?php if ($itemCount == 0) {
    print('disabled') ;
                }?>> <?php
            } ?>
        </td>
        <td align=center>
            <input type="submit" name="submitButton" value="My Account">
            <input type="submit" name="submitButton" value="Logout">
        </td>
        <td align=right>
            <input type="submit" name="submitButton" value="Bulk" <?php if ($sectionCount == 0) {
                print('disabled') ;
                                                                  }?>>
            <input type="submit" name="submitButton" value="Add New" <?php if ($sectionCount == 0) {
                print('disabled') ;
                                                                     }?>>
            <input type="submit" name="submitButton" value="Edit Sections">
        </td>
    </tr>
</table>

</form>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

</body>
</html>
