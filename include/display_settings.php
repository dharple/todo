<?php

if (!isset($_SESSION['user_id'])) {
    return;
}

// Reset To Defaults //

if (isset($_REQUEST['reset_display_settings'])) {
    unset($_SESSION['filter_closed']);
    unset($_SESSION['filter_priority']);
    unset($_SESSION['filter_aging']);
    unset($_SESSION['show_inactive']);
    unset($_SESSION['num_columns']);
    unset($_SESSION['show_section']);
    unset($_SESSION['show_priority']);
}

// Defaults //

$GLOBALS['display_filter_closed'] = 'none';
$GLOBALS['display_filter_aging'] = 'all';
$GLOBALS['display_filter_priority'] = 'all';
$GLOBALS['display_show_inactive'] = 'n';
$GLOBALS['display_num_columns'] = 2;
$GLOBALS['display_show_section'] = 0;
$GLOBALS['display_show_priority'] = 'n';

// Closed //

if (isset($_SESSION['filter_closed'])) {
    $GLOBALS['display_filter_closed'] = $_SESSION['filter_closed'];
}

if (isset($_REQUEST['filter_closed'])) {
    $GLOBALS['display_filter_closed'] = $_REQUEST['filter_closed'];
}

$_SESSION['filter_closed'] = $GLOBALS['display_filter_closed'];

// Priority //

if (isset($_SESSION['filter_priority'])) {
    $GLOBALS['display_filter_priority'] = $_SESSION['filter_priority'];
}

if (isset($_REQUEST['filter_priority'])) {
    $GLOBALS['display_filter_priority'] = $_REQUEST['filter_priority'];
}

$_SESSION['filter_priority'] = $GLOBALS['display_filter_priority'];

// Aging //

if (isset($_SESSION['filter_aging'])) {
    $GLOBALS['display_filter_aging'] = $_SESSION['filter_aging'];
}

if (isset($_REQUEST['filter_aging'])) {
    $GLOBALS['display_filter_aging'] = $_REQUEST['filter_aging'];
}

$_SESSION['filter_aging'] = $GLOBALS['display_filter_aging'];

// Inactive //

if (isset($_SESSION['show_inactive'])) {
    $GLOBALS['display_show_inactive'] = $_SESSION['show_inactive'];
}

if (isset($_REQUEST['show_inactive'])) {
    $GLOBALS['display_show_inactive'] = $_REQUEST['show_inactive'];
}

$_SESSION['show_inactive'] = $GLOBALS['display_show_inactive'];

// Num Columns //

if (isset($_SESSION['num_columns'])) {
    $GLOBALS['display_num_columns'] = $_SESSION['num_columns'];
}

if (isset($_REQUEST['num_columns'])) {
    $GLOBALS['display_num_columns'] = $_REQUEST['num_columns'];
}

$_SESSION['num_columns'] = $GLOBALS['display_num_columns'];

// Show Section //

if (isset($_SESSION['show_section'])) {
    $GLOBALS['display_show_section'] = $_SESSION['show_section'];
}

if (isset($_REQUEST['show_section'])) {
    $GLOBALS['display_show_section'] = $_REQUEST['show_section'];
}

$_SESSION['show_section'] = $GLOBALS['display_show_section'];

// Priority Value //

if (isset($_SESSION['show_priority'])) {
    $GLOBALS['display_show_priority'] = $_SESSION['show_priority'];
}

if (isset($_REQUEST['show_priority'])) {
    $GLOBALS['display_show_priority'] = $_REQUEST['show_priority'];
}

$_SESSION['show_priority'] = $GLOBALS['display_show_priority'];


// ... //

$GLOBALS['closed_display'] = [
    'all' => 'All',
    'recently' => 'Recently',
    'today' => 'Today',
    'none' => 'None'
];

$GLOBALS['priority_display'] = [
    'all' => 'All',
    'high' => '' . $GLOBALS['todo_priority']['high'],
    'normal' => $GLOBALS['todo_priority']['high'] . '-' . $GLOBALS['todo_priority']['normal'],
    'low' => $GLOBALS['todo_priority']['high'] . '-' . $GLOBALS['todo_priority']['low']
];

$GLOBALS['aging_display'] = [
    'all' => 'All',
    '30' => '30',
    '60' => '60',
    '90' => '90',
    '365' => '365'
];

$GLOBALS['show_priority_display'] = [
    'y' => 'All',
    'above_normal' => 'Above Normal',
    'n' => 'None'
];
