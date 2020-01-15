<?php

require_once('include/common.php');

// Defaults

include_once('include/display_settings.php');

// Handle POST

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Logout') {
        session_unset();
        header('Location: login.php');
        die();
    } elseif ($_POST['submitButton'] == 'My Account') {
        header('Location: account.php');
        die();
    } elseif ($_POST['submitButton'] == 'Move Items') {
        header('Location: item_move.php');
        die();
    }
}

$user_id = $_SESSION['user_id'];

if ($user_id !== '0') {
    header('Location: index.php');
    exit();
}

?>
<html>
<head>
<title>Admin Page</title>
<link rel="stylesheet" href="styles/display.php" type="text/css">
<meta name="robots" content="noindex, nofollow">
</head>
<body>

<br>

<form method=POST>

<table width="100%" cellpadding=0 cellspacing=0 border=0>
    <tr>
        <td align=left width="33%">
            <input type="submit" name="submitButton" value="Move Items">
        </td>
        <td align=center width="33%">
            <input type="submit" name="submitButton" value="My Account">
            <input type="submit" name="submitButton" value="Logout">
        </td>
        <td align=right width="33%">
            &nbsp;
        </td>
    </tr>
</table>

</form>


</body>
</html>
