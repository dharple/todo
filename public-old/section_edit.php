<?php

use App\Legacy\DateUtils;
use App\Legacy\Entity\Item;
use App\Legacy\Entity\Section;
use App\Legacy\SimpleList;

$user_id = $_SESSION['user_id'];

?>
<html>
<head>
<title>Section Editor</title>

<script language="JavaScript">

function updateEditor(selectList, inputBox) {
    var idx = selectList.selectedIndex;
    var content = selectList.options[idx].text;
    if(content == null) {
        content = '';
    }

    inputBox.value = content;
}

</script>

</head>
<body>

<table width=100%>
<tr>
<td align=left>
<b>Section Editor</b>
</td>
<td align=right>
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>

<?php

if (count($_POST)) {
    $relational_cleanup = [];

    if ($_POST['submitButton'] != '') {
        $ret = true;

        if ($_POST['submitButton'] == 'Add') {
            $name = $_POST['add_name'];
            $name = trim($name);

            if ($name != '') {
                $section = new Section($db);
                $section->setName($name);
                $section->setUserId($user_id);
                $ret = $section->save();
            }
        } elseif ($_POST['submitButton'] == 'Rename') {
            $name = $_POST['edit_name'];
            $name = trim($name);

            $id = $_POST['edit_section_id'];
            if ($id > 0) {
                $section = new Section($db, $id);
                $section->setName($name);
                $ret = $section->save();
            }
        } elseif ($_POST['submitButton'] == 'Activate') {
            $id = $_POST['toggle_section_id'];
            if ($id == 'all') {
                $sectionList = new SimpleList($db, Section::class);
                $sections = $sectionList->load("WHERE user_id = '" . addslashes($user_id) . "' AND status = 'Inactive'");
            } else {
                if ($id > 0) {
                    $sections = [new Section($db, $id)];
                } else {
                    $sections = []; // skip the next loop
                }
            }
            foreach ($sections as $section) {
                if ($section->getStatus() == 'Inactive' && $_POST['resetStartTimes'] == 'yes') {
                    $dateUtils = new DateUtils();
                    $now = $dateUtils->getNow();
                    $query = "UPDATE item SET created='" . $now . "' WHERE section_id = '" . $section->getId() . "' AND status = 'Open'";
                    $result = $db->query($query);
                }
                $section->setStatus('Active');
                $ret = $section->save();
                if (!$ret) {
                    break;
                }
            }
        } elseif ($_POST['submitButton'] == 'Deactivate') {
            $id = $_POST['toggle_section_id'];
            if ($id == 'all') {
                $sectionList = new SimpleList($db, Section::class);
                $sections = $sectionList->load("WHERE user_id = '" . addslashes($user_id) . "' AND status = 'Active'");
            } else {
                if ($id > 0) {
                    $sections = [new Section($db, $id)];
                } else {
                    $sections = []; // skip the next loop
                }
            }
            foreach ($sections as $section) {
                $section->setStatus('Inactive');
                $ret = $section->save();
                if (!$ret) {
                    break;
                }
            }
        }

        if (!$ret) {
            print('An error occured while updating your section.<br>');
            print($section->getErrorNumber() . ': ' . $section->getErrorMessage());
            print('<br>');
            print('<hr>');
        }
    }
}


?>

<form method="POST">

Add New:<br>
<input type="text" name="add_name" />
<input type="submit" name="submitButton" value="Add" />

<br>
<br>
<hr>
<br>

Rename:<br>

<select name="edit_section_id" onChange="updateEditor(this.form.edit_section_id, this.form.edit_name);">
<option value="">Choose...</option>
<?php

$sectionList = new SimpleList($db, Section::class);
$sections = $sectionList->load("WHERE user_id = '" . addslashes($user_id) . "' ORDER BY name");

foreach ($sections as $section) {
    print('<option value="' . $section->getId() . '">' . $section->getName() . '</option>');
}

?>
</select>
to 
<input type="text" name="edit_name" />
<input type="submit" name="submitButton" value="Rename" />

<br>
<br>
<hr>
<br>

Change Status:<br>

<select name="toggle_section_id">
<option value="">Choose...</option>
<option value="all">All</option>
<?php

foreach ($sections as $section) {
    print('<option value="' . $section->getId() . '">' . $section->getName() . ' (' . $section->getStatus() . ')</option>');
}

?>
</select>
to 
<input type="submit" name="submitButton" value="Deactivate" />
<input type="submit" name="submitButton" value="Activate" />
<br>
<input type="checkbox" name="resetStartTimes" value="yes" /> <font size="2">Reset start times when making an inactive section active?</font>

<br>
<br>
<font size="2">Note: Open items that are in sections that are switched from Inactive to Active will have their created stamp set to the current time.</font>

<br>
<br>
<hr>
<br>


</form>

</body>
</html>
