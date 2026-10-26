<?
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_session.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util_core.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_lang.php');

if (IS_AJAX) {
	$act = getParam("act");
	$mAuth = validMenu($mid, "", $db);
	if ($userlevel >= substr($mAuth,0,1)) {
		$CanRead = "Y";
	} else {
		$CanRead = "N";
	}
	if ($userlevel >= substr($mAuth,1,1)) {
		$CanWrite = "Y";
	} else {
		$CanWrite = "N";
	}
	if ($userlevel >= substr($mAuth,2,1)) {
		$CanDelete = "Y";
	} else {
		$CanDelete = "N";
	}


	switch ($act) {
		case "getProgram":
			$a = getParam("a");
			$ret = makeInput($db, $CanWrite, "select", "PROGRAM", "mProgram", "mProgram", "", "", "", "N", $a, "전체", "Y");
			break;
		case "getUserProgram":
			$a = getParam("a");
			$ret = makeInput($db, $CanWrite, "select", "USERPROGRAM", "mProgram", "mProgram", "", "", "", "N", $a, "", "Y");
			break;
		case "getPeriod":
			$a = getParam("a");
			$lang = getParam("lang");
			$ret = getPeriod($db, $a);
			break;
		case "getGrade":
			$a = getParam("a");
			$lang = getParam("lang");
			$ret = makeInput($db, $CanWrite, "select", "GRADE", "mGrade", "mGrade", "", "", "", "Y", $a, $msg_select, "Y");
		}
	echo $ret;
}
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db_close.php');
?>