<?php

use App\Analytics\ItemStats;
use App\Auth\Guard;
use App\Entity\Item;
use App\Helper;
use App\Renderer\DisplayHelper;
use App\Renderer\ListDisplay;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

try {
    $log = Helper::getLogger();
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

try {
    $em = Helper::getEntityManager();
    $twig = Helper::getTwig();
    $user = Guard::getUser();
} catch (Exception $e) {
    $log->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}

// Handle POST

$errors = [];

if (count($_POST)) {
    if (!empty($_POST['markDoneButton'])) {
        try {
            foreach ($_POST['itemIds'] as $itemId) {
                $item = $em->find(Item::class, $itemId);

                if ($item === null) {
                    $errors[] = sprintf('Unable to load item #%s', $itemId);
                    continue;
                }

                $item
                    ->setStatus(($_POST['markDoneButton'] != 'Delete') ? 'Closed' : 'Deleted')
                    ->setCompleted(new DateTime(($_POST['markDoneButton'] == 'Mark Done Yesterday') ? 'yesterday 23:45' : null));
                $em->persist($item);
            }
            $em->flush();
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to mark items done: %s', $e->getMessage());
        }
    } elseif (!empty($_POST['editButton'])) {
        if (!empty($_POST['itemIds'])) {
            $url = sprintf('item_edit.php?%s', http_build_query([
                'op' => 'edit',
                'ids' => $_POST['itemIds'],
            ]));
            header(sprintf('Location: %s', $url));
            exit;
        }
        $errors[] = 'Please select one or more items to edit';
    } elseif (!empty($_POST['prioritizeButton'])) {
        $url = 'item_prioritize.php';
        if (!empty($_POST['itemIds'])) {
            $url = sprintf('%s?%s', $url, http_build_query([
                'ids' => $_POST['itemIds'],
            ]));
        }
        header(sprintf('Location: %s', $url));
        exit;
    } elseif (!empty($_POST['duplicateButton'])) {
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

$config = Helper::getDisplayConfig();

try {
    $config->processRequest();
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

$listDisplay = new ListDisplay($config, $em, $log, $twig, $user);

$itemStats = new ItemStats($em, $user);

try {
    $listDisplay->setFooter($twig->render('partials/index/summary.php.twig', [
        'itemStats' => $itemStats,
    ]));
} catch (Exception $e) {
    $log->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}

$listOutput = $listDisplay->getOutput();
$itemCount = $listDisplay->getOutputCount()->getTotalCount();
$shownOpenCount = $listDisplay->getOutputCount()->getOpenCount();
$totalOpenCount = $em->getRepository(Item::class)->getOpenItemCount($user);

// if nothing has been closed, and nothing has been hidden, don't show the
// footer on the printed view
$hideFooter = (($shownOpenCount == $totalOpenCount) && ($itemStats->doneTotal() == 0));
if ($hideFooter) {
    $listDisplay->setFooter('');
    $listOutput = $listDisplay->getOutput();
}

$sections = $user->getSections()->matching(
    new Criteria(
        new Comparison('status', '=', 'Active')
    )
);
$sectionCount = count($sections);

try {
    $twig->display('index.html.twig', [
        'chartData' => $itemStats->getWeeklySummary(4),
        'config' => $config,
        'countOpen' => $totalOpenCount,
        'countShown' => $shownOpenCount,
        'errors' => $errors,
        'filterAgingValues' => DisplayHelper::getFilterAgingValues(),
        'filterClosedValues' => DisplayHelper::getFilterClosedValues(),
        'filterDeletedValues' => DisplayHelper::getFilterDeletedValues(),
        'filterFreshnessValues' => DisplayHelper::getFilterFreshnessValues(),
        'filterPriorityValues' => DisplayHelper::getFilterPriorityValues(),
        'hasItems' => ($itemCount > 0),
        'hasSections' => ($sectionCount > 0),
        'itemStats' => $itemStats,
        'list' => $listOutput,
        'showAdvanced' => ($config->getFilterClosed() != 'none' || $config->getFilterDeleted() != 'none'),
        'showPriorityValues' => DisplayHelper::getShowPriorityValues(),
        'user' => $user,
    ]);
} catch (Exception $e) {
    $log->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
