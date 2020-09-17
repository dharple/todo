<?php

use App\Legacy\SimpleList;
use App\Legacy\DateUtils;
use App\Legacy\ItemHistory;
use App\Legacy\Entity\Section;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

$view = $_REQUEST['view'] ?? '';
$sort = $_REQUEST['sort'] ?? 'task';

$itemHistory = new ItemHistory($db, $user->getId());
if ($sort == 'section') {
    $itemHistory->setOrdering('section');
}

switch ($view) {
    case 'month':
        $items = $itemHistory->doneThisMonth();
        $period = 'This Month';
        break;

    case 'lastmonth':
        $items = $itemHistory->doneLastMonth();
        $period = 'Last Month';
        break;

    case 'week':
        $items = $itemHistory->doneThisWeek();
        $period = 'This Week';
        break;

    case 'lastweek':
        $items = $itemHistory->doneLastWeek();
        $period = 'Last Week';
        break;

    case 'yesterday':
        $items = $itemHistory->doneYesterday();
        $period = 'Yesterday';
        break;

    case 'today':
        $items = $itemHistory->doneToday();
        $period = 'Today';
        break;

    case 'month3':
        $items = $itemHistory->donePreviousMonths(3);
        $period = 'Past 3 Months';
        break;

    case 'month6':
        $items = $itemHistory->donePreviousMonths(6);
        $period = 'Past 6 Months';
        break;

    case 'month9':
        $items = $itemHistory->donePreviousMonths(9);
        $period = 'Past 9 Months';
        break;

    case 'month12':
        $items = $itemHistory->donePreviousMonths(12);
        $period = 'Past 12 Months';
        break;

    default:
        $items = $itemHistory->doneTotal();
        $period = 'All';
        break;
}

$sectionList = new SimpleList($db, Section::class);
$sections = $sectionList->load("WHERE user_id = '" . addslashes($user->getId()) . "'");
$sectionsById = [];
foreach ($sections as $section) {
    $sectionsById[$section->getId()] = $section->getName();
}
unset($sections);

$dateUtils = new DateUtils();

$twig->display('show_done.html.twig', [
    'items'    => $items,
    'period'   => $period,
    'sections' => $sectionsById,
    'sort'     => $sort,
    'view'     => $view,
]);

