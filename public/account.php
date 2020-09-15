<?php

use App\Legacy\Entity\User;
use App\Legacy\SimpleList;

$error_message = '';

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Update') {
        $user = new User($db, $user->getId());

        $user->setFullname($_POST['fullname']);
        if ($_POST['timezone'] == 'Other') {
            $user->setTimezone($_POST['timezone_other']);
        } else {
            $user->setTimezone($_POST['timezone']);
        }

        $user->save();
    } elseif ($_POST['submitButton'] == 'Change Password') {
        $user = new User($db, $user->getId());

        $ret = $user->confirmPassword($_POST['old_password']);
        if ($ret && $_POST['new_password'] == $_POST['confirm']) {
            $user->setPassword($_POST['new_password']);
            $user->save();
        } else {
            if (!$ret) {
                $error_message = 'Incorrect password';
            } else {
                $error_message = 'New passwords do not match';
            }
        }
    }
}

$twig->display('partials/page/header.html.twig', [
    'title' => 'Account Editor',
]);

?>

<table width=100%>
<tr>
<td align=left>
<b>Account Editor</b>
</td>
<td align=right>
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>

<?php

if ($error_message != '') {
    print('<span style="color: red;">' . $error_message . '</span><br><hr><br>');
}

?>

<form method="POST" action="account.php">

<table>
<tr>
<td align=right>
Full Name:
</td>
<td align=left>
<input type="text" name="fullname" value="<?php print(htmlspecialchars($user->getFullname())); ?>" data-lpignore="true" />
</td>
</tr>

<tr>
<td align=right>
Timezone:
</td>
<td align=left>
<select name="timezone">
<?php
$timezones = timezone_identifiers_list(DateTimeZone::PER_COUNTRY, 'US');
$timezones[] = 'Other';

$current_tz = $user->getTimezone();

foreach ($timezones as $tz) {
    print('<option value="' . $tz . '"');
    if ($current_tz == $tz) {
        print(' selected');
    } elseif ($tz == 'Other' && !in_array($current_tz, $timezones)) {
        print(' selected');
    }

    print('>' . $tz . '</option>');
}

$other_tz = '';
if (!in_array($current_tz, $timezones)) {
    $other_tz = $current_tz;
}

?>
</select>
<input type="text" name="timezone_other" value="<?php print(htmlspecialchars($other_tz)); ?>" data-lpignore="true" />
</td>
</tr>

</table>

<input type=submit name="submitButton" value="Update">

<hr>

Change Password<br><br>

<table>
<tr><td align=right>Old Password:</td><td><input type="password" name="old_password" value="" /></td></tr>
<tr><td align=right>New Password:</td><td><input type="password" name="new_password" value="" /></td></tr>
<tr><td align=right>Confirm:</td><td><input type="password" name="confirm" value="" /></td></tr>
</table>

<input type=submit name="submitButton" value="Change Password">

</form>

<?php

$twig->display('partials/page/footer.html.twig');
