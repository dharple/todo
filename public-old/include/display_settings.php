<?php

// Reset To Defaults //

if ($_REQUEST['reset_display_settings']) {
    unset($_SESSION['filter_closed']);
    unset($_SESSION['filter_priority']);
    unset($_SESSION['filter_aging']);
    unset($_SESSION['show_estimate']);
    unset($_SESSION['show_inactive']);
    unset($_SESSION['num_columns']);
    unset($_SESSION['show_section']);
    unset($_SESSION['show_priority']);
}

// Defaults //

$display_filter_closed = 'none';
$display_filter_aging = 'all';
$display_filter_priority = 'all';
$display_show_estimate = 'n';
$display_show_inactive = 'n';
$display_num_columns = 2;
$display_show_section = 0;
$display_show_priority = 'n';

// Closed //

if (isset($_SESSION['filter_closed'])) {
    $display_filter_closed = $_SESSION['filter_closed'];
}

if (isset($_REQUEST['filter_closed'])) {
    $display_filter_closed = $_REQUEST['filter_closed'];
}

$_SESSION['filter_closed'] = $display_filter_closed;

// Priority //

if (isset($_SESSION['filter_priority'])) {
    $display_filter_priority = $_SESSION['filter_priority'];
}

if (isset($_REQUEST['filter_priority'])) {
    $display_filter_priority = $_REQUEST['filter_priority'];
}

$_SESSION['filter_priority'] = $display_filter_priority;

// Aging //

if (isset($_SESSION['filter_aging'])) {
    $display_filter_aging = $_SESSION['filter_aging'];
}

if (isset($_REQUEST['filter_aging'])) {
    $display_filter_aging = $_REQUEST['filter_aging'];
}

$_SESSION['filter_aging'] = $display_filter_aging;

// Estimate //

if (isset($_SESSION['show_estimate'])) {
    $display_show_estimate = $_SESSION['show_estimate'];
}

if (isset($_REQUEST['show_estimate'])) {
    $display_show_estimate = $_REQUEST['show_estimate'];
}

$_SESSION['show_estimate'] = $display_show_estimate;

// Inactive //

if (isset($_SESSION['show_inactive'])) {
    $display_show_inactive = $_SESSION['show_inactive'];
}

if (isset($_REQUEST['show_inactive'])) {
    $display_show_inactive = $_REQUEST['show_inactive'];
}

$_SESSION['show_inactive'] = $display_show_inactive;

// Num Columns //

if (isset($_SESSION['num_columns'])) {
    $display_num_columns = $_SESSION['num_columns'];
}

if (isset($_REQUEST['num_columns'])) {
    $display_num_columns = $_REQUEST['num_columns'];
}

$_SESSION['num_columns'] = $display_num_columns;

// Show Section //

if (isset($_SESSION['show_section'])) {
    $display_show_section = $_SESSION['show_section'];
}

if (isset($_REQUEST['show_section'])) {
    $display_show_section = $_REQUEST['show_section'];
}

$_SESSION['show_section'] = $display_show_section;

// Priority Value //

if (isset($_SESSION['show_priority'])) {
    $display_show_priority = $_SESSION['show_priority'];
}

if (isset($_REQUEST['show_priority'])) {
    $display_show_priority = $_REQUEST['show_priority'];
}

$_SESSION['show_priority'] = $display_show_priority;


// ... //

$closed_display = [
    'all' => 'All',
    'recently' => 'Recently',
    'today' => 'Today',
    'none' => 'None'
];

$priority_display = [
    'all' => 'All',
    'high' => '' . $todo_priority['high'],
    'normal' => $todo_priority['high'] . '-' . $todo_priority['normal'],
    'low' => $todo_priority['high'] . '-' . $todo_priority['low']
];

$aging_display = [
    'all' => 'All',
    '30' => '30',
    '60' => '60',
    '90' => '90',
    '365' => '365'
];

$show_priority_display = [
    'y' => 'All',
    'above_normal' => 'Above Normal',
    'n' => 'None'
];
