<?php

use App\Auth\Guard;
use App\Entity\Item;
use App\Entity\Section;
use App\Helper;

$twig = Helper::getTwig();

try {
    $em = Helper::getEntityManager();
    $user = Guard::getUser();
    $sectionRepository = $em->getRepository(Section::class);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}

$errors = [];

if (count($_POST)) {
    $relational_cleanup = [];

    if ($_POST['submitButton'] != '') {
        $ret = true;

        try {
            if ($_POST['submitButton'] == 'Add') {
                $name = trim($_POST['add_name'] ?? '');

                if ($name != '') {
                    $section = (new Section())
                        ->setName($name)
                        ->setStatus('Active')
                        ->setUser($user);

                    $em->persist($section);
                } else {
                    $errors[] = 'Please specify the name of the section to add.';
                }
            } elseif ($_POST['submitButton'] == 'Rename') {
                $name = trim($_POST['edit_name'] ?? '');

                $id = $_POST['edit_section_id'];
                if ($id > 0) {
                    $section = $sectionRepository
                        ->findOneBy([
                            'id' => $id,
                            'user' => $user,
                        ])
                        ->setName($name);
                    $em->persist($section);
                } else {
                    $errors[] = 'Please specify a section to rename.';
                }
            } elseif ($_POST['submitButton'] == 'Activate') {
                $findBy = [
                    'status' => 'Inactive',
                    'user' => $user,
                ];

                if ($_POST['toggle_section_id'] !== 'all') {
                    $findBy['id'] = $_POST['toggle_section_id'];
                }

                $sections = $sectionRepository->findBy($findBy);

                foreach ($sections as $section) {
                    if ($_POST['resetStartTimes'] == 'yes') {
                        $items = $em->getRepository(Item::class)
                            ->findBy([
                                'section' => $section,
                                'status' => 'Open',
                                'user' => $user,
                            ]);
                        foreach ($items as $item) {
                            $item->setCreated(new DateTime());
                            $em->persist($item);
                        }
                    }
                    $section->setStatus('Active');
                    $em->persist($section);
                }
            } elseif ($_POST['submitButton'] == 'Deactivate') {
                $findBy = [
                    'status' => 'Active',
                    'user' => $user,
                ];

                if ($_POST['toggle_section_id'] !== 'all') {
                    $findBy['id'] = $_POST['toggle_section_id'];
                }

                $sections = $sectionRepository->findBy($findBy);

                foreach ($sections as $section) {
                    $section->setStatus('Inactive');
                    $em->persist($section);
                }
            }

            $em->flush();
        } catch (Exception $e) {
            $errors[] = sprintf('Failed to edit items: %s', $e->getMessage());
        }
    }
}

$sections = $sectionRepository
    ->findBy(['user' => $user], ['name' => 'ASC']);

try {
    $twig->display('section_edit.html.twig', [
        'errors' => $errors,
        'sections' => $sections,
    ]);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
