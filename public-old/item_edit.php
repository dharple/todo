<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Legacy\DateUtils;
use App\Legacy\Entity\Item;
use App\Legacy\Entity\Section;
use App\Legacy\SimpleList;

$user_id = $_SESSION['user_id'];

if (count($_POST)) {
    $dateUtils = new DateUtils();

    if (!is_array($_POST['task'])) {
        $_POST['task'] = [];
    }

    foreach ($_POST['task'] as $itemId => $task) {
        if ($itemId == 'new') {
            $item = new Item($db);
            $item->setCreated($dateUtils->getNow());
            $item->setUserId($user_id);
        } else {
            $item = new Item($db, $itemId);
            $item->setCompleted($_POST['completed'][$itemId]);
        }
        $item->setTask($task);
        $item->setSectionId($_POST['section'][$itemId]);
        $item->setStatus($_POST['status'][$itemId]);
        $item->setPriority($_POST['priority'][$itemId]);
        $item->setEstimate($_POST['estimate'][$itemId]);
        $item->save();
    }

    if ($_REQUEST['submitButton'] == 'Do It') {
        header('Location: index.php');
        die();
    }
}

?>
<html>
<head>
<title>Item Editor</title>
</head>
<body>

<table width=100%>
<tr>
<td align=left>
<b>Item Editor</b>
</td>
<td align=right>
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>


<form method="POST" action="item_edit.php">
    <input type="hidden" name="op" value="<?php print($_REQUEST['op']); ?>">
    <input type="hidden" name="ids" value="<?php print(htmlspecialchars($_REQUEST['ids'] ?? '')); ?>">


<?php

$sectionList = new SimpleList($db, Section::class);
$sections = $sectionList->load("WHERE user_id = '" . addslashes($user_id) . "' ORDER BY name");

if ($_REQUEST['op'] == 'edit') {
    print('Editing...<br><br>');

    $itemIds = unserialize($_REQUEST['ids']);

    if (!is_array($itemIds)) {
        $itemIds = [];
    }
} elseif ($_REQUEST['op'] == 'add') {
    print('Adding...<br><br>');

    $itemIds = ['new'];
}


foreach ($itemIds as $itemId) {
    if ($itemId == 'new') {
        $item = new Item($db);
        $item->setStatus('Open');
        $item->setPriority('Normal');
    } else {
        $item = new Item($db, $itemId);
    }

    print('Item Id #: ' . $itemId . "<br><br>\n");

    print('Section: ');
    print('<select name=section[' . $itemId . ']>');
    foreach ($sections as $section) {
        print('<option value=' . $section->getId());
        if ($section->getId() == $item->getSectionId()) {
            print(' selected');
        }
        print('>');
        print($section->getName());
        if ($section->getStatus() == 'Inactive') {
            print(' (Inactive)');
        }
        print('</option>');
    }
    print('</select>');
    print("<br>\n");

    print('Task: ');
    print('<input type=text name=task[' . $itemId . '] value="' .
            htmlspecialchars($item->getTask()) . '">');
    print("<br>\n");

    if ($itemId == 'new') {
        print('<input type="hidden" name=status[' . $itemId . '] value="Open">');
    } else {
        print('Status: ');
        print('<select name=status[' . $itemId . ']>');
        foreach (['Open', 'Closed', 'Deleted'] as $status) {
            print('<option value="' . $status . '"');
            if ($status == $item->getStatus()) {
                print(' selected');
            }
            print('>');
            print($status);
            print('</option>');
        }
        print('</select>');
        print("<br>\n");
    }

    print('Priority: ');
    print('<select name=priority[' . $itemId . ']>');

    if ($item->getPriority() == 0) {
        $item->setPriority($todo_priority['normal']);
    }

    for ($priority = $todo_priority['high']; $priority <= $todo_priority['low']; $priority++) {
        print('<option value="' . $priority . '"');
        if ($priority == $item->getPriority()) {
            print(' selected');
        }
        print('>');
        print($priority);
        print('</option>');
    }
    print('</select>');
    print("<br>\n");

    if ($_REQUEST['op'] == 'edit') {
        print('Completed: ');
        print('<input type=text name=completed[' . $itemId . '] value="' .
                htmlspecialchars($item->getCompleted()) . '">');
        print("<br>\n");
    }

    print('Estimate: ');
    print('<input type=text name=estimate[' . $itemId . '] value="' .
            htmlspecialchars($item->getEstimate()) . '">');
    print("<br>\n");

    print("<br>\n");

    print('<hr>');
}

?>

<input type=submit name="submitButton" value="Do It">
<?php
if ($_REQUEST['op'] == 'add') {
    ?>
<input type=submit name="submitButton" value="Do It, Then Add Another">
    <?php
}
?>
</form>
