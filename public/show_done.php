<?php

use App\Helper;
use App\Analytics\ItemHistory;

$twig = Helper::getTwig();

$view = $_REQUEST['view'] ?? '';
$sort = $_REQUEST['sort'] ?? 'task';

$itemHistory = new ItemHistory();
if ($sort == 'section') {
    $itemHistory->setOrdering('section');
}

switch ($view) {
    case 'month':
        $items = $itemHistory->doneThisMonth();
        $period = 'This Month';
        break;

    case 'last-month':
        $items = $itemHistory->doneLastMonth();
        $period = 'Last Month';
        break;

    case 'week':
        $items = $itemHistory->doneThisWeek();
        $period = 'This Week';
        break;

    case 'last-week':
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

try {
    $twig->display('show_done.html.twig', [
        'items'    => $items,
        'period'   => $period,
        'sort'     => $sort,
        'view'     => $view,
    ]);
} catch (Exception $e) {
    Helper::getLogger()->critical($e->getMessage());
    echo $e->getMessage();
    exit;
}
