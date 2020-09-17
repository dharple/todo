<?php

use App\Legacy\Entity\Item;
use App\Legacy\ListDisplay;
use App\Legacy\SimpleList;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

$errors = [];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Update') {
        foreach ($_POST['itemPriority'] as $itemId => $priority) {
            $item = new Item($db, $itemId);
            if ($item->getId() != $itemId) {
                $errors[] = sprintf('Unable to load item #%s', $itemId);
                continue;
            }

            if ($priority < $GLOBALS['todo_priority']['high']) {
                $priority = $GLOBALS['todo_priority']['high'];
            } elseif ($priority > $GLOBALS['todo_priority']['low']) {
                $priority = $GLOBALS['todo_priority']['low'];
            }

            $item->setPriority($priority);
            $ret = $item->save();

            if (!$ret) {
                $errors[] = sprintf(
                    'An error occured while updating item #%s - %s.  %s: %s',
                    $itemId,
                    $item->getTask(),
                    $item->getErrorNumber(),
                    $item->getErrorMessage()
                );
            }
        }
    }

    if (empty($errors)) {
        header('Location: index.php');
        exit();
    }
}

$listDisplay = new ListDisplay($db, $user->getId());
$listDisplay->setInternalPriorityLevels($GLOBALS['todo_priority']);
$listDisplay->setShowPriorityEditor('y');

$listDisplay->setFilterClosed($GLOBALS['display_filter_closed']);
$listDisplay->setFilterPriority($GLOBALS['display_filter_priority']);
$listDisplay->setFilterAging($GLOBALS['display_filter_aging']);
$listDisplay->setShowInactive($GLOBALS['display_show_inactive']);
$listDisplay->setShowSection($GLOBALS['display_show_section']);

$ids = unserialize($_REQUEST['ids']);
if (is_array($ids) && count($ids)) {
    $listDisplay->setIds($ids);
} else {
    $itemList = new SimpleList($db, Item::class);
    $items = $itemList->load("WHERE user_id = '" . addslashes($user->getId()) . "' AND status = 'Open'");
    $ids = [];
    foreach ($items as $item) {
        array_push($ids, $item->getId());
    }
    if (count($ids) > 0) {
        $listDisplay->setIds($ids);
    }
}

$listOutput = $listDisplay->getOutput();

$itemCount = $listDisplay->getOutputCount();

$twig->display('item_prioritize.html.twig', [
    'hasItems' => ($itemCount > 0),
    'errors'   => $errors,
    'ids'      => $_REQUEST['ids'],
    'list'     => $listOutput,
]);
