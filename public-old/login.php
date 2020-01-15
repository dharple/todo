<?php

$is_login = true;

require_once('include/common.php');

$error_message = '';

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Login') {
        $user = new \App\Legacy\User($db);
        $ret = $user->login($_POST['username'], $_POST['password']);

        if ($ret) {
            $_SESSION['user_id'] = $user->getId();
            if ($user->getId() === '0') {
                header('Location: admin.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error_message = 'Invalid Login';
        }
    }
}

?>
<html>
<head>
<title>To Do List</title>
<link rel="stylesheet" href="styles/display.php" type="text/css">
<meta name="robots" content="noindex, nofollow">
</head>
<body>

<form method=POST>

<br>
<?php

if ($error_message != '') {
    print("<span style=\"color: red;\">$error_message</span><br><hr><br>");
}

?>

<table cellpadding=0 cellspacing=0 border=0>
    <tr>
        <td align=right>
            Username:
        </td>
        <td align=left>
            <input type="text" name="username" />
        </td>
    </tr>
    <tr>
        <td align=right>
            Password:
        </td>
        <td align=left>
            <input type="password" name="password" />
        </td>
    </tr>
    <tr>
        <td align=center colspan=2>
            <input type="submit" name="submitButton" value="Login" />
        </td>
    </tr>
</table>

</form>

<body>
</html>
