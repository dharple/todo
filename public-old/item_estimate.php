<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Update') {
        foreach ($_POST['itemEstimate'] as $itemId => $estimate) {
            $item = new \App\Legacy\Item($db, $itemId);
            if ($item->getId() != $itemId) {
                print('Unable to load item #' . $itemId . '<br>');
                continue;
            }

            $item->setEstimate($estimate);
            $ret = $item->save();

            if (!$ret) {
                print('An error occured while updating item #' . $itemId . ' - ' . $item->getTask() . '.<br>');
                print($item->getErrorNumber() . ': ' . $item->getErrorMessage());
                print('<br>');
                print('<hr>');
            }
        }
    }
}

$listDisplay = new \App\Legacy\ListDisplay($db, $_SESSION['user_id']);
$listDisplay->setColumns(1);
$listDisplay->setInternalPriorityLevels($todo_priority);
$listDisplay->setShowEstimateEditor('y');

$listDisplay->setFilterClosed($display_filter_closed);
$listDisplay->setFilterPriority($display_filter_priority);
$listDisplay->setFilterAging($display_filter_aging);
$listDisplay->setShowInactive($display_show_inactive);

$ids = unserialize(stripslashes($_REQUEST['ids']));
if (is_array($ids) && count($ids)) {
    $listDisplay->setIds($ids);
} else {
    $user_id = $_SESSION['user_id'];
    $itemList = new \App\Legacy\SimpleList($db, \App\Legacy\Item::class);
    $items = $itemList->load("WHERE user_id = '$user_id' AND status = 'Open' AND estimate <= 0");
    $ids = [];
    foreach ($items as $item) {
        array_push($ids, $item->getId());
    }
    if (count($ids) > 0) {
        $listDisplay->setIds($ids);
    }
}

?>
<html>
<head>
<title>Estimate Editor</title>
<link rel="stylesheet" href="styles/display.php" type="text/css">
</head>
<body>

<table width=100%>
<tr>
<td align=left>
<b>Estimate Editor</b>
</td>
<td align=right>
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>

<form method=POST action="item_estimate.php">
    <input type="hidden" name="ids" value="<?php print(htmlspecialchars(stripslashes($_REQUEST['ids']))); ?>">

<?php

print($listDisplay->getOutput());

$itemCount = $listDisplay->getOutputCount();

?>
<br>

<input type="submit" name="submitButton" value="Update" <?php if ($itemCount == 0) {
    print('disabled') ;
                                                        }?>>

</form>

</body>
</html>