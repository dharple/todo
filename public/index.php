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

$listOutput = $listDisplay->getOutput();
$itemCount = $listDisplay->getOutputCount();

$sectionList = new SimpleList($db, Section::class);
$sectionCount = $sectionList->count("WHERE user_id = '" . addslashes($user_id) . "' AND status = 'Active'");

$twig->display('index.html.twig', [
    'avg'                    => $avg,
    'filterAgingSelected'    => $display_filter_aging,
    'filterAgingValues'      => $aging_display,
    'filterClosedSelected'   => $display_filter_closed,
    'filterClosedValues'     => $closed_display,
    'filterPrioritySelected' => $display_filter_priority,
    'filterPriorityValues'   => $priority_display,
    'hasItems'               => ($itemCount > 0),
    'hasSections'            => ($sectionCount > 0),
    'itemStats'              => $itemStats, 
    'list'                   => $listOutput,
    'showDuplicate'          => ($display_filter_closed != 'none'),
    'showInactiveSelected'   => $display_show_inactive,
    'showPrioritySelected'   => $display_show_priority,
    'showPriorityValues'     => $show_priority_display,
    'user'                   => $user,
]);
