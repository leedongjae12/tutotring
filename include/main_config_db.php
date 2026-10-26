<?
//$db = @mysqli_connect('localhost', 'root', 'apmsetup', 'tutoring');
//$db = @mysqli_connect('165.132.16.36', 'tutoring', 't10041004', 'tutoring');
$db = @mysqli_connect('165.132.16.33', 'tt_user', 'tt_user1004', 'tutoring');
if (!$db) {
	include ($_SERVER['DOCUMENT_ROOT'] . '/include/error.php');
	exit;
}
@mysqli_query($db, "set names utf8");
@mysqli_autocommit($db, TRUE);
?>
