<?php

require_once('include/common.php');
require_once('include/DateUtils.php');
require_once('include/RecurringItem.php');

$user_id = $_SESSION['user_id'];

if (count($_POST)) {
    $dateUtils = new DateUtils();

    $tasks = split("[\r\n]", stripslashes($_POST['tasks']));
    foreach ($tasks as $task) {
        $task = trim($task);
        if ($task == '') {
            continue;
        }

        $item = new Item($db);

        $item->setCreated($dateUtils->getNow());
        $item->setUserId($user_id);
        $item->setTask($task);
        $item->setSectionId($_POST['section']);
        $item->setStatus('Open');
        $item->setPriority($_POST['priority']);
        $item->save();

        if ($_POST['recurring'] == '1') {
            $recurring_item = new RecurringItem($db);

            $recurring_item->setUserId($user_id);
            $recurring_item->setTask($task);
            $recurring_item->save();
        }
    }

    if ($_REQUEST['submitButton'] == 'Do It') {
        header('Location: index.php');
        die();
    }
}

?>
<html>
<head>
<title>Item Bulk Add</title>
</head>
<body>

<script language="JavaScript">

function copy_items(form) {
    var items = form.recurring_items;

    var build = '';

    for(var i = 0; i < items.length; i++) {
        if(!items.options[i].selected)
            continue;

        build += items.options[i].text + "\n";
    }

    if(build != '')
        form.tasks.value += "\n" + build;
}

</script>

<table width=100%>
<tr>
<td align=left>
<b>Item Bulk Add</b>
</td>
<td align=right>
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>

<form method="POST" action="item_bulk_add.php">

Bulk Adding...<br><br>

Section:
<select name=section>
<?php

$ids = [];
$query = "SELECT section_id, MAX(created) AS created FROM item WHERE user_id = '$user_id' GROUP BY section_id ORDER BY created DESC LIMIT 5";
$result = $db->query($query);
while ($row = $db->fetchRow($result)) {
    array_push($ids, $row[0]);
}

if (count($ids) > 0) {
    $sectionList = new SimpleList($db, 'Section');
    $sections = $sectionList->load("WHERE user_id = '$user_id' AND id IN (" . implode(',', $ids) . ')');

    foreach ($ids as $id) {
        foreach ($sections as $section) {
            if ($section->getId() == $id) {
                break;
            }
        }
        if (!is_object($section)) {
            continue;
        }

        if ($section->getId() != $id) {
            continue;
        }

        print('<option value=' . $section->getId() . '>');
        print($section->getName());
        if ($section->getStatus() == 'Inactive') {
            print(' (Inactive)');
        }
        print('</option>');
    }

    print('<option value="">-------------------------</option>');
}

$sectionList = new SimpleList($db, 'Section');
$sections = $sectionList->load("WHERE user_id = '$user_id' ORDER BY name");

$sectionCache = [];
foreach ($sections as $section) {
    print('<option value=' . $section->getId() . '>');
    print($section->getName());
    if ($section->getStatus() == 'Inactive') {
        print(' (Inactive)');
    }
    print('</option>');

    $sectionCache[$section->getId()] = $section->getName();
}

?>
</select>
<br>

Priority:
<select name=priority>
<?php
for ($priority = $todo_priority['high']; $priority <= $todo_priority['low']; $priority++) {
    print('<option value="' . $priority . '"');
    if ($priority == $todo_priority['normal']) {
        print(' selected');
    }
    print('>');
    print($priority);
    print('</option>');
}
?>
</select>
<br>

Recurring:
<input type="checkbox" name="recurring" value="1" />
<br>

<table>
<tr>
<td>

Tasks (newline separated):<br>
<textarea name="tasks" cols=60 rows=15></textarea><br>

</td>

<?php

$recurringItemList = new SimpleList($db, 'RecurringItem');
$recurringItems = $recurringItemList->load("WHERE user_id = '$user_id' ORDER BY task");

if (count($recurringItems) > 0) {
    ?>

<td>&nbsp;&nbsp;&nbsp;</td>

<td align="left" valign="top">

<br>

<select name="recurring_items" size="10" multiple>
    <?php

    foreach ($recurringItems as $recurringItem) {
        print('<option value=' . $recurringItem->getId() . '>');
        print($recurringItem->getTask());
        print('</option>');
    }

    ?>
</select>

<br>
<input type="button" value="Copy" onClick="copy_items(this.form)" />

</tr>

    <?php
}
?>

</table>

<hr>

<input type=submit name="submitButton" value="Do It">
<input type=submit name="submitButton" value="Do It, Then Add Another">

</form>

</body>
</html>
