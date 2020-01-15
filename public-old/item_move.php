<?php

require_once('include/common.php');
require_once('include/DateUtils.php');

$user_id = $_SESSION['user_id'];

if ($user_id !== '0') {
    header('Location: index.php');
    exit();
}

?>
<html>
<head>
<title>Item Mover</title>

</head>
<body>

<table width=100%>
<tr>
<td align=left>
<b>Item Mover</b>
</td>
<td align=right>
<a href="admin.php">Home</a>
</td>
</tr>
</table>

<hr>
<br>

<?php

$userList = new SimpleList($db, 'User');
$users = $userList->load('ORDER BY username');

?>

<form method="POST">

<table width="100%">

<tr>
<td width="50%" align="center">
<b>From:</b>
<select name="from_user_id">
<option value="">Choose...</option>
<?php

foreach ($users as $user) {
    print('<option value="' . $user->getId() . '">' . $user->getUsername() . '</option>');
}

?>
</select>
</td>
<td width="50%" align="center">
<b>To:</b>
<select name="to_user_id">
<option value="">Choose...</option>
<?php

foreach ($users as $user) {
    print('<option value="' . $user->getId() . '">' . $user->getUsername() . '</option>');
}

?>
</select>
</td>
</tr>

</table>

</form>

</body>
</html>
