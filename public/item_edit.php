<?php

use App\Helper;
use App\Entity\Section;
use App\Legacy\DateUtils;
use App\Legacy\Entity\Item;
use App\Legacy\SimpleList;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];

try {
    $em = Helper::getEntityManager();
    $user = Helper::getUser();
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}

if (count($_POST)) {
    $dateUtils = new DateUtils();

    if (!is_array($_POST['task'])) {
        $_POST['task'] = [];
    }

    foreach ($_POST['task'] as $itemId => $task) {
        if ($itemId == 'new') {
            $item = new Item($db);
            $item->setCreated($dateUtils->getNow());
            $item->setUserId($user->getId());
        } else {
            $item = new Item($db, $itemId);
            $item->setCompleted($_POST['completed'][$itemId]);
        }
        $item->setTask($task);
        $item->setSectionId($_POST['section'][$itemId]);
        $item->setStatus($_POST['status'][$itemId]);
        $item->setPriority($_POST['priority'][$itemId]);
        $item->save();
    }

    if ($_REQUEST['submitButton'] == 'Do It') {
        header('Location: index.php');
        die();
    }
}

$qb = $em->getRepository(Section::class)
    ->createQueryBuilder('s')
    ->where('s.user = :user')
    ->orderBy('s.name')
    ->setParameter('user', $user);
$sections = $qb->getQuery()->getResult();

$items = [];

if ($_REQUEST['op'] == 'edit') {
    $itemList = new SimpleList($db, Item::class);
    $items = $itemList->load("WHERE user_id = '" . addslashes((string) $user->getId()) . "' AND id IN ('" . implode("','", unserialize($_REQUEST['ids'])) . "')");
} elseif ($_REQUEST['op'] == 'add') {
    $item = new Item($db);
    $item->setStatus('Open');
    $item->setPriority($GLOBALS['todo_priority']['normal']);

    $items = [ $item ];
}

$twig->display('item_edit.html.twig', [
    'ids'           => $_REQUEST['ids'] ?? '',
    'items'         => $items,
    'op'            => $_REQUEST['op'],
    'sections'      => $sections,
    'statuses'      => ['Open', 'Closed', 'Deleted'],
    'todo_priority' => $GLOBALS['todo_priority'],
]);
