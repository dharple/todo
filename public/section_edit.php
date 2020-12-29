<?php

use App\Legacy\DateUtils;
use App\Legacy\Entity\Section;
use App\Legacy\SimpleList;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];
$errors = [];
$section = null;

if (count($_POST)) {
    $relational_cleanup = [];

    if ($_POST['submitButton'] != '') {
        $ret = true;

        if ($_POST['submitButton'] == 'Add') {
            $name = $_POST['add_name'];
            $name = trim($name);

            if ($name != '') {
                $section = new Section($db);
                $section->setName($name);
                $section->setUserId($user->getId());
                $ret = $section->save();
            }
        } elseif ($_POST['submitButton'] == 'Rename') {
            $name = $_POST['edit_name'];
            $name = trim($name);

            $id = $_POST['edit_section_id'];
            if ($id > 0) {
                $section = new Section($db, $id);
                $section->setName($name);
                $ret = $section->save();
            }
        } elseif ($_POST['submitButton'] == 'Activate') {
            $id = $_POST['toggle_section_id'];
            if ($id == 'all') {
                $sectionList = new SimpleList($db, Section::class);
                $sections = $sectionList->load("WHERE user_id = '" . addslashes($user->getId()) . "' AND status = 'Inactive'");
            } else {
                if ($id > 0) {
                    $sections = [new Section($db, $id)];
                } else {
                    // skip the next loop
                    $sections = [];
                }
            }
            foreach ($sections as $section) {
                if ($section->getStatus() == 'Inactive' && $_POST['resetStartTimes'] == 'yes') {
                    $dateUtils = new DateUtils();
                    $now = $dateUtils->getNow();
                    $query = "UPDATE item SET created='" . $now . "' WHERE section_id = '" . $section->getId() . "' AND status = 'Open'";
                    $result = $db->query($query);
                }
                $section->setStatus('Active');
                $ret = $section->save();
                if (!$ret) {
                    break;
                }
            }
        } elseif ($_POST['submitButton'] == 'Deactivate') {
            $id = $_POST['toggle_section_id'];
            if ($id == 'all') {
                $sectionList = new SimpleList($db, Section::class);
                $sections = $sectionList->load("WHERE user_id = '" . addslashes($user->getId()) . "' AND status = 'Active'");
            } else {
                if ($id > 0) {
                    $sections = [new Section($db, $id)];
                } else {
                    // skip the next loop
                    $sections = [];
                }
            }
            foreach ($sections as $section) {
                $section->setStatus('Inactive');
                $ret = $section->save();
                if (!$ret) {
                    break;
                }
            }
        }

        if (!$ret) {
            $errors[] = sprintf(
                'An error occured while updating your section.  %s: %s',
                isset($section) ? $section->getErrorNumber() : 'unknown',
                isset($section) ? $section->getErrorMessage() : 'unknown'
            );
        }
    }
}

$sectionList = new SimpleList($db, Section::class);
$sections = $sectionList->load("WHERE user_id = '" . addslashes($user->getId()) . "' ORDER BY name");

$twig->display('section_edit.html.twig', [
    'sections' => $sections,
    'errors'   => $errors,
]);
