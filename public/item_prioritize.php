<?php

use App\Legacy\Entity\Item;
use App\Legacy\ListDisplay;
use App\Legacy\SimpleList;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

if (count($_POST)) {
    if ($_POST['submitButton'] == 'Update') {
        foreach ($_POST['itemPriority'] as $itemId => $priority) {
            $item = new Item($db, $itemId);
            if ($item->getId() != $itemId) {
                print('Unable to load item #' . $itemId . '<br>');
                continue;
            }

            if ($priority < $GLOBALS['todo_priority']['high']) {
                $priority = $GLOBALS['todo_priority']['high'];
            } elseif ($priority > $GLOBALS['todo_priority']['low']) {
                $priority = $GLOBALS['todo_priority']['low'];
            }

            $item->setPriority($priority);
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

$listDisplay = new ListDisplay($db, $user->getId());
$listDisplay->setInternalPriorityLevels($GLOBALS['todo_priority']);
$listDisplay->setShowPriorityEditor('y');

$listDisplay->setFilterClosed($GLOBALS['display_filter_closed']);
$listDisplay->setFilterPriority($GLOBALS['display_filter_priority']);
$listDisplay->setFilterAging($GLOBALS['display_filter_aging']);
$listDisplay->setShowInactive($GLOBALS['display_show_inactive']);

$ids = unserialize($_REQUEST['ids']);
if (is_array($ids) && count($ids)) {
    $listDisplay->setIds($ids);
} else {
    $itemList = new SimpleList($db, Item::class);
    $items = $itemList->load("WHERE user_id = '" . addslashes($user->getId()) . "' AND status = 'Open'");
    $ids = [];
    foreach ($items as $item) {
        array_push($ids, $item->getId());
    }
    if (count($ids) > 0) {
        $listDisplay->setIds($ids);
    }
}

$twig->display('partials/page/header.html.twig', [
    'title' => 'Priority Editor',
]);

?>

<table width=100%>
<tr>
<td align=left>
<b>Priority Editor</b>
</td>
<td align=right>
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>

<form method=POST action="item_prioritize.php">
    <input type="hidden" name="ids" value="<?php print(htmlspecialchars($_REQUEST['ids'])); ?>">

<?php

print($listDisplay->getOutput());

$itemCount = $listDisplay->getOutputCount();

?>
<br>

<input type="submit" name="submitButton" value="Update" <?php if ($itemCount == 0) {
    print('disabled') ;
                                                        }?>>

</form>

<?php

$twig->display('partials/page/footer.html.twig');
