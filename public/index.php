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
$itemCount = $listDisplay->getOutputCount();

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
        'errors' => $errors,
        'filterAgingValues' => DisplayHelper::getFilterAgingValues(),
        'filterClosedValues' => DisplayHelper::getFilterClosedValues(),
        'filterPriorityValues' => DisplayHelper::getFilterPriorityValues(),
        'hasItems' => ($itemCount > 0),
        'hasSections' => ($sectionCount > 0),
        'itemStats' => $itemStats,
        'list' => $listOutput,
        'showDuplicate' => ($config->getFilterClosed() != 'none'),
        'showPriorityValues' => DisplayHelper::getShowPriorityValues(),
        'user' => $user,
    ]);
} catch (Exception $e) {
    $log->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
