<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Legacy\Entity\Section;

// Handle POST

$refresh_url = '';

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Export') {
        if ($_POST['version'] == 'html') {
            $refresh_url = 'export_basic.php';
        } elseif ($_POST['version'] == 'ics') {
            $refresh_url = 'export_ics.php';
        }
    }
}

?>
<html>
<head>
<title>Export Menu</title>
<link rel="stylesheet" href="styles/display.php" type="text/css">
<meta name="robots" content="noindex, nofollow">
<?php
if ($refresh_url) {
    print('<meta http-equiv="refresh" content="1;url=' . htmlspecialchars($refresh_url) . '">');
}
?>
</head>
<body>

<table width=100%>
<tr>
<td align=left valign=top>
<b>Export Menu</b>
</td>
<td align=right>
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>

<?php

if ($refresh_url) {
    print('<table width=100%><tr align=center><td><b>Starting download</b></td></tr></table><br><hr><br>');
}

?>

<table>
<tr>
<td align=right>
Filter Closed Items:
</td>
<td>
<b><?php print($closed_display[$display_filter_closed]); ?></b>
</td>
</tr>
<tr>
<td align=right>
Filter Priority:
</td>
<td>
<b><?php print($priority_display[$display_filter_priority]); ?></b>
</td>
</tr>
<tr>
<td align=right>
Show Priority:
</td>
<td>
<b><?php print($show_priority_display[$display_show_priority]); ?></b>
</td>
</tr>
<tr>
<td align=right>
Filter Aging:
</td>
<td>
<b><?php

print($aging_display[$display_filter_aging]);
if ($display_filter_aging != 'all') {
    print(' days old');
}

?></b>
</td>
</tr>
<tr>
<td align=right>
Show Estimates:
</td>
<td>
<b><?php print($display_show_estimate == 'y' ? 'Yes' : 'No'); ?></b>
</td>
</tr>
<tr>
<td align=right>
Show Inactive Sections:
</td>
<td>
<b><?php print($display_show_inactive == 'y' ? 'Yes' : 'No'); ?></b>
</td>
</tr>
<tr>
<td align=right>
Show Section:
</td>
<td>
<b>
<?php

if ($display_show_section == 0) {
    print('All');
} else {
    $section = new Section($db, $display_show_section);
    print($section->getName());
}

?>
</b>
</td>
</tr>
</table>

<br>
<br>

<form method=POST>

<table>
<tr>
<td align=right>
Version:
</td>
<td>
<select name="version">
<option value="html">HTML</option>
<option value="ics">ICS</option>
</select>
</td>
</tr>
</table>

<table width="100%" cellpadding=0 cellspacing=0 border=0>
    <tr>
        <td align=left>
            <input type="submit" name="submitButton" value="Export">
        </td>
    </tr>
</table>

</form>

</body>
</html>
