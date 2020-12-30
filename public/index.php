<?php

use App\Analytics\ItemStats;
use App\Entity\Item;
use App\Helper;
use App\Legacy\Renderer\DisplayConfig;
use App\Legacy\Renderer\DisplayHelper;
use App\Legacy\Renderer\ListDisplay;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

$twig = Helper::getTwig();

try {
    $em = Helper::getEntityManager();
    $user = Helper::getUser();
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
    } elseif ($_POST['submitButton'] == 'Edit') {
        if (!empty($_POST['itemIds'])) {
            header('Location: item_edit.php?op=edit&ids=' . urlencode(serialize($_POST['itemIds'])));
            exit;
        }
        $errors[] = 'Please select one or more items to edit';
    } elseif ($_POST['submitButton'] == 'Prioritize') {
        $queryString = '';
        if (!empty($_POST['itemIds'])) {
            $queryString = '?ids=' . urlencode(serialize($_POST['itemIds']));
        }
        header('Location: item_prioritize.php' . $queryString);
        exit;
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
                    ->setCompleted(null)
                    ->setCreated(new DateTime())
                    ->setStatus('Open');

                $em->persist($newItem);
            }
            $em->flush();
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to mark items done: %s', $e->getMessage());
        }
    }
}

$config = new DisplayConfig();
$config
    ->setFilterAging($GLOBALS['display_filter_aging'])
    ->setFilterClosed($GLOBALS['display_filter_closed'])
    ->setFilterPriority($GLOBALS['display_filter_priority'])
    ->setSectionLink('index.php?show_section={SECTION_ID}')
    ->setShowInactive($GLOBALS['display_show_inactive'])
    ->setShowPriority($GLOBALS['display_show_priority'])
    ->setShowSection($GLOBALS['display_show_section']);

$listDisplay = new ListDisplay($user->getId(), $config);

$itemStats = new ItemStats();

try {
    $listDisplay->setFooter($twig->render('partials/index/summary.php.twig', [
        'itemStats' => $itemStats,
    ]));
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}

$listOutput = $listDisplay->getOutput();
$itemCount = $listDisplay->getOutputCount();

$sections = $user->getSections()->matching(
    new Criteria(
        new Comparison('status', '=', 'Active')
    )
);
$sectionCount = count($sections);

try {
    $twig->display('index.html.twig', [
        'config' => $config,
        'errors' => $errors,
        'filterAgingValues' => DisplayHelper::getAgingFilterValues(),
        'filterClosedValues' => DisplayHelper::getClosedFilterValues(),
        'filterPriorityValues' => DisplayHelper::getPriorityFilterValues(),
        'hasItems' => ($itemCount > 0),
        'hasSections' => ($sectionCount > 0),
        'itemStats' => $itemStats,
        'list' => $listOutput,
        'showDuplicate' => ($GLOBALS['display_filter_closed'] != 'none'),
        'showPriorityValues' => DisplayHelper::getShowPriorityDisplay(),
        'user' => $user,
    ]);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
