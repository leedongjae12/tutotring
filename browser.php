<?
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_session.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util_core.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_lang.php');

if ((int)$userlevel < 7) {
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?=$msg_browser_title?></title>
<LINK rel="stylesheet" type="text/css" href="<?=$html_css?>">
<link rel="icon" type="image/x-icon" href="/images/favicon.ico" />
<link rel="stylesheet" type="text/css" href="/css/gh-buttons.css" />
<script type="text/javascript" src="/script/jquery.min.js"></script>
<script type="text/javascript" src="/script/input.js"></script>
<script type="text/javascript">
	jQuery(window).load(function() {
		hideLoader();
	});
</script>
<script type="text/javascript">
	function OpenFile( fileUrl )
	{
		window.top.opener.tinyfck.document.forms[0].elements[window.top.opener.tinyfck_field].value = fileUrl;
		
		if (window.top.opener.tinyfck.document.forms[0].elements[window.top.opener.tinyfck_field].onchange != null) {
			window.top.opener.tinyfck.document.forms[0].elements[window.top.opener.tinyfck_field].onchange();
		}
		
		window.top.close();
		window.top.opener.tinyfck.focus();
	}
</script>
<body>
<?
$act = getParam("act");
$actt = getParam("actt");
$wDepth = getNumber(getParam("wDepth"));
$RefID = getNumber(getParam("RefID"));
$cmid = getParam("cmid");
$mid = getParam("mid");

if (!$cmid) {
	$cmid = $mid;
}

if ($actt == "image") {
	$FolderNo = "I";
	$FolderYes = "A";
} else if ($actt == "file") {
	$FolderNo = "F";
	$FolderYes = "B";
} else {
	exit;
}

If ($wDepth == "") {
	//처음이라면 1단계 구조 보여주기
	$wDepth = 1;
}

If ($RefID == "") {
	//처음이라면 참조아이디는 없음
	$RefID = 0;
}

If ($actt != "image" && $actt != "file") {
	exit;
}

If ($act == "folderok") {
	$IsFolder = getParam("IsFolder");
	$wFileName = getParam("wname");
	$wFile = "";
	$wFileSize = 0;
	$wDate = time();
	@mysqli_autocommit($db, false);
	$err = false;
	try {
		$stmt = @mysqli_prepare($db, "insert into tFileDB (wDepth, RefID, IsFolder, wFileName, wFileSize, wFile, wDate, mID) values (?, ?, ?, ?, ?, ?, ?, ?)");
		@mysqli_stmt_bind_param($stmt, 'iissisii', $wDepth, $RefID, $IsFolder, $wFileName, $wFileSize, $wFile, $wDate, $userno);
		$result = @mysqli_stmt_execute($stmt);
		if (!$result) {
			throw new Exception(mysqli_error($db));
		}
		@mysqli_stmt_close($stmt);
	} catch(Exception $e)  {
		$err = true;
		$errMsg = $base_page . ": " . $e->getmessage();
		@systemLog($errMsg);
	}
	if ($err == false) {
		@mysqli_commit($db);
	} else {
		@mysqli_rollback($db);
		?>
		<script type="text/javascript">
			alert('<?=$msg_commit_error?>'); history.go(-1);
		</script>
		<?
	}
	@mysqli_autocommit($db, true);
}

If ($act == "fileok") {
	$gFile = file_upload("wname", $max_upload_size, $actt);
	if ($gFile != "n" && $gFile != "") {
		$file_info = explode("|", $gFile);
		$wFile = $file_info[0];
		$wFileName = $file_info[1];
		$wFileSize = $file_info[2];
		$IsFolder = getParam("IsFolder");
		$wDate = time();
		@mysqli_autocommit($db, false);
		$err = false;
		try {
			$stmt = @mysqli_prepare($db, "insert into tFileDB (wDepth, RefID, IsFolder, wFileName, wFileSize, wFile, wDate, mID) values (?, ?, ?, ?, ?, ?, ?, ?)");
			@mysqli_stmt_bind_param($stmt, 'iissisii', $wDepth, $RefID, $IsFolder, $wFileName, $wFileSize, $wFile, $wDate, $userno);
			$result = @mysqli_stmt_execute($stmt);
			if (!$result) {
				throw new Exception(mysqli_error($db));
			}
			@mysqli_stmt_close($stmt);
		} catch(Exception $e)  {
			$err = true;
			$errMsg = $base_page . ": " . $e->getmessage();
			@systemLog($errMsg);
		}

		if ($err == false) {
			@mysqli_commit($db);
		} else {
			@mysqli_rollback($db);
			$wFile = $_SERVER['DOCUMENT_ROOT'] . $wFile;
			@unlink($wFile);
			?>
			<script type="text/javascript">
				alert('<?=$msg_commit_error?>'); history.go(-1);
			</script>
			<?
		}
		@mysqli_autocommit($db, true);
	}
}

if ($act == "del") {
	$uid = getNumber(getParam("uID"));
	$stmt = @mysqli_prepare($db, "select wFile, IsFolder from tFileDB where ID=? and mID=?");
	@mysqli_stmt_bind_param($stmt, 'ii', $uid, $userno);
	@mysqli_stmt_execute($stmt);
	@mysqli_stmt_bind_result($stmt, $wFile, $IsFolder);
	@mysqli_stmt_fetch($stmt);
	@mysqli_stmt_close($stmt);
	@mysqli_autocommit($db, false);
	$err = false;
	try {
		$stmt = @mysqli_prepare($db, "delete from tFileDB where ID=? and mID=?");
		@mysqli_stmt_bind_param($stmt, 'ii', $uid, $userno);
		$result = @mysqli_stmt_execute($stmt);
		if (!$result) {
			throw new Exception(mysqli_error($db));
		}
		@mysqli_stmt_close($stmt);
	} catch(Exception $e)  {
		$err = true;
		$errMsg = $base_page . ": " . $e->getmessage();
		@systemLog($errMsg);
	}

	if ($err == false) {
		@mysqli_commit($db);
		If ($wFile != "" && $IsFolder == $FolderNo) {
			$wFile = $_SERVER['DOCUMENT_ROOT'] . $wFile;
			@unlink($wFile);
		}
	} else {
		@mysqli_rollback($db);
		?>
		<script type="text/javascript">
			alert('<?=$msg_commit_error?>'); history.go(-1);
		</script>
		<?
	}
	@mysqli_autocommit($db, true);
}

if ($lang == "k") {
	$imgPrefix = "";
} else {
	$imgPrefix = "2";
}
?>
<table width="450" border="0" cellpadding="3" cellspacing="0" align="center"><tr><td colspan="3"><img src="/images/<?=$actt?>list<?=$imgPrefix?>.gif"></td></tr>
<form method="post" name="fcreate" action="<?=$PHP_SELF?>?mid=<?=$mid?>&cmid=<?=$cmid?>&lang=<?=$lang?>">
<tr><td colspan="3"><img src="/images/folder_create<?=$imgPrefix?>.gif" align="absmiddle"> <input type="text" name="wname" id="wname" size="20" class="solid"> <a href="#" OnClick="Javascript: if (validateFolder('wname', 'Y')) { showLoader(); document.fcreate.submit(); } return flase;"><img src="/images/folder_createok<?=$imgPrefix?>.gif" border="0" align="absmiddle"></a><input type="hidden" name="IsFolder" value="<?=$FolderYes?>"><input type="hidden" name="actt" value="<?=$actt?>"><input type="hidden" name="wDepth" value="<?=$wDepth?>"><input type="hidden" name="RefID" value="<?=$RefID?>"><input type="hidden" name="act" value="folderok"></td></tr></form>
<form method="post" name="fupload" action="<?=$PHP_SELF?>?mid=<?=$mid?>&cmid=<?=$cmid?>&lang=<?=$lang?>" ENCTYPE="MULTIPART/FORM-DATA">
<tr><td colspan="3"><img src="/images/file_upload<?=$imgPrefix?>.gif" align="absmiddle"> <input type="file" name="wname" id="wfile" class="solid"> <a href="#" OnClick="Javascript: if (FileUploadCheck('wfile', 'Y', 1, '<?=$actt?>', <?=$max_upload_size?>)) { showLoader(); document.fupload.submit(); } return false;"><img src="/images/file_uploadok<?=$imgPrefix?>.gif" border="0" align="absmiddle"></a><input type="hidden" name="IsFolder" value="<?=$FolderNo?>"><input type="hidden" name="actt" value="<?=$actt?>"><input type="hidden" name="wDepth" value="<?=$wDepth?>"><input type="hidden" name="RefID" value="<?=$RefID?>"><input type="hidden" name="act" value="fileok"></td></tr></form>
<?
//폴더 리스트

If ($wDepth > 1) {
	$pRefID = $RefID;
	$aCnt = 1;
	$stmt = @mysqli_prepare($db, "select wFileName, ID, wDepth, RefID from tFileDB where IsFolder=? and wDepth <= ? and mID=? order by wDepth DESC");
	@mysqli_stmt_bind_param($stmt, 'sii', $FolderYes, $wDepth, $userno);
	@mysqli_stmt_execute($stmt);
	@mysqli_stmt_store_result($stmt);
	@mysqli_stmt_bind_result($stmt, $wFileName, $uID, $wDepth2, $RefID2);
	if (@mysqli_stmt_num_rows($stmt) > 0) {
		while (@mysqli_stmt_fetch($stmt)) {
			$aID = $uID;
			$mDepth = $wDepth2;
			If ($aID == $pRefID) {
				$aFileName = $wFileName;
				$aDepth[$mDepth-1] = $wDepth2;
				$aRefID[$mDepth-1] = $uID;
				$FolderName[$mDepth-1] = $aFileName;
				$aCnt++;
				$pRefID = $RefID2;
			}
		}
	}
	?>
	<tr><td colspan="3" style="padding:5px;"><img src="/images/bullet.gif" border="0" align="absmiddle"> <?=$msg_browser_current?>: <a href="#" OnClick="Javascript: document.ffmove.RefID.value=''; document.ffmove.wDepth.value='1'; document.ffmove.submit(); return false;"><?=$msg_browser_root?></a>&nbsp;-&nbsp;
	<?
	for ($i=0 ; $i < sizeof($FolderName); $i++) {
		if ($i < sizeof($FolderName) - 1) {
			$StrNext = "&nbsp;-&nbsp;";
			$StrLink = "<a href=\"#\" OnClick=\"Javascript: document.ffmove.RefID.value='" . $aRefID[$i] . "'; document.ffmove.wDepth.value='" . ($aDepth[$i]+1) . "'; document.ffmove.submit(); return false;\">";
			$StrLink2 = "</a>";
		} else {
			$StrNext = "";
			$StrLink = "";
			$StrLink2 = "";
		}
		?>
		<?=$StrLink?><?=htmlspecialchars($FolderName[$i])?><?=$StrLink2?><?=$StrNext?>
		<?
	}
	?>
	</td></tr>
	<?
} else {
	?>
	<tr><td colspan="3" style="padding:5px;"><img src="/images/bullet.gif" border="0" align="absmiddle"> <?=$msg_browser_current?>: <?=$msg_browser_root?></td></tr>
	<?
}

//파일 목록
If ($wDepth > 1) {
	$pID = "";
	$pDepth = $wDepth - 1;
	$stmt = @mysqli_prepare($db, "select RefID from tFileDB where wDepth=? and ID=? order by IsFolder DESC, wFileName ASC");
	@mysqli_stmt_bind_param($stmt, 'ii', $pDepth, $RefID);
	@mysqli_stmt_execute($stmt);
	@mysqli_stmt_bind_result($stmt, $RefID2);
	@mysqli_stmt_fetch($stmt);
	@mysqli_stmt_close($stmt);
	if ($RefID2 != "") {
		$pID = $RefID2;
	}
	?>
	<tr><td><img src="/images/FolderUp.gif" border="0" align="absmiddle"> <a href="#" OnClick="Javascript: document.ffmove.RefID.value='<?=$pID?>'; document.ffmove.wDepth.value='<?=$wDepth-1?>'; document.ffmove.submit(); return false;">..</a> </td><td>&nbsp;</td></tr> 
	<?
}
$stmt = @mysqli_prepare($db, "select ID, wDepth, RefID, wFileName, wFile, wFileSize, IsFolder, wDate, B.FileCnt from tFileDB left join (select count(RefID) as FileCnt, RefID as FileID from tFileDB group by RefID) B on B.FileID=tFileDB.ID where mID=? and wDepth=? and RefID=? order by IsFolder ASC, wFileName ASC");
@mysqli_stmt_bind_param($stmt, 'iii', $userno, $wDepth, $RefID);
@mysqli_stmt_execute($stmt);
@mysqli_stmt_store_result($stmt);
@mysqli_stmt_bind_result($stmt, $uID, $wDepth, $RefID, $wFileName, $wFile, $wFileSize, $IsFolder, $wDate, $FileCnt);
if (@mysqli_stmt_num_rows($stmt) > 0) {
	while (@mysqli_stmt_fetch($stmt)) {
		$wID = $uID;
		$wDepth2 = $wDepth;
		$wRefID2 = $RefID;
		$wFileName = $wFileName;
		$wFile = $wFile;
		$wFileSize = $wFileSize;
		$IsFolder = $IsFolder;
		$FileCnt = $FileCnt;
		if ($FileCnt == "") {
			$FileCnt = 0;
		}

		if ($FileCnt == 0 || $IsFolder == $FolderNo) {
			$StrDelete = "<a href=\"#\" OnClick=\"Javascript: if (confirm('" . $msg_delete_check . "')) {document.ffdel.uID.value='" . $wID . "'; document.ffdel.RefID.value='" . $RefID . "'; document.ffdel.wDepth.value='" . $wDepth . "'; document.ffdel.submit();} return false;\"><img src=\"/images/file_delete" . $imgPrefix . ".gif\" border=\"0\" align=\"absmiddle\"></a>";
		} else {
			$StrDelete = "<a href=\"#\" OnClick=\"Javascript: alert('" . $msg_delete_folder . "'); return false;\"><img src=\"/images/file_delete" . $imgPrefix . ".gif\" border=\"0\" align=\"absmiddle\"></a>";
		}
		if ($IsFolder == $FolderNo) {
			$temp_ext = explode(".", $wFileName);
			$myfile_ext = $temp_ext[sizeof($temp_ext)-1];
			$myfile_ext = strtolower($myfile_ext);
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/images/ext_" . $myfile_ext . ".gif")) {
				$strImg = "/images/ext_" . $myfile_ext . ".gif";
			} else {
				$strImg = "/images/ext_default.gif";
			}
			if ($wFileSize > 1024000) {
				$FileSize = number_format($wFileSize/1024000,2) . "MB";
			} else {
				$FileSize = number_format($wFileSize/1024,2) . "KB";
			}
			if ($actt == "image" && ($myfile_ext == "jpg" || $myfile_ext == "jpeg" || $myfile_ext == "gif" || $myfile_ext == "png")) {
				?>
				<tr><td width="320" style="font-size:11px;"><img src="<?=$strImg?>" border="0" align="absmiddle"> <a href="#" onClick="Javascript:OpenFile('<?=$wFile?>');"  style="font-size:11px;"><?=htmlspecialchars($wFileName)?></a> (<?=$FileSize?>)</td><td align="center" width="75" style="font-size:11px;">(<?=date('Y-m-d',$wDate)?>)</td><td align="center" width="60"><?=$StrDelete?></td></tr>
				<?
			} else if ($actt == "file") {
				?>
				<tr><td width="320" style="font-size:11px;"><img src="<?=$strImg?>" border="0" align="absmiddle"> <a href="#" onClick="Javascript:OpenFile('/downloadfile.php?mid=<?=$cmid?>&fid=<?=$wID?>');"  style="font-size:11px;"><?=htmlspecialchars($wFileName)?></a> (<?=$FileSize?>)</td><td align="center" width="75" style="font-size:11px;">(<?=date('Y-m-d',$wDate)?>)</td><td align="center" width="60"><?=$StrDelete?></td></tr>
				<?
			}
		} else if ($IsFolder == $FolderYes) {
			?>
			<tr><td width="320" style="font-size:11px;"><img src="/images/Folder.gif" border="0" align="absmiddle"> <a href="#" OnClick="Javascript: document.ffmove.RefID.value='<?=$wID?>'; document.ffmove.wDepth.value='<?=$wDepth2+1?>'; document.ffmove.submit(); return false;"  style="font-size:11px;"><?=htmlspecialchars($wFileName)?></a></td><td align="center" width="75" style="font-size:11px;">(<?=date('Y-m-d',$wDate)?>)</td><td align="center" width="60" align="center"><?=$StrDelete?></td></tr>
			<?
		}
	}
}
?>
<form name="ffmove" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&cmid=<?=$cmid?>&lang=<?=$lang?>">
<input type="hidden" name="actt" value="<?=$actt?>">
<input type="hidden" name="RefID" value="">
<input type="hidden" name="wDepth" value="">
</form>
<form name="ffdel" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&cmid=<?=$cmid?>&lang=<?=$lang?>">
<input type="hidden" name="act" value="del">
<input type="hidden" name="uID" value="">
<input type="hidden" name="RefID" value="">
<input type="hidden" name="wDepth" value="">
<input type="hidden" name="actt" value="<?=$actt?>">
</form>
</table>
<?
include ($_SERVER['DOCUMENT_ROOT'] . '/include/ose_loader.php');
?>
</body>
</html>
<?
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db_close.php');
?>