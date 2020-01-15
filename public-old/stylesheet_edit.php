<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Legacy\Entity\UserStylesheet;

$user_id = $_SESSION['user_id'];

if ($_REQUEST['stylesheet_id'] > 0) {
    $stylesheet_id = intval($_REQUEST['stylesheet_id']);
} else {
    $stylesheet_id = 0;
}

$stylesheet = new UserStylesheet($db);
$edit_stylesheet_id = 'new';

if ($stylesheet_id > 0) {
    $stylesheet->load($stylesheet_id);
    if ($stylesheet->getUserId() == $user_id) {
        $edit_stylesheet_id = $stylesheet_id;
    }
}

if (count($_POST)) {
    if ($_REQUEST['submitButton'] == 'Save As New') {
        $edit_stylesheet_id = 'new';
    }

    if ($edit_stylesheet_id == 'new') {
        $stylesheet->clearId();
        $stylesheet->setUserId($user_id);
        $stylesheet->setSheetType($_POST['sheet_type']);
    }

    $stylesheet->setSheetName(stripslashes($_POST['sheet_name']));
    $stylesheet->setPublic($_POST['public']);
    $stylesheet->setContents(stripslashes($_POST['contents']));

    $stylesheet->save();

    $edit_stylesheet_id = $stylesheet->getId();
}

?>
<html>
<head>
<title>Stylesheet Editor</title>
</head>
<body>

<table width=100%>
<tr>
<td align=left>
<b>Stylesheet Editor</b>
</td>
<td align=right>
<a href="account.php">Account Page</a> |
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>

<form method="POST" action="stylesheet_edit.php">
    <input type="hidden" name="stylesheet_id" value="<?php print($edit_stylesheet_id); ?>">

    <table>
        <tr>
            <td valign=top>Name:</td>
            <td valign=top><input type="text" name="sheet_name" size="32" value="<?php print(htmlspecialchars($stylesheet->getSheetName())); ?>"></td>
        </tr>
        <tr>
            <td valign=top>Type:</td>
            <td valign=top>
<?php
if ($edit_stylesheet_id == 'new') {
    print('<select name="sheet_type">');
    foreach (['display', 'print', 'export'] as $sheet_type) {
        print("<option value=\"$sheet_type\"");
        if ($sheet_type == $stylesheet->getSheetType()) {
            print(' selected');
        }
        print('>' . ucfirst($sheet_type) . '</option>');
    }
    print('</select>');
} else {
    print(ucfirst($stylesheet->getSheetType()));
    print('<input type="hidden" name="sheet_type" value="' . $stylesheet->getSheetType() . '">');
}
?>
            </td>
        </tr>

        <tr>
            <td valign=top>Public:</td>
            <td valign=top>
<?php
print('<select name="public">');
foreach (['n', 'y'] as $public) {
    print("<option value=\"$public\"");
    if ($public == $stylesheet->getPublic()) {
        print(' selected');
    }
    print('>' . (($public == 'y') ? 'Yes' : 'No') . '</option>');
}
print('</select>');
?>
            </td>
        </tr>
        <tr>
            <td valign=top>Contents:</td>
            <td valign=top><textarea name="contents" cols=60 rows=20><?php print(htmlspecialchars($stylesheet->getContents())); ?></textarea></td>
        </tr>
    </table>

<?php
if ($edit_stylesheet_id != 'new') {
    ?>
<input type=submit name="submitButton" value="Save">
    <?php
}
?>

<input type=submit name="submitButton" value="Save As New">

</form>
