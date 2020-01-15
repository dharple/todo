<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

?>
<html>
<head>
<title>Items Done</title>
</head>
<body>

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
} elseif ($_REQUEST['view'] == 'month3') {
    print(' - Past 3 Months');
} elseif ($_REQUEST['view'] == 'month6') {
    print(' - Past 6 Months');
} elseif ($_REQUEST['view'] == 'month9') {
    print(' - Past 9 Months');
} elseif ($_REQUEST['view'] == 'month12') {
    print(' - Past 12 Months');
} else {
    print(' - All Items');
}
?></b>
</td>
<td align=right>
<a href="show_done2.php?view=<?php print($_REQUEST['view']); ?>">Alternate View</a> |
<a href="index.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>

<table width=100%>

<?php

$sectionList = new \App\Legacy\SimpleList($db, \App\Legacy\Section::class);
$sections = $sectionList->load("WHERE user_id = '$user_id'");
$sectionsById = [];
foreach ($sections as $section) {
    $sectionsById[$section->getId()] = $section->getName();
}
unset($sections);

$dateUtils = new \App\Legacy\DateUtils();


$itemHistory = new \App\Legacy\ItemHistory($db, $user_id);

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

    case 'month3':
        $items = $itemHistory->donePreviousMonths(3);
        break;

    case 'month6':
        $items = $itemHistory->donePreviousMonths(6);
        break;

    case 'month9':
        $items = $itemHistory->donePreviousMonths(9);
        break;

    case 'month12':
        $items = $itemHistory->donePreviousMonths(12);
        break;

    default:
        $items = $itemHistory->doneTotal();
        break;
}

$lastDate = '';

foreach ($items as $item) {
    $thisDate = date('F jS, Y', strtotime($item->getCompleted()));

    if ($lastDate != '' && $thisDate != $lastDate) {
        print("<tr><td colspan=4>&nbsp;</td></tr>\n");
    }

    print('<tr><td valign=top align=right width=10%>');

    if ($thisDate != $lastDate) {
        print('<nobr>');
        print($thisDate);
        print('</nobr>');
        $lastDate = $thisDate;
    } else {
        print('&nbsp;');
    }

    $sectionId = $item->getSectionId();

    print('</td><td valign=top>');
    print('&nbsp;&nbsp;&nbsp;&nbsp;');
    print('</td><td valign=top align=left width=75%>');
    print($item->getTask());
    print('</td><td valign=top align=right><nobr>(');
    print($sectionsById[$sectionId]);
    print(")</nobr></td></tr>\n");
}

?>

</table>
</body>
</html>
