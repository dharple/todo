<?php

use App\Entity\Section;
use App\Helper;
use App\Legacy\DateUtils;
use App\Legacy\Entity\Item;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

$entityManager = Helper::getEntityManager();

if (count($_POST)) {
    $dateUtils = new DateUtils();

    $tasks = preg_split("/[\r\n]/", $_POST['tasks']);
    foreach ($tasks as $task) {
        $task = trim($task);
        if ($task == '') {
            continue;
        }

        $item = new Item($db);

        $item->setCreated($dateUtils->getNow());
        $item->setUserId($user->getId());
        $item->setTask($task);
        $item->setSectionId($_POST['section']);
        $item->setStatus('Open');
        $item->setPriority($_POST['priority']);
        $item->save();
    }

    if ($_REQUEST['submitButton'] == 'Do It') {
        header('Location: index.php');
        die();
    }
}

$query = "SELECT section_id FROM item WHERE user_id = '" . addslashes($user->getId()) . "' AND status != 'Deleted' ORDER BY created DESC LIMIT 1";
$result = $db->query($query);
$row = $db->fetchRow($result);
$selected = $row[0];

$sectionRepository = $entityManager->getRepository(Section::class);
$qb = $sectionRepository->createQueryBuilder('s')
    ->where('s.user = :user')
    ->orderBy('s.name')
    ->setParameter('user', $user->getId());
$sections = $qb->getQuery()->getResult();

$twig->display('item_bulk_add.html.twig', [
    'sections'         => $sections,
    'selectedPriority' => $GLOBALS['todo_priority']['normal'],
    'selectedSection'  => $selected,
    'todo_priority'    => $GLOBALS['todo_priority'],
]);
