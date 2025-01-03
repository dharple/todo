<?php

use App\Auth\Guard;
use App\Entity\Item;
use App\Helper;
use App\Renderer\DisplayHelper;
use App\Renderer\ListDisplay;

$priorityLevels = DisplayHelper::getPriorityLevels();

try {
    $log = Helper::getLogger();
} catch (Exception $e) {
    print('An error occurred...');
    exit;
}

try {
    $em = Helper::getEntityManager();
    $twig = Helper::getTwig();
    $user = Guard::getUser();
} catch (Exception $e) {
    $log->critical($e->getMessage());
    print('An error occurred...');
    exit;
}

$errors = [];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Update') {
        try {
            foreach ($_POST['itemPriority'] as $itemId => $priority) {
                $item = $em->find(Item::class, $itemId);

                if ($item === null) {
                    $errors[] = sprintf('Unable to load item #%s', $itemId);
                    continue;
                }

                if ($priority < $priorityLevels['high']) {
                    $priority = $priorityLevels['high'];
                } elseif ($priority > $priorityLevels['low']) {
                    $priority = $priorityLevels['low'];
                }

                $item->setPriority($priority);
                $em->persist($item);
            }
            $em->flush();
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to change priorities: %s', $e->getMessage());
        }
    }

    if (empty($errors)) {
        header('Location: index.php');
        exit();
    }
}

// don't affect the user's config
$config = clone Helper::getDisplayConfig();

try {
    $config->setFilterClosed('none');
} catch (Exception $e) {
    $errors[] = 'Could not disable closed filter for this view.';
}

try {
    $config->setFilterDeleted('none');
} catch (Exception $e) {
    $errors[] = 'Could not disable deleted filter for this view.';
}

$config->setShowPriorityEditor(true);

if (isset($_REQUEST['ids'])) {
    $ids = $_REQUEST['ids'];
    if (is_array($ids) && count($ids)) {
        $config->setFilterIds($ids);
    }
}

$listDisplay = new ListDisplay($config, $em, $log, $twig, $user);
$listOutput = $listDisplay->getOutput();
$itemCount = $listDisplay->getOutputCount()->getOpenCount();

try {
    $twig->display('item_prioritize.html.twig', [
        'hasItems' => ($itemCount > 0),
        'errors' => $errors,
        'list' => $listOutput,
    ]);
} catch (Exception $e) {
    $log->critical($e->getMessage());
    print('An error occurred...');
    exit;
}
