<?php

use App\Entity\Item;
use App\Helper;
use App\Legacy\Entity\Section;
use App\Legacy\ItemStats;
use App\Legacy\Renderer\DisplayConfig;
use App\Legacy\Renderer\ListDisplay;
use App\Legacy\SimpleList;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

try {
    $em = Helper::getEntityManager();
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}

// Handle POST

$errors = [];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Mark Done') {
        try {
            foreach ($_POST['itemIds'] as $itemId) {
                $item = $em->find(Item::class, $itemId);

                if ($item === null) {
                    $errors[] = sprintf('Unable to load item #%s', $itemId);
                    continue;
                }

                $item
                    ->setStatus('Closed')
                    ->setCompleted(new DateTime());
                $em->persist($item);
            }
            $em->flush();
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to mark items done: %s', $e->getMessage());
        }
    } elseif ($_POST['submitButton'] == 'Add New') {
        header('Location: item_edit.php?op=add');
        die();
    } elseif ($_POST['submitButton'] == 'Bulk') {
        header('Location: item_bulk_add.php');
        die();
    } elseif ($_POST['submitButton'] == 'Edit') {
        if (!empty($_POST['itemIds'])) {
            header('Location: item_edit.php?op=edit&ids=' . urlencode(serialize($_POST['itemIds'])));
            die();
        }
        $errors[] = 'Please select one or more items to edit';
    } elseif ($_POST['submitButton'] == 'Edit Sections') {
        header('Location: section_edit.php');
        die();
    } elseif ($_POST['submitButton'] == 'Logout') {
        header('Location: logout.php');
        die();
    } elseif ($_POST['submitButton'] == 'My Account') {
        header('Location: account.php');
        die();
    } elseif ($_POST['submitButton'] == 'Prioritize') {
        $queryString = '';
        if (!empty($_POST['itemIds'])) {
            $queryString = '?ids=' . urlencode(serialize($_POST['itemIds']));
        }
        header('Location: item_prioritize.php' . $queryString);
        die();
    } elseif ($_POST['submitButton'] == 'Duplicate') {
        try {
            foreach ($_POST['itemIds'] as $itemId) {
                $item = $em->find(Item::class, $itemId);

                if ($item === null) {
                    $errors[] = sprintf('Unable to load item #%s', $itemId);
                    continue;
                }

                $newItem = clone $item;
                $newItem
                    ->setStatus('Open')
                    ->setCompleted(null);

                $em->persist($newItem);
            }
            $em->flush();
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to mark items done: %s', $e->getMessage());
        }
    }
}

// Ugly
$query = "UPDATE item SET created = completed WHERE user_id = '" . addslashes($user->getId()) . "' AND status = 'Closed' AND (TO_DAYS(completed) - TO_DAYS(created)) < 0";
$result = $db->query($query);

$query = "SELECT AVG(IFNULL(TO_DAYS(item.completed) - TO_DAYS(item.created) + 1, TO_DAYS(NOW()) - TO_DAYS(item.created) + 1)) FROM item LEFT JOIN section ON item.section_id = section.id WHERE item.user_id = '" . addslashes($user->getId()) . "' AND (item.status = 'closed' OR (item.status = 'open' AND section.status = 'active'))";
$result = $db->query($query);
$row = $db->fetchRow($result);
$avg = $row[0];
// End Ugly

$config = new DisplayConfig();
$config
    ->setFilterAging($GLOBALS['display_filter_aging'])
    ->setFilterClosed($GLOBALS['display_filter_closed'])
    ->setFilterPriority($GLOBALS['display_filter_priority'])
    ->setInternalPriorityLevels($GLOBALS['todo_priority'])
    ->setSectionLink('index.php?show_section={SECTION_ID}')
    ->setShowInactive($GLOBALS['display_show_inactive'])
    ->setShowPriority($GLOBALS['display_show_priority'])
    ->setShowSection($GLOBALS['display_show_section']);

$listDisplay = new ListDisplay($user->getId(), $config);

$itemStats = new ItemStats($db, $user->getId());

$listDisplay->setFooter($twig->render('partials/index/summary.php.twig', [
    'avg'       => $avg,
    'itemStats' => $itemStats,
]));

$listOutput = $listDisplay->getOutput();
$itemCount = $listDisplay->getOutputCount();

$sectionList = new SimpleList($db, Section::class);
$sectionCount = $sectionList->count("WHERE user_id = '" . addslashes($user->getId()) . "' AND status = 'Active'");

$twig->display('index.html.twig', [
    'avg'                    => $avg,
    'config'                 => $config,
    'errors'                 => $errors,
    'filterAgingValues'      => $GLOBALS['aging_display'],
    'filterClosedValues'     => $GLOBALS['closed_display'],
    'filterPriorityValues'   => $GLOBALS['priority_display'],
    'hasItems'               => ($itemCount > 0),
    'hasSections'            => ($sectionCount > 0),
    'itemStats'              => $itemStats,
    'list'                   => $listOutput,
    'showDuplicate'          => ($GLOBALS['display_filter_closed'] != 'none'),
    'showPriorityValues'     => $GLOBALS['show_priority_display'],
    'user'                   => $user,
]);
