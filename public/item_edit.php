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
    if (!is_array($_POST['task'])) {
        $_POST['task'] = [];
    }

    try {
        $itemIds = array_keys($_POST['task']);
        $items = [];
        if (count($itemIds) === 1 && $itemIds[0] === 'new') {
            $items[] = (new Item())
                ->setCreated(new DateTime())
                ->setUser($user)
                ->setStatus('Open');
        } else {
            $items = $em->getRepository(Item::class)
                ->findBy([
                    'id' => $itemIds,
                    'user' => $user,
                ]);
        }

        foreach ($items as $item) {
            $itemId = $item->getId() ?? 'new';

            if (!array_key_exists($itemId, $_POST['task'])) {
                throw new Exception(sprintf('Could not find item #%s', $itemId));
            }

            if ($itemId !== 'new') {
                $item->setStatus($_POST['status'][$itemId]);
                switch($_POST['status'][$itemId]) {
                    case 'Open':
                        $item->setCompleted(null);
                        break;

                    case 'Closed':
                    case 'Deleted':
                        $item->setCompleted(new DateTime($_POST['completed'][$itemId]));
                        break;
                }
            }

            $item
                ->setPriority($_POST['priority'][$itemId])
                ->setSection($em->getReference(Section::class, $_POST['section'][$itemId]))
                ->setTask($_POST['task'][$itemId]);

            $em->persist($item);
        }

        $em->flush();
    } catch (Exception $e) {
        $errors[] = sprintf('Failed to edit items: %s', $e->getMessage());
    }

    if (empty($errors) && $_REQUEST['submitButton'] == 'Do It') {
        header('Location: index.php');
        exit;
    }
}

$sections = $em->getRepository(Section::class)
    ->findBy(['user' => $user], ['name' => 'ASC']);

$items = [];

$sectionOverride = null;
if ($_REQUEST['op'] == 'edit') {
    $items = $em->getRepository(Item::class)
        ->findBy([
            'id' => $_REQUEST['ids'],
            'user' => $user,
        ]);
} elseif ($_REQUEST['op'] == 'add') {
    $items = [
        (new Item())
            ->setPriority($priorityLevels['normal'])
            ->setStatus('Open'),
    ];
    $sectionOverride = DisplayHelper::getDefaultSectionId($em, $user);
}

try {
    $twig->display('item_edit.html.twig', [
        'errors' => $errors,
        'ids' => $_REQUEST['ids'] ?? '',
        'items' => $items,
        'op' => $_REQUEST['op'],
        'priorityLevels' => $priorityLevels,
        'sections' => $sections,
        'sectionOverride' => $sectionOverride,
        'statuses' => ['Open', 'Closed', 'Deleted'],
    ]);
} catch (Exception $e) {
    $log->critical($e->getMessage());
    print('An error occurred...');
    exit;
}
