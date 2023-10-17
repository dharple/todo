<?php

use App\Auth\Guard;
use App\Entity\Item;
use App\Entity\Section;
use App\Helper;
use App\Renderer\DisplayHelper;

$priorityLevels = DisplayHelper::getPriorityLevels();

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

$errors = [];

if (count($_POST)) {
    try {
        $tasks = preg_split("/[\r\n]/", (string) $_POST['tasks']);
        foreach ($tasks as $task) {
            $task = trim((string) $task);
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

$selected = DisplayHelper::getDefaultSectionId($em, $user);

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
    $log->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
