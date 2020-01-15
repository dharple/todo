<?php

require_once("include/common.php");
require_once("include/UserStylesheet.php");

$error_message = '';

if (count($_POST)) {

	if($_POST['submitButton'] == 'Update') {
		$user = new User($db, $_SESSION['user_id']);
		
		$user->setFullname(stripslashes($_POST['fullname']));
		if($_POST['timezone'] == 'Other')
			$user->setTimezone(stripslashes($_POST['timezone_other']));
		else
			$user->setTimezone(stripslashes($_POST['timezone']));
		$user->setDisplayStylesheetId($_POST['display_stylesheet_id']);
		$user->setPrintStylesheetId($_POST['print_stylesheet_id']);
		$user->setExportStylesheetId($_POST['export_stylesheet_id']);

		$user->save();
	}
	else if($_POST['submitButton'] == 'Change Password') {
		$user = new User($db, $_SESSION['user_id']);

		$ret = $user->confirmPassword($_POST['old_password']);
		if($ret && $_POST['new_password'] == $_POST['confirm']) {
			$user->setPassword($_POST['new_password']);
			$user->save();
		}
		else {
			if(!$ret)
				$error_message = 'Incorrect password';
			else
				$error_message = 'New passwords do not match';
		}
	}
	else if($_POST['submitButton'] == 'Edit Stylesheet') {
		header('Location: stylesheet_edit.php?stylesheet_id=' . $_POST['stylesheet_id']);
		die();
	}

}

?>
<html>
<head>
<title>Account Editor</title>
</head>
<body>

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
<br>

<?php

if($error_message != '') {
	print("<span style=\"color: red;\">$error_message</span><br><hr><br>");
}

$user = new User($db, $_SESSION['user_id']);
$user_id = $_SESSION['user_id'];

$stylesheetList = new SimpleList($db, 'UserStylesheet');

$userList = new SimpleList($db, 'User');
$tempUsers = $userList->load('');

$allUsers = array();
foreach($tempUsers as $tempUser) {
	$allUsers[$tempUser->getId()] = $tempUser->getFullname();
}

?>

<form method="POST" action="account.php">

<table>
<tr>
<td align=right>
Full Name:
</td>
<td align=left>
<input type="text" name="fullname" value="<?php print(htmlspecialchars($user->getFullname())); ?>">
</td>
</tr>

<tr>
<td align=right>
Timezone:
</td>
<td align=left>
<select name="timezone">
<?php
$timezones = array(
	'US/Eastern',
	'US/Central',
	'US/Mountain',
	'US/Pacific',
	'Other'
);

$current_tz = $user->getTimezone();

foreach($timezones as $tz) {
	print("<option value=\"$tz\"");
	if($current_tz == $tz)
		print(" selected");
	else if($tz == 'Other' && !in_array($current_tz, $timezones))
		print(" selected");

	print(">$tz</option>");
}

$other_tz = '';
if(!in_array($current_tz, $timezones))
	$other_tz = $current_tz;

?>
</select>
<input type="text" name="timezone_other" value="<?php print(htmlspecialchars($other_tz)); ?>" />
</td>
</tr>

<tr>
<td align=right>
Display Stylesheet:
</td>
<td align=left>
<select name="display_stylesheet_id">
<?php

$stylesheets = $stylesheetList->load("WHERE sheet_type = 'display' AND (user_id = '$user_id' OR public = 'y')");
$current_id = $user->getDisplayStylesheetId();

foreach($stylesheets as $stylesheet) {
	$id = $stylesheet->getId();
	print("<option value=\"$id\"");
	if($current_id == $id)
		print(' selected');

	print(">" . $stylesheet->getSheetName() . " (" . $allUsers[$stylesheet->getUserId()] . ")</option>");
}

?>
</select>
</td>
</tr>

<tr>
<td align=right>
Print Stylesheet:
</td>
<td align=left>
<select name="print_stylesheet_id">
<?php

$stylesheets = $stylesheetList->load("WHERE sheet_type = 'print' AND (user_id = '$user_id' OR public = 'y')");
$current_id = $user->getPrintStylesheetId();

foreach($stylesheets as $stylesheet) {
	$id = $stylesheet->getId();
	print("<option value=\"$id\"");
	if($current_id == $id)
		print(' selected');

	print(">" . $stylesheet->getSheetName() . " (" . $allUsers[$stylesheet->getUserId()] . ")</option>");
}

?>
</select>
</td>
</tr>

<tr>
<td align=right>
Export Stylesheet:
</td>
<td align=left>
<select name="export_stylesheet_id">
<?php

$stylesheets = $stylesheetList->load("WHERE sheet_type = 'export' AND (user_id = '$user_id' OR public = 'y')");
$current_id = $user->getExportStylesheetId();

foreach($stylesheets as $stylesheet) {
	$id = $stylesheet->getId();
	print("<option value=\"$id\"");
	if($current_id == $id)
		print(' selected');

	print(">" . $stylesheet->getSheetName() . " (" . $allUsers[$stylesheet->getUserId()] . ")</option>");
}

?>
</select>
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

<hr>

<form method="POST" action="account.php">

<table>
<tr>
<td align=right>
Edit Stylesheet:
</td>
<td align=left>
<select name="stylesheet_id">
<?php

$stylesheets = $stylesheetList->load("WHERE (user_id = '$user_id' OR public = 'y')");
$current_id = $user->getDisplayStylesheetId();

foreach($stylesheets as $stylesheet) {
	$id = $stylesheet->getId();
	print("<option value=\"$id\"");
	if($current_id == $id)
		print(' selected');

	print(">" . $stylesheet->getSheetName() . " / " . ucfirst($stylesheet->getSheetType()) . " / " . $allUsers[$stylesheet->getUserId()] . "</option>");
}

?>
</select>
</td>
</tr>

</table>

<input type=submit name="submitButton" value="Edit Stylesheet">

</form>

</body>
</html>
