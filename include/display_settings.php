<?php

if (!isset($_SESSION['user_id'])) {
    return;
}

$fields = [
    'filter_aging'    => 'all',
    'filter_closed'   => 'none',
    'filter_priority' => 'all',
    'show_inactive'   => 'n',
    'show_priority'   => 'n',
    'show_section'    => 0,
];

foreach ($fields as $field => $default) {
    $displayField = 'display_' . $field;

    $value = $default;

    if (isset($_SESSION[$field]) && !isset($_REQUEST['reset_display_settings'])) {
        $value = $_SESSION[$field];
    }

    if (isset($_REQUEST[$field])) {
        $value = $_REQUEST[$field];
    }

    $_SESSION[$field] = $GLOBALS[$displayField] = $value;
}
