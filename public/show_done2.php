<?php

use App\Legacy\SimpleList;
use App\Legacy\DateUtils;
use App\Legacy\ItemHistory;
use App\Legacy\Entity\Section;

$db = $GLOBALS['db'];
$twig = $GLOBALS['twig'];
$user = $GLOBALS['user'];

$twig->display('partials/page/header.html.twig', [
    'title' => 'Items Done',
]);

?>

<table width=100%>
<tr>
<td align=left>
<b>Done List
<?php

if ($_REQUEST['view'] == 'month') {
    print(' - This Month');
} elseif ($_REQUEST['view'] == 'lastmonth') {
    print(' - Last Month');
} elseif ($_REQUEST['view'] == 'week') {
    print(' - This Week');
} elseif ($_REQUEST['view'] == 'lastweek') {
    print(' - Last Week');
} elseif ($_REQUEST['view'] == 'today') {
    print(' - Today');
} elseif ($_REQUEST['view'] == 'yesterday') {
    print(' - Yesterday');
} else {
    print(' - All Items');
}
?></b>
</td>
<td align=right>
<a href="show_done.php?view=<?php print($_REQUEST['view']); ?>">Main View</a> |
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>

<table width=100%>

<?php

$sectionList = new SimpleList($db, Section::class);
$sections = $sectionList->load("WHERE user_id = '" . addslashes($user->getId()) . "'");
$sectionsById = [];
foreach ($sections as $section) {
    $sectionsById[$section->getId()] = $section->getName();
}
unset($sections);

$dateUtils = new DateUtils();


$itemHistory = new ItemHistory($db, $user->getId());
$itemHistory->setOrdering('section');

switch ($_REQUEST['view']) {
    case 'month':
        $items = $itemHistory->doneThisMonth();
        break;

    case 'lastmonth':
        $items = $itemHistory->doneLastMonth();
        break;

    case 'week':
        $items = $itemHistory->doneThisWeek();
        break;

    case 'lastweek':
        $items = $itemHistory->doneLastWeek();
        break;

    case 'yesterday':
        $items = $itemHistory->doneYesterday();
        break;

    case 'today':
        $items = $itemHistory->doneToday();
        break;

    default:
        $items = $itemHistory->doneTotal();
        break;
}

$lastDate = '';
$lastSection = '';

foreach ($items as $item) {
    $thisDate = date('F jS, Y', strtotime($item->getCompleted()));
    $thisSection = $item->getSectionId();

    if ($thisDate != $lastDate || $thisSection != $lastSection) {
        if ($lastDate != '') {
            print("</td></tr>\n");
        }

        if ($lastDate != '' && $thisDate != $lastDate) {
            print("<tr><td colspan=5><hr width=\"90%\"></td></tr>\n");
        } elseif ($lastSection != 0 && $thisSection != $lastSection) {
            print("<tr><td colspan=5>&nbsp;</td></tr>\n");
        }

        print('<tr><td valign=top align=right width=10%>');

        if ($thisDate != $lastDate) {
            print('<nobr>');
            print($thisDate);
            print('</nobr>');
            $lastDate = $thisDate;
            $lastSection = 0;
        } else {
            print('&nbsp;');
        }

        print('</td><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;</td>');

        print('<td valign=top>');
        if ($thisSection != $lastSection) {
            print('<nobr>');
            print($sectionsById[$thisSection]);
            print('</nobr>');
            $lastSection = $thisSection;
        } else {
            print('&nbsp;');
        }

        print('</td><td valign=top>&nbsp;&nbsp;&nbsp;&nbsp;</td>');

        print('<td valign=top align=left width=75%>');
    } else {
        print('<br>');
    }

    print($item->getTask());
}

if ($lastDate != '') {
    print("</td></tr>\n");
}

?>

</table>

<?php

$twig->display('partials/page/footer.html.twig');
