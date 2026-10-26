<?php
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_session.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util_core.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_lang.php');

$fid = getParam("fid");
$mAuth = validMenu($mid, "", $db);
$uid = getNumber(getParam("uid"));

//권한확인
if (is_numeric($fid) && $mid != "") {
	$stmt = @mysqli_prepare($db, "select wFile, wFileName, wFileSize from tFileDB where ID=?");
	@mysqli_stmt_bind_param($stmt, 'i', $fid);
	@mysqli_stmt_execute($stmt);
	@mysqli_stmt_bind_result($stmt, $aFile, $aFileName, $aFileSize);
	@mysqli_stmt_fetch($stmt);
	@mysqli_stmt_close($stmt);
	if ($aFile != "") {
		Header("Content-type: application/unkown");
		Header("Content-Length: ".$aFileSize);
		Header("Content-Disposition: attachment; filename=\"". iconv("UTF-8", "EUC-KR", $aFileName) ."\"");
		Header("Expires: 0");
		if (is_file($_SERVER['DOCUMENT_ROOT'] . $aFile)) {
		   $fp = fopen($_SERVER['DOCUMENT_ROOT'] . $aFile, "r");
		   if (!fpassthru($fp)) fclose($fp);
		}
	}
}
if ($uid > 0 && $mid != "" && $userlevel >= "7") {
	$stmt = @mysqli_prepare($db, "select mFile, mFileName, mFileSize from tMember where ID=?");
	@mysqli_stmt_bind_param($stmt, 'i', $uid);
	@mysqli_stmt_execute($stmt);
	@mysqli_stmt_bind_result($stmt, $aFile, $aFileName, $aFileSize);
	@mysqli_stmt_fetch($stmt);
	@mysqli_stmt_close($stmt);
	if ($aFile != "") {
		Header("Content-type: application/unkown");
		Header("Content-Length: ".$aFileSize);
		Header("Content-Disposition: attachment; filename=\"". iconv("UTF-8", "EUC-KR", $aFileName) ."\"");
		Header("Expires: 0");
		if (is_file($_SERVER['DOCUMENT_ROOT'] . $aFile)) {
		   $fp = fopen($_SERVER['DOCUMENT_ROOT'] . $aFile, "r");
		   if (!fpassthru($fp)) fclose($fp);
		}
	}
}

include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db_close.php');
?>