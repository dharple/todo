<?php

use App\Entity\Item;
use App\Helper;
use App\Legacy\Renderer\DisplayConfig;
use App\Legacy\Renderer\ListDisplay;

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

                if ($priority < $GLOBALS['todo_priority']['high']) {
                    $priority = $GLOBALS['todo_priority']['high'];
                } elseif ($priority > $GLOBALS['todo_priority']['low']) {
                    $priority = $GLOBALS['todo_priority']['low'];
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

$config = new DisplayConfig();
$config
    ->setFilterAging($GLOBALS['display_filter_aging'])
    ->setFilterClosed($GLOBALS['display_filter_closed'])
    ->setFilterPriority($GLOBALS['display_filter_priority'])
    ->setInternalPriorityLevels($GLOBALS['todo_priority'])
    ->setShowInactive($GLOBALS['display_show_inactive'])
    ->setShowPriorityEditor('y')
    ->setShowSection($GLOBALS['display_show_section']);

$ids = unserialize($_REQUEST['ids']);
if (is_array($ids) && count($ids)) {
    $config->setIds($ids);
} else {
    try {
        $qb = $em->createQueryBuilder()
            ->select('i.id')
            ->from(Item::class, 'i')
            ->where('i.status = :status')
            ->andWhere('i.user = :user')
            ->setParameter('status', 'Open')
            ->setParameter('user', $user->getId());
        $ids = $qb->getQuery()->getScalarResult();
        if (count($ids) > 0) {
            $config->setIds($ids);
        }
    } catch (Exception $e) {
        Helper::getLogger()->critical($e->getMessage());
        echo $e->getMessage();
        exit;
    }
}

$listDisplay = new ListDisplay($user->getId(), $config);
$listOutput = $listDisplay->getOutput();
$itemCount = $listDisplay->getOutputCount();

$twig->display('item_prioritize.html.twig', [
    'hasItems' => ($itemCount > 0),
    'errors'   => $errors,
    'ids'      => $_REQUEST['ids'],
    'list'     => $listOutput,
]);
