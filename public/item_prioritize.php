<?php

use App\Auth\Guard;
use App\Entity\Item;
use App\Helper;
use App\Renderer\DisplayHelper;
use App\Legacy\Renderer\ListDisplay;

$twig = Helper::getTwig();
$priorityLevels = DisplayHelper::getPriorityLevels();

try {
    $em = Helper::getEntityManager();
    $user = Guard::getUser();
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
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

$config->setShowPriorityEditor(true);

$ids = unserialize($_REQUEST['ids']);
if (is_array($ids) && count($ids)) {
    $config->setFilterIds($ids);
}

$listDisplay = new ListDisplay($user->getId(), $config);
$listOutput = $listDisplay->getOutput();
$itemCount = $listDisplay->getOutputCount();

try {
    $twig->display('item_prioritize.html.twig', [
        'hasItems' => ($itemCount > 0),
        'errors' => $errors,
        'ids' => $_REQUEST['ids'],
        'list' => $listOutput,
    ]);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
