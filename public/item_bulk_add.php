<?php

use App\Auth\Guard;
use App\Entity\Item;
use App\Entity\Section;
use App\Helper;
use App\Legacy\Renderer\DisplayHelper;

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
    try {
        $tasks = preg_split("/[\r\n]/", $_POST['tasks']);
        foreach ($tasks as $task) {
            $task = trim($task);
            if ($task == '') {
                continue;
            }

            $item = (new Item())
                ->setCreated(new DateTime())
                ->setPriority($_POST['priority'])
                ->setSection($em->getReference(Section::class, $_POST['section']))
                ->setStatus('Open')
                ->setTask($task)
                ->setUser($user);

            $em->persist($item);
        }

        $em->flush();
    } catch (Exception $e) {
        $errors[] = sprintf('Failed to add items: %s', $e->getMessage());
    }

    if (empty($errors) && $_REQUEST['submitButton'] == 'Do It') {
        header('Location: index.php');
        exit;
    }
}

$selected = $em->getRepository(Item::class)
    ->findOneBy([
        'status' => ['Open', 'Closed'],
        'user' => $user,
    ], [
        'id' => 'DESC'
    ])
    ->getSection()
    ->getId();

$sections = $em->getRepository(Section::class)
    ->findBy(['user' => $user], ['name' => 'ASC']);

try {
    $twig->display('item_bulk_add.html.twig', [
        'errors' => $errors,
        'priorityLevels' => $priorityLevels,
        'sections' => $sections,
        'selectedPriority' => $priorityLevels['normal'],
        'selectedSection' => $selected,
    ]);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
