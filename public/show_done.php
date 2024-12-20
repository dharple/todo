<?php

use App\Analytics\ItemHistory;
use App\Auth\Guard;
use App\Helper;

$errors = [];

$view = $_REQUEST['view'] ?? 'all';
$sort = $_REQUEST['sort'] ?? 'task';

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

$itemHistory = new ItemHistory($em, $user);
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

    case 'all':
        $items = $itemHistory->doneTotal();
        $period = 'All';
        break;

    default:
        $items = [];
        $period = "UNKNOWN";
        $errors[] = "Invalid selector";
        break;
}

try {
    $twig->display('show_done.html.twig', [
        'errors'   => $errors,
        'items'    => $items,
        'period'   => $period,
        'sort'     => $sort,
        'view'     => $view,
    ]);
} catch (Exception $e) {
    $log->critical($e->getMessage());
    print('An error occurred...');
    exit;
}
