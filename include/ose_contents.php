<?
$act = getParam("act");
if (!$act) {
	$act = "view";
}
$actt = getParam("actt");

$uid = getParam("uid");

if (isset($cmid) && $cmid == "") {
	$cmid = getParam("cmid");
}

if (!$cmid) {
	$cmid = $mid;
}

$mAuth_Read_Check = "Y";

if ($userlevel >= "7") {
	$mAuth_Write_Check = "Y";
	$mAuth_Delete_Check = "Y";
}

if ($base_page != "oseadmin.php") {
	$mAuth_Write_Check = "N";
	$mAuth_Delete_Check = "N";
}

switch ($act) {
	case "view":
		if ($mAuth_Read_Check == "Y") {
			if ($actt == "mok") {
				$tmtitle = getParam("tmtitle");
				$tmtext = getParam("tmtext");
				@mysqli_autocommit($db, false);
				$err = false;
				try {
					$stmt = @mysqli_prepare($db, "update tContents Set cTitle=?, cText=? where ID=?");
					@mysqli_stmt_bind_param($stmt, 'ssi', $tmtitle, $tmtext, $uid);
					$result = @mysqli_stmt_execute($stmt);
					if (!$result) {
						throw new Exception(mysqli_error($db));
					}
					@mysqli_stmt_close($stmt);
				} catch(Exception $e) {
					$err = true;
					$errMsg = $base_page . ": " . $e->getmessage();
					@systemLog($errMsg);
				}
				if ($err == false) {
					@mysqli_commit($db);
					@addLog ($userno, "콘텐츠 수정 : " . $cmid, $db);
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
			
			if ($actt == "wok") {
				$tmtitle = getParam("tmtitle");
				$tmtext = getParam("tmtext");
				@mysqli_autocommit($db, false);
				$err = false;
				try {
					$stmt = @mysqli_prepare($db, "insert into tContents (cTitle, cText, cDate, menuID) values (?, ?, ?, ?)");
					@mysqli_stmt_bind_param($stmt, 'ssis', $tmtitle, $tmtext, time(), $cmid);
					$result = @mysqli_stmt_execute($stmt);
					if (!$result) {
						throw new Exception(mysqli_error($db));
					}
					@mysqli_stmt_close($stmt);
					@addLog ($userno, "콘텐츠 추가 : " . $cmid, $db);
				} catch(Exception $e) {
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
			
			$mText = "";
			$stmt = @mysqli_prepare($db, "select ID, cText from tContents where menuID=?");
			@mysqli_stmt_bind_param($stmt, 's', $cmid);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_store_result($stmt);
			@mysqli_stmt_bind_result($stmt, $uid, $mText);
			@mysqli_stmt_fetch($stmt);
			@mysqli_stmt_close($stmt);

			if ($mText == "") {
				if ($mText == "") {
					$mText = "<p>" . $msg_error_notext . "</p><p>&nbsp;</p>";
				}
				if ($mAuth_Write_Check == "Y") {
					?>
					<p style="text-align:right;"><a href="<?=$PHP_SELF?>?mid=<?=$mid?>&act=write&cmid=<?=$cmid?>&sOpt=<?=urlencode($sOpt)?>" class="button icon add"><?=$msg_btn_add?></a></p>
					<?
				}
			} else {
				echo $mText;
				?>
				<p style="text-align:right;">
				<?
				if ($mAuth_Write_Check == "Y") {
					?>
					<a href="<?=$PHP_SELF?>?mid=<?=$mid?>&act=modify&cmid=<?=$cmid?>&uid=<?=$uid?>&sOpt=<?=urlencode($sOpt)?>" class="button icon edit"><?=$msg_btn_edit?></a>
					<?
				}
				?>
				<?
				if ($mAuth_Delete_Check == "Y") {
					?>
					<a href="<?=$PHP_SELF?>?mid=<?=$mid?>&act=delete&cmid=<?=$cmid?>&uid=<?=$uid?>&sOpt=<?=urlencode($sOpt)?>"  OnClick="Javascript: if (confirm('<?=$msg_delete_check?>')) { } else {return false;}" class="button danger icon trash" style="margin-left:5px;"><?=$msg_btn_delete?></a>
					<?
				}
			}
		}
		break;
	case "write":
		if ($mAuth_Write_Check == "Y") {
			$stmt = @mysqli_prepare($db, "select mTitle from tMenu where menuID=?");
			@mysqli_stmt_bind_param($stmt, 's', $cmid);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_store_result($stmt);
			@mysqli_stmt_bind_result($stmt, $mTitle);
			@mysqli_stmt_fetch($stmt);			
			?>
			<form name="contentsadd"  method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
			<input type="hidden" name="act" value="view">
			<input type="hidden" name="actt" value="wok">
			<input type="hidden" name="cmid" value="<?=$cmid?>">
			<input type="hidden" name="sOpt" value="<?=urlencode($sOpt)?>">
			<div class="admViewTitleBg">
				<span class="admLabel"><?=$msg_lbl_subject?></span><span class="admLabelBox"><input type="text" name="tmtitle" id="tmtitle" style="width:95%;" maxlength="150" value="<?=$mTitle?>"></span>
			</div>
			<div class="admContentBg">
				<textarea id="tmtext" name="tmtext"></textarea>
			</div>
			<p align="center"><a href="#" OnClick="Javascript: showLoader(); document.contentsadd.submit(); return false;" class="button icon approve"><?=$msg_btn_save?></a>
			</form>
			<?
		}
		TinyMCEInit ($userlevel, "100%", $editor_css, $mid, $cmid, $lang);
		break;
	case "modify":
		if ($mAuth_Write_Check == "Y") {
			
			$stmt = @mysqli_prepare($db, "select ID, cText, cTitle from tContents where menuID=?");
			@mysqli_stmt_bind_param($stmt, 's', $cmid);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_store_result($stmt);
			@mysqli_stmt_bind_result($stmt, $uid, $mText, $mTitle);
			@mysqli_stmt_fetch($stmt);
			@mysqli_stmt_close($stmt);
			?>
			<form name="contentsadd"  method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
			<input type="hidden" name="act" value="view">
			<input type="hidden" name="actt" value="mok">
			<input type="hidden" name="cmid" value="<?=$cmid?>">
			<input type="hidden" name="sOpt" value="<?=urlencode($sOpt)?>">
			<input type="hidden" name="uid" value="<?=$uid?>">
			<div class="admViewTitleBg">
				<span class="admLabel"><?=$msg_lbl_subject?></span><span class="admLabelBox"><input type="text" name="tmtitle" id="tmtitle" style="width:95%;" maxlength="150" value="<?=$mTitle?>"></span>
			</div>
			<div class="admContentBg">
				<textarea id="tmtext" name="tmtext"><?=$mText?></textarea>
			</div>
			<p align="center"><a href="#" OnClick="Javascript: showLoader(); document.contentsadd.submit(); return false;" class="button icon approve"><?=$msg_btn_save?></a>
			</form>
			<?
		}
		TinyMCEInit ($userlevel, "100%", $editor_css, $mid, $cmid, $lang);
		break;
	case "delete":
		if ($mAuth_Delete_Check == "Y") {
			@mysqli_autocommit($db, false);
			$err = false;
			try {
				$stmt = @mysqli_prepare($db, "delete from tContents where ID=?");
				@mysqli_stmt_bind_param($stmt, 'i', $uid);
				$result = @mysqli_stmt_execute($stmt);
				@mysqli_stmt_close($stmt);
			} catch(Exception $e) {
				$err = true;
				$errMsg = $base_page . ": " . $e->getmessage();
				@systemLog($errMsg);
			}

			if ($err == false) {
				@mysqli_commit($db);
				@addLog ($userno, "콘텐츠 삭제 : " . $cmid, $db);
				?>
				<script type="text/javascript">
					window.location='<?=$PHP_SELF?>?mid=<?=$mid?>&cmid=<?=$cmid?>&sOpt=<?=$sOpt?>';
				</script>
				<?
			} else {
				@mysqli_rollback($db);
				?>
				<script type="text/javascript">
					alert('<?=$msg_commit_error?>'); history.go(-1);
				</script>
				<?
			}
		}
		break;
}
?>