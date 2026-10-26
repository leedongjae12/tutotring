<?php
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_session.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util_core.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_lang.php');


		$CanRead = "Y";

		$CanWrite = "Y";

		$CanDelete = "Y";

		// 첨부파일 업로드 최대 크기: 10MB
		$upload_limit_mb = 2;
		$upload_limit_bytes = $upload_limit_mb * 1024 * 1024;

		// 클라이언트 검사는 우회할 수 있으므로 서버에서도 실제 업로드 크기를 확인합니다.
		function isUploadSizeExceeded($fieldName, $maxBytes) {
			if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
				return false;
			}

			$error = isset($_FILES[$fieldName]["error"]) ? (int)$_FILES[$fieldName]["error"] : UPLOAD_ERR_NO_FILE;

			if ($error === UPLOAD_ERR_NO_FILE) {
				return false;
			}

			// php.ini 또는 MAX_FILE_SIZE 제한에 걸린 경우도 크기 초과로 처리
			if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
				return true;
			}

			if ($error !== UPLOAD_ERR_OK) {
				return false;
			}

			return isset($_FILES[$fieldName]["size"]) && (int)$_FILES[$fieldName]["size"] > $maxBytes;
		}


?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?=$html_title?></title>
	<link rel="icon" type="image/x-icon" href="/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?=$html_css?>?v=1" />
	<link rel="stylesheet" type="text/css" href="/css/gh-buttons.css" />
	<link rel="stylesheet" type="text/css" href="/script/datepicker/datepicker.css" />
	<script type="text/javascript" src="/script/jquery.min.js"></script>
	<script type="text/javascript" src="/script/input.js"></script>
	<script type="text/javascript" src="/script/datepicker/datepicker.js"></script>
	<script type="text/javascript" src="/script/datepicker/datepicker_ko.js"></script>
	<script type="text/javascript" src="/script/bpopup/jquery.bpopup.js"></script>
	<script type="text/javascript">
		jQuery(window).load(function() {
			hideLoader();
		});

		function checkFileUploadSize(input) {
			var maxBytes = <?=$upload_limit_bytes?>;
			var maxMB = <?=$upload_limit_mb?>;

			if (input && input.files && input.files.length > 0 && input.files[0].size > maxBytes) {
				alert("파일 최대 업로드 사이즈는 " + maxMB + "MB입니다. 업로드 가능한 파일 크기를 초과했습니다.");
				input.value = "";
				return false;
			}

			return true;
		}
	</script>
</head>
<body>
	<div id="wrapper_pop">
		<?php
		if ($CanRead == "N" ) {
			?>
			<script type="text/javascript">
				parent.window.location="./index.php";
			</script>
			<?php
		} else {
			$oCampus = $_SESSION["oCampus"];
			switch ($pact) {
				case "tutorinfo":
					//튜터정보
					$mSemester = getParam("mSemester");
					$mProgram =  getNumber(getParam("mProgram"));
					if ($userlevel >= "7") {
						$oCampus = getParam("mCampus");
					}
					?>
					<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=htmlspecialchars($app_label_tutorinfo)?></b></p>
					<?php
					$stmt = @mysqli_prepare($db, "select tStudent.sHakbun, (case when '" . $lang . "'='k' Then tStudent.sName Else tStudent.sNameE End), tStudent.sName, tStudent.sNameE, dbdec(?, tStudent.sContact), dbdec(?, tStudent.sEmail), tMember.mSosok, getCodeName('SOSOK', tMember.mSosok, '', '" . $lang . "'), tMember.mSosokName, tMember.mGrade, getCodeName('GRADE', tMember.mGrade, tMember.mSosok, '" . $lang . "'), tMember.mOpen, tMember.mCredit, getCodeName('CREDIT', tMember.mCredit, '', '" . $lang . "'), tMember.mFile, tMember.mFileName, tMember.mFileSize, tMember.mService, tMember.mPurpose, tMember.mMaterial, tMember.mDesc, tMember.mTime, tMember.mRemark, tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, tMember.mGubun, tMember.mCredit, tMember.mFile, tMember.mFileName, tMember.mFileSize, tMember.mService, tMember.ID from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID inner join tStudent on tMember.sID=tStudent.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? and tTutoring.ID=? and tMember.mGubun='A'");
					@mysqli_stmt_bind_param($stmt, 'ssssii', $enc_key, $enc_key, $oCampus, $mSemester, $mProgram, $uid);
					@mysqli_stmt_execute($stmt);
					@mysqli_stmt_store_result($stmt);
					@mysqli_stmt_bind_result($stmt, $sHakbun, $sName, $sNameK, $sNameE, $sContact, $sEmail, $mSosok, $StrSosok, $mSosokName, $mGrade, $StrGrade, $mOpen, $mCredit, $StrCredit, $mFile, $mFileName, $mFileSize, $mService, $mPurpose, $mMaterial, $mDesc, $mTime, $mRemark, $tTitle, $tProfessor, $oGubun, $mGubun, $mCredit, $mFile, $mFileName, $mFileSize, $mService, $memID);
					@mysqli_stmt_fetch($stmt);
					@mysqli_stmt_close($stmt);
					if ($userlevel >= "7") {
						?>
						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_forma_1)?></th><td><?=htmlspecialchars($sHakbun)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_2)?></th><td><?=htmlspecialchars($sNameK)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_3)?></th><td><?=htmlspecialchars($sNameE)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_4)?></th><td><?=htmlspecialchars($sEmail)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_5)?></th><td><?=htmlspecialchars($sContact)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_6)?></th><td><?=makeInput($db, "N", "radiolist", "SOSOK", "mSosok", "mSosok", "", "", $mSosok, "Y", "", "", "")?> <?=htmlspecialchars($mSosokName)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_7)?></th><td><?=makeInput($db, "N", "select", "GRADE", "mGrade", "mGrade", "", "", $mGrade, "Y", $mSosok, $msg_select, "")?></td>
							</tr>
						</table>

						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_formb_0)?></th><td><?=makeInput($db, "N", "select", "OGUBUN", "oGubun", "oGubun", "", "", $oGubun, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_1)?></th><td><?=makeInput($db, "N", "text", "", "tTitle", "tTitle", " style=\"width:60%;\" maxlength=\"150\" " . $oRead, "", $tTitle, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_2)?></th><td><?=makeInput($db, "N", "text", "", "tProfessor", "tProfessor", " style=\"width:100px; maxlength=\"33\" " . $oRead, "", $tProfessor, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_3)?></th><td><?=makeInput($db, "N", "select", "MGUBUN", "mGubun", "mGubun", "", "", $mGubun, "", "", "", "")?></td>
							</tr>
							<tr id="tutorinfoa">
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_4)?></th><td><?=makeInput($db, "N", "select", "CREDIT", "mCredit", "mCredit", "", "", $mCredit, "", "", "", "")?></td>
							</tr>
							<tr id="tutorinfob">
								<?php
								$temp_ext = explode(".", $mFileName);
								$myfile_ext = $temp_ext[sizeof($temp_ext)-1];	
								$myfile_ext = strtolower($myfile_ext);
								if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/images/ext_" . strtolower($myfile_ext) . ".gif")) {
									$img_ext = "/images/ext_" . strtolower($myfile_ext) . ".gif";
								} else {
									$img_ext = "/images/ext_default.gif";
								}
								if ($mFileSize > 1024000) {
									$fsize = number_format($mFileSize/1024000,2) . "MB";
								} else {
									$fsize = number_format($mFileSize/1024,2) . "KB";
								}
								if ($mFile != "") {
									$StrFile =  "<a href=\"/downloadfile.php?mid=" . $mid . "&lang=" . $lang . "&uid=" . $memID . "\"><img src=\"" . $img_ext . "\" border=\"0\" align=\"absmiddle\"> " . htmlspecialchars($mFileName) . "(" . $fsize . ") </a>";
								} else {
									$StrFile = "";
								}
								?>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_5)?></th><td><?=$StrFile?></td>
							</tr>
							<!--<tr id="tutorinfoc">
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_6)?></th><td><?=makeInput($db, "N", "checkbox", "", "mService", "mService", "", "", $mService, "", "", "", "")?> <?=htmlspecialchars($app_label_formb_7)?></td>
							</tr>-->
						</table>
						<?php
					}
					?>
					<table class="ContentsTableGray" width="100%">
						<tr>
							<th width="130"><?=htmlspecialchars($app_label_formb_1)?></th><td><?=nl2br(htmlspecialchars($tTitle))?></td>
						</tr>
						<tr>
							<th class="thnormal"><?=htmlspecialchars($app_label_formb_2)?></th><td><?=nl2br(htmlspecialchars($tProfessor))?></td>
						</tr>
						<tr>
							<th class="thnormal"><?=htmlspecialchars($app_label_formc_1)?></th><td><?=nl2br(htmlspecialchars($mPurpose))?></td>
						</tr>
						<tr>
							<th class="thnormal"><?=htmlspecialchars($app_label_formc_2)?></th><td><?=nl2br(htmlspecialchars($mMaterial))?></td>
						</tr>
						<tr>
							<th class="thnormal"><?=htmlspecialchars($app_label_formc_3)?></th><td><?=nl2br(htmlspecialchars($mDesc))?></td>
						</tr>
						<tr>
							<th class="thnormal"><?=htmlspecialchars($app_label_formc_4)?></th><td><?=nl2br(htmlspecialchars($mTime))?></td>
						</tr>
						<tr>
							<th class="thnormal"><?=htmlspecialchars($app_label_formc_5)?></th><td><?=nl2br(htmlspecialchars($mRemark))?></td>
						</tr>
					</table>
					<p align="center"><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
					<?php
					if ($userlevel >= 7) {
						@addLog ($userno, "튜터정보 조회 [" . $oCampus . "] " . $mSemester  . " - " . $mProgram . " : " . $sHakbun, $db);
					}
					break;
				case "tuteeinfo":
					//튜티정보
					$mSemester = getParam("mSemester");
					$mProgram =  getNumber(getParam("mProgram"));
					?>
					<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=htmlspecialchars($app_label_tuteeinfo)?></b> (<?=htmlspecialchars(getTutoringInfo($db, $uid))?>)</p>
					<table class="ContentsTableGray" width="100%">
					<?php
					if ($userlevel >= "7") {
						$oCampus = getParam("mCampus");
						?>
						<tr>
							<th width="130">학번</th><th width="130">이름</th><th>구분</th><th>소속</th><th>연락처</th><th>이메일</th>
						</tr>
						<?php
					} else {
						?>
						<tr>
							<th width="130"><?=htmlspecialchars($app_label_forma_1)?></th><th width="130"><?=htmlspecialchars($app_label_forma_2a)?></th><th><?=htmlspecialchars($app_label_forma_6)?></th>
						</tr>
						<?php
					}
					$stmt = @mysqli_prepare($db, "select tStudent.sHakbun, (case when '" . $lang . "'='k' Then tStudent.sName Else tStudent.sNameE End), dbdec(?, tStudent.sContact), dbdec(?, tStudent.sEmail), tMember.mSosok, getCodeName('SOSOK', tMember.mSosok, '', '" . $lang . "'), tMember.mSosokName, tMember.mGrade, getCodeName('GRADE', tMember.mGrade, tMember.mSosok, '" . $lang . "'), tMember.mOpen, tTutoring.tTitle, tTutoring.tProfessor from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID inner join tStudent on tMember.sID=tStudent.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? and tTutoring.ID=? and tMember.mGubun='B'");
					@mysqli_stmt_bind_param($stmt, 'ssssii', $enc_key, $enc_key, $oCampus, $mSemester, $mProgram, $uid);
					@mysqli_stmt_execute($stmt);
					echo mysqli_error($db);
					@mysqli_stmt_store_result($stmt);
					@mysqli_stmt_bind_result($stmt, $sHakbun, $sName, $sContact, $sEmail, $mSosok, $StrSosok, $mSosokName, $mGrade, $StrGrade, $mOpen, $tTitle, $tProfessor);
					while (@mysqli_stmt_fetch($stmt)) {
						if ($userlevel >= 7) {
							?>
							<tr>
								<td style="text-align:center;"><?=htmlspecialchars($sHakbun)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($sName)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($StrSosok)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($mSosokName)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($sContact)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($sEmail)?></td>
							</tr>
							<?php
							@addLog ($userno, "튜티정보 조회 [" . $oCampus . "] " . $mSemester  . " - " . $mProgram . " : " . $sHakbun, $db);
						} else {
							$sHakbun = substr($sHakbun,0,7) . "****";
							?>
							<tr>
								<td style="text-align:center;"><?=htmlspecialchars($sHakbun)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($sName)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($mSosokName)?></td>
							</tr>
							<?php
						}
					}
					@mysqli_stmt_close($stmt);
					?>
					</table>
					<p align="center"><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
					<?php
					break;
				case "applytutor":
					//튜터신청
					$mSemester = getParam("mSemester");
					$mProgram =  getNumber(getParam("mProgram"));
					$CanApply = getPeriodCheck($db, $mProgram);

					if (true) {
						if ($actt == "mok" && $CanApply == "Y" && $uid > 0 && $mProgram > 0) {
							//정원 체크
							$stmt = @mysqli_prepare($db, "select (select count(ID) from tMember where tID=tTutoring.ID and mGubun='A'), (select count(ID) from tMember where tID=tTutoring.ID and mGubun='B' and sID=?), 1, (select count(MM.ID) from tMember MM inner join tTutoring TT on MM.tID=TT.ID inner join tProgram PG on TT.pID=PG.ID where MM.sID=? and PG.pCampus=? and PG.pSemester=? and PG.ID=?) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? and tTutoring.ID=?");
							@mysqli_stmt_bind_param($stmt, 'iississii', $userno, $userno, $oCampus, $mSemester, $mProgram, $oCampus, $mSemester, $mProgram, $uid);
							@mysqli_stmt_execute($stmt);
							@mysqli_stmt_store_result($stmt);
							@mysqli_stmt_bind_result($stmt, $sCnt, $aCnt, $sMax, $mCnt);
							@mysqli_stmt_fetch($stmt);
							@mysqli_stmt_close($stmt);
							if ($mCnt > 2) {
								?>
								<script type="text/javascript">
									alert("<?=$app_label_max?>");
									histroy.go(-1);
								</script>
								<?php
							} elseif ($aCnt > 0) {
								?>
								<script type="text/javascript">
									alert("<?=$app_apply_alreadytutee?>");
									histroy.go(-1);
								</script>
								<?php
							} elseif ($sCnt >= $sMax) {
								?>
								<script type="text/javascript">
									alert("<?=$app_apply_quota?>");
									histroy.go(-1);
								</script>
								<?php
							} else {
								$mName = getParam("mName");
								$mNameE = getParam("mNameE");
								$mContact = getParam("mContact");
								$mEmail = getParam("mEmail");
								$mSosok = getParam("mSosok");
								$mSosokName = getParam("mSosokName");
								$mGrade = getParam("mGrade");
								$mCredit = getParam("mCredit");
								$mService = getYN(getParam("mCredit"));
								if (isUploadSizeExceeded("mFile", $upload_limit_bytes)) {
									?>
									<script type="text/javascript">
										alert("파일 최대 업로드 사이즈는 <?=$upload_limit_mb?>MB입니다. 업로드 가능한 파일 크기를 초과했습니다.");
										history.go(-1);
									</script>
									<?php
									exit;
								}
								$gFile = file_upload("mFile", $max_upload_size, "document");
								if ($gFile != "n" && $gFile != "") {
									$file_info = explode("|", $gFile);
									$mFile = $file_info[0];
									$mFileName = $file_info[1];
									$mFileSize = $file_info[2];
								} else {
									$mFile = "";
									$mFileName = "";
									$mFileSize = 0;
								}
								$mPurpose = getParam("mPurpose");
								$mMaterial = getParam("mMaterial");
								$mDesc = getParam("mDesc");
								$mTime = getParam("mTime");
								$mRemark = getParam("mRemark");

								if ($mName != "" && $mNameE != "" && $mContact != "" && $mEmail != "" && $mSosok != "" && $mSosokName != "" && $mGrade != "" && $mCredit != "" && $mFile != "" && $mPurpose != "") {
									@mysqli_autocommit($db, false);
									try {
										$saveDay = time() + getDay($db, $oCampus)*24*60*60;
										$stmt = @mysqli_prepare($db, "insert into tMember (tID, sID, mSosok, mSosokName, mGrade, mOpen, mGubun, mCredit, mFile, mFileName, mFileSize, mService, mPurpose, mMaterial, mDesc, mTime, mRemark, mDate, mDateMax) values (?, ?, ?, ?, ?, 'N', 'A', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?)");
										@mysqli_stmt_bind_param($stmt, 'iissssssissssssi', $uid, $userno, $mSosok, $mSosokName, $mGrade, $mCredit, $mFile, $mFileName, $mFileSize, $mService, $mPurpose, $mMaterial, $mDesc, $mTime, $mRemark, $saveDay);
										$result = @mysqli_stmt_execute($stmt);
										if (!$result) {
											throw new Exception(mysqli_error($db));
										}
										@mysqli_stmt_close($stmt);
										$stmt = @mysqli_prepare($db, "update tStudent Set sName=?, sNameE=?, sContact=dbenc(?, ?), sEmail=dbenc(?, ?), sLogin=? where ID=? and sHakbun=?");
										@mysqli_stmt_bind_param($stmt, 'ssssssiis', $mName, $mNameE, $enc_key, $mContact, $enc_key, $mEmail, $saveDay, $userno, $userid);
										$result = @mysqli_stmt_execute($stmt);
										if (!$result) {
											throw new Exception(mysqli_error($db));
										}
										@mysqli_stmt_close($stmt);
										@mysqli_commit($db);
										?>
										<script type="text/javascript">
											alert('<?=$app_apply_tutor_success?>');
											closePop();
										</script>
										<?php
									} catch(Exception $e) {
										$errMsg = $base_page . ": " . $e->getmessage();
										@systemLog($errMsg);
										@mysqli_rollback($db);
										?>
										<script type="text/javascript">
											alert('<?=$msg_commit_error?>');
											history.go(-1);
										</script>
										<?php
									}
									@mysqli_autocommit($db, true);
								}
							}
						}

						$stmt = @mysqli_prepare($db, "select sName, sNameE, dbdec(?, sContact), dbdec(?, sEmail) from tStudent where ID=? and sHakbun=?");
						@mysqli_stmt_bind_param($stmt, 'ssis', $enc_key, $enc_key, $userno, $userid);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $mName, $mNameE, $mContact, $mEmail);
						@mysqli_stmt_fetch($stmt);
						@mysqli_stmt_close($stmt);

						$mSosok = "A";
						$placeholder = $app_label_forma_6a;

						?>
						<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=htmlspecialchars($app_label_apply_tutor)?></b> (<?=htmlspecialchars(getTutoringInfo($db, $uid))?>)</p>
						<div id="ose_css_icor">
							<div class="ose_icor_head"><a href="#" onclick="Javascript: if (document.getElementById('ose_ico01').style.display=='') { document.getElementById('ose_ico01').style.display='none'; } else { document.getElementById('ose_ico01').style.display=''; } return false" style="text-decoration:none;"><?=htmlspecialchars($app_label_privacy)?><i class="fa fa-search-plus" aria-hidden="true" style="color:#949494; font-size:20px; float:right; margin-top:12px;"></i></a>
							<input type="radio" name="agree1" id="agree1" value="Y">&nbsp;<span style="color:#000000; font-weight:normal;"><?=htmlspecialchars($app_label_agree)?> &nbsp;&nbsp;<input type="radio" name="agree1" value="N">&nbsp;<?=htmlspecialchars($app_label_noagree)?></span>
							</div>
							<div class="ose_icor_box" id="ose_ico01" style="display:none;">
							<?=getContents($db, "m03_" . $oCampus . strtoupper($lang))?>
							</div>
						</div>
						<form name="fform" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>&lang=<?=$lang?>" ENCTYPE="MULTIPART/FORM-DATA">
						<input type="hidden" name="mSemester" value="<?=htmlspecialchars($mSemester)?>">
						<input type="hidden" name="mProgram" value="<?=htmlspecialchars($mProgram)?>">
						<input type="hidden" name="uid" value="<?=htmlspecialchars($uid)?>">
						<input type="hidden" name="actt">
						<input type="hidden" name="vlang" id="lang" value="<?=htmlspecialchars($lang)?>">
						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_forma_1)?></th><td><?=htmlspecialchars($userid)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_2)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mName", "mName", " style=\"width:90%;\" maxlength=\"33\"", "", $mName, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_3)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mNameE", "mNameE", " style=\"width:90%;\" maxlength=\"33\"", "", $mNameE, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_4)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mEmail", "mEmail", " style=\"width:90%;\" maxlength=\"200\"", "", $mEmail, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_5)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mContact", "mContact", " style=\"width:90%;\" maxlength=\"200\" placeholder=\"" . $msg_mobile_format . "\"", "", $mContact, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_6)?></th><td><?=makeInput($db, $CanWrite, "radiolist", "SOSOK", "mSosok", "mSosok", "", "", $mSosok, "Y", "", "", "")?><br><?=makeInput($db, $CanWrite, "text", "", "mSosokName", "mSosokName", " style=\"width:90%;\" maxlength=\"66\" placeholder=\"" . $placeholder . "\"", "", $mSosokName, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_7)?></th><td><?=makeInput($db, $CanWrite, "select", "GRADE", "mGrade", "mGrade", "", "", $mGrade, "Y", $mSosok, $msg_select, "")?></td>
							</tr>
						</table>

						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_formb_1)?></th><td><?=htmlspecialchars(getTutoringInfo($db, $uid))?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_4)?></th><td><?=makeInput($db, $CanWrite, "radiolist", "CREDIT", "mCredit", "mCredit", "", "", $mCredit, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_5)?></th><td><?=makeInput($db, $CanWrite, "file", "", "mFile", "mFile", " style=\"width:90%;\" maxlength=\"200\" onchange=\"checkFileUploadSize(this);\"", "", "", "", "", "", "")?></td>
							</tr>
							<!--<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_6)?></th><td><?=makeInput($db, $CanWrite, "checkbox", "", "mService", "mService", "", "", $mService, "", "", "", "")?> <?=htmlspecialchars($app_label_formb_7)?></td>
							</tr>-->
						</table>
						<p><?=htmlspecialchars($app_label_formc_noti_1)?></p>
						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_formc_1)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mPurpose", "mPurpose", " style=\"width:90%;height:80px;\"", "", $mPurpose, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_2)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mMaterial", "mMaterial", " style=\"width:90%;height:80px;\"", "", $mMaterial, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_3)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mDesc", "mDesc", " style=\"width:90%;height:80px;\"", "", $mDesc, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_4)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mTime", "mTime", " style=\"width:90%;height:80px;\"", "", $mTime, "", "", "", "")?><br><?=htmlspecialchars($app_label_formc_noti_2)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_5)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mRemark", "mRemark", " style=\"width:90%;height:80px;\"", "", $mRemark, "", "", "", "")?><br><?=htmlspecialchars($app_label_formc_noti_3)?></td>
							</tr>
						</table>

						
						</form>
						<p align="center"><?php if ($CanApply == "Y") {?><a href="#" OnClick="Javascript: if (validateAgree('agree1', '<?=$app_label_privacy?>') && validateName('mName', 'Y') && validateName('mNameE', 'Y') && validateEmail('mEmail', 'Y') && validateContact('mContact', 'Y') && validateSosok('mSosokName', 'Y') && validateGrade('mGrade', 'Y') && FileUploadCheck('mFile', 'Y', 1, 'file', '<?=$max_upload_size?>') && validateText('mPurpose', 'Y') && validateText('mMaterial', 'Y') && validateText('mDesc', 'Y') && validateText('mTime', 'Y')) { if (confirm('<?=$app_confirm_tutor?>')) { showLoader(); document.fform.actt.value='mok'; document.fform.submit(); } } return false;" class="button icon approve"><?php } else {?><a href="#" OnClick="Javascript: alert('<?=$app_label_noperiod?>'); return false;" class="button icon approve"><?php }?><?=htmlspecialchars($app_label_app_submit)?></a><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
						<script type="text/javascript">
							$("input:radio[name=mSosok]").click(function() {
								showLoader();
								var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
								var a = $("input:radio[name=mSosok]:checked").val();
								$.ajax({
									type: "POST",
									async: false,
									url: "/include/ose_ajax.php",
									dataType: "html",
									data: "mid=<?=$mid?>&act=getGrade&a="+a+"&lang=<?=$lang?>&ntime="+ntime,
									success: function(response, status, request) {
										$("#mGrade").find("option").remove().end().append(response);
									}
								});
								if (a == "A") {
									document.getElementById("mSosokName").placeholder = "<?=$app_label_forma_6a?>";
								} else {
									document.getElementById("mSosokName").placeholder = "<?=$app_label_forma_6b?>";
								}
								hideLoader();
							});
						</script>
						<?php
					} else {
						?>
						<p><?=$msg_error_permission?></p>
						<p align="center"><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
						<?php
					}
					break;
				case "applytutee":
					//튜티신청
					$mSemester = getParam("mSemester");
					$mProgram =  getNumber(getParam("mProgram"));
					$CanApply = getPeriodCheck($db, $mProgram);

					if (substr($usersosok,1,1) == "Y") {
						if ($actt == "mok" && $CanApply == "Y" && $uid > 0 && $mProgram > 0) {
							//정원 체크
							$stmt = @mysqli_prepare($db, "select (select count(ID) from tMember where tID=tTutoring.ID and mGubun='B'), (select count(ID) from tMember where tID=tTutoring.ID and mGubun='A' and sID=?), tProgram.pMax, (select count(MM.ID) from tMember MM inner join tTutoring TT on MM.tID=TT.ID inner join tProgram PG on TT.pID=PG.ID where MM.sID=? and PG.pCampus=? and PG.pSemester=? and PG.ID=?) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? and tTutoring.ID=?");
							@mysqli_stmt_bind_param($stmt, 'iississii', $userno, $userno, $oCampus, $mSemester, $mProgram, $oCampus, $mSemester, $mProgram, $uid);
							@mysqli_stmt_execute($stmt);
							@mysqli_stmt_store_result($stmt);
							@mysqli_stmt_bind_result($stmt, $sCnt, $aCnt, $sMax, $mCnt);
							@mysqli_stmt_fetch($stmt);
							@mysqli_stmt_close($stmt);
							if ($mCnt > 2) {
								?>
								<script type="text/javascript">
									alert("<?=$app_label_max?>");
									histroy.go(-1);
								</script>
								<?php
							} elseif ($aCnt > 0) {
								?>
								<script type="text/javascript">
									alert("<?=$app_apply_alreadytutor?>");
									histroy.go(-1);
								</script>
								<?php
							} elseif ($sCnt >= $sMax) {
								?>
								<script type="text/javascript">
									alert("<?=$app_apply_quota?>");
									histroy.go(-1);
								</script>
								<?php
							} else {
								$mName = getParam("mName");
								$mNameE = getParam("mNameE");
								$mContact = getParam("mContact");
								$mEmail = getParam("mEmail");
								$mSosok = getParam("mSosok");
								$mSosokName = getParam("mSosokName");
								$mGrade = getParam("mGrade");
								if ($mName != "" && $mNameE != "" && $mContact != "" && $mEmail != "" && $mSosok != "" && $mSosokName != "" && $mGrade != "") {
									@mysqli_autocommit($db, false);
									try {
										$saveDay = time() + getDay($db, $oCampus)*24*60*60;
										$stmt = @mysqli_prepare($db, "insert into tMember (tID, sID, mSosok, mSosokName, mGrade, mOpen, mGubun, mCredit, mFile, mFileName, mFileSize, mService, mPurpose, mMaterial, mDesc, mTime, mRemark, mDate, mDateMax) values (?, ?, ?, ?, ?, 'N', 'B', '', '', '', 0, 'N', '', '', '', '', '', UNIX_TIMESTAMP(), ?)");
										@mysqli_stmt_bind_param($stmt, 'iisssi', $uid, $userno, $mSosok, $mSosokName, $mGrade, $saveDay);
										$result = @mysqli_stmt_execute($stmt);
										if (!$result) {
											throw new Exception(mysqli_error($db));
										}
										@mysqli_stmt_close($stmt);
										$stmt = @mysqli_prepare($db, "update tStudent Set sName=?, sNameE=?, sContact=dbenc(?, ?), sEmail=dbenc(?, ?), sLogin=? where ID=? and sHakbun=?");
										@mysqli_stmt_bind_param($stmt, 'ssssssiis', $mName, $mNameE, $enc_key, $mContact, $enc_key, $mEmail, $saveDay, $userno, $userid);
										$result = @mysqli_stmt_execute($stmt);
										if (!$result) {
											throw new Exception(mysqli_error($db));
										}
										@mysqli_stmt_close($stmt);
										@mysqli_commit($db);
										?>
										<script type="text/javascript">
											alert('<?=$app_apply_tutee_success?>');
											closePop();
										</script>
										<?php
									} catch(Exception $e) {
										$errMsg = $base_page . ": " . $e->getmessage();
										@systemLog($errMsg);
										@mysqli_rollback($db);
										?>
										<script type="text/javascript">
											alert('<?=$msg_commit_error?>');
											history.go(-1);
										</script>
										<?php
									}
									@mysqli_autocommit($db, true);
								}
							}
						}

						$stmt = @mysqli_prepare($db, "select sName, sNameE, dbdec(?, sContact), dbdec(?, sEmail) from tStudent where ID=? and sHakbun=?");
						@mysqli_stmt_bind_param($stmt, 'ssis', $enc_key, $enc_key, $userno, $userid);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $mName, $mNameE, $mContact, $mEmail);
						@mysqli_stmt_fetch($stmt);
						@mysqli_stmt_close($stmt);

						$mSosok = "A";
						$placeholder = $app_label_forma_6a;

						?>
						<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=htmlspecialchars($app_label_apply_tutee)?></b> (<?=htmlspecialchars(getTutoringInfo($db, $uid))?>)</p>
						<div id="ose_css_icor">
							<div class="ose_icor_head"><a href="#" onclick="Javascript: if (document.getElementById('ose_ico01').style.display=='') { document.getElementById('ose_ico01').style.display='none'; } else { document.getElementById('ose_ico01').style.display=''; } return false" style="text-decoration:none;"><?=htmlspecialchars($app_label_privacy)?><i class="fa fa-search-plus" aria-hidden="true" style="color:#949494; font-size:20px; float:right; margin-top:12px;"></i></a>
							<input type="radio" name="agree1" id="agree1" value="Y">&nbsp;<span style="color:#000000; font-weight:normal;"><?=htmlspecialchars($app_label_agree)?> &nbsp;&nbsp;<input type="radio" name="agree1" value="N">&nbsp;<?=htmlspecialchars($app_label_noagree)?></span>
							</div>
							<div class="ose_icor_box" id="ose_ico01" style="display:none;">
							<?=getContents($db, "m03_" . $oCampus . strtoupper($lang))?>
							</div>
						</div>
						<form name="fform" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>&lang=<?=$lang?>">
						<input type="hidden" name="mSemester" value="<?=htmlspecialchars($mSemester)?>">
						<input type="hidden" name="mProgram" value="<?=htmlspecialchars($mProgram)?>">
						<input type="hidden" name="uid" value="<?=htmlspecialchars($uid)?>">
						<input type="hidden" name="actt">
						<input type="hidden" name="vlang" id="lang" value="<?=htmlspecialchars($lang)?>">
						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_forma_1)?></th><td><?=htmlspecialchars($userid)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_2)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mName", "mName", " style=\"width:90%;\" maxlength=\"33\"", "", $mName, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_3)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mNameE", "mNameE", " style=\"width:90%;\" maxlength=\"33\"", "", $mNameE, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_4)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mEmail", "mEmail", " style=\"width:90%;\" maxlength=\"200\"", "", $mEmail, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_5)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mContact", "mContact", " style=\"width:90%;\" maxlength=\"200\" placeholder=\"" . $msg_mobile_format . "\"", "", $mContact, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_6)?></th><td><?=makeInput($db, $CanWrite, "radiolist", "SOSOK", "mSosok", "mSosok", "", " OnClick=\"Javascript: if (this.value=='B') { alert ('대학원생은 튜티를 신청할 수 없습니다'); document.fform.mSosok[0].checked = true; } return false;\"", $mSosok, "Y", "", "", "")?><br><?=makeInput($db, $CanWrite, "text", "", "mSosokName", "mSosokName", " style=\"width:90%;\" maxlength=\"66\" placeholder=\"" . $placeholder . "\"", "", $mSosokName, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_7)?></th><td><?=makeInput($db, $CanWrite, "select", "GRADE", "mGrade", "mGrade", "", "", $mGrade, "Y", $mSosok, $msg_select, "")?></td>
							</tr>
						</table>
						</form>
						<p align="center"><?php if ($CanApply == "Y") {?><a href="#" OnClick="Javascript: if (validateAgree('agree1', '<?=$app_label_privacy?>') && validateName('mName', 'Y') && validateName('mNameE', 'Y') && validateEmail('mEmail', 'Y') && validateContact('mContact', 'Y') && validateSosok('mSosokName', 'Y') && validateGrade('mGrade', 'Y')) { if (confirm('<?=$app_confirm_tutee?>')) { showLoader(); document.fform.actt.value='mok'; document.fform.submit(); } } return false;" class="button icon approve"><?php } else {?><a href="#" OnClick="Javascript: alert('<?=$app_label_noperiod?>'); return false;" class="button icon approve"><?php }?><?=htmlspecialchars($app_label_app_submit)?></a><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
						<script type="text/javascript">
							$("input:radio[name=mSosok]").click(function() {
								showLoader();
								var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
								var a = $("input:radio[name=mSosok]:checked").val();
								$.ajax({
									type: "POST",
									async: false,
									url: "/include/ose_ajax.php",
									dataType: "html",
									data: "mid=<?=$mid?>&act=getGrade&a="+a+"&lang=<?=$lang?>&ntime="+ntime,
									success: function(response, status, request) {
										$("#mGrade").find("option").remove().end().append(response);
									}
								});
								if (a == "A") {
									document.getElementById("mSosokName").placeholder = "<?=$app_label_forma_6a?>";
								} else {
									document.getElementById("mSosokName").placeholder = "<?=$app_label_forma_6b?>";
								}
								hideLoader();
							});
						</script>
						<?php
					} else {
						?>
						<p><?=$msg_error_permission?></p>
						<p align="center"><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
						<?php
					}
					break;
				case "subjectsearch":
					//과목 검색
					$sOpt = getParam("sOpt");
					if ($sOpt != "B") {
						$sOpt = "A";
					}
					$mSemester = getParam("mSemester");
					$keyword = getParam("keyword");
					?>
					<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=htmlspecialchars($app_label_subjectsearch)?></b></p>
					<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>&lang=<?=$lang?>">
					<div id="contents_sbox">
					<input type="hidden" name="mSemester" value="<?=htmlspecialchars($mSemester)?>">
					<input type="radio" name="sOpt" value="A" <?php if ($sOpt == "A") echo "checked";?>> 과목명 <input type="radio" name="sOpt" value="B" <?php if ($sOpt == "B") echo "checked";?>> 교수명 <input type="text" name="keyword" id="keyword" style="width:150px;" placeholder="<?=$app_label_subjectkeyword?>" value="<?=htmlspecialchars($keyword)?>"> <a href="#" OnClick="Javascript: if (validateKeyword('keyword', 'Y')) { showLoader(); document.ffsearch.submit(); } return false;" class="button icon search"><?=htmlspecialchars($msg_btn_search)?></a>
					</div>
					</form>
					<?php
					if ($keyword != "") {
						?>
						<table class="ContentsTableBlue" width="100%">
						<tr>
							<th width="40"><?=htmlspecialchars($app_label_result_a)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_l)?></th>
							<th><?=htmlspecialchars($app_label_result_b)?></th>
							<th width="120"><?=htmlspecialchars($app_label_result_f)?></th>
						</tr>
						<?php
						if (substr($mSemester,4,1) == "A") {
							$hyhg = substr($mSemester,0,4) . "1";
						} elseif (substr($mSemester,4,1) == "B") {
							$hyhg = substr($mSemester,0,4) . "3";
						} elseif (substr($mSemester,4,1) == "C") {
							$hyhg = substr($mSemester,0,4) . "2";
						} elseif (substr($mSemester,4,1) == "D") {
							$hyhg = substr($mSemester,0,4) . "4";
						}
						$i = 1;
						if ($lang == "k") {
							$lg = "ko";
						} else {
							$lg = "en";
						}
						if ($sOpt == "A") {
							$data = array("hyhg" => $hyhg,"lang" => $lg,"classname" => $keyword, "callback" => "");
						} else {
							$data = array("hyhg" => $hyhg,"lang" => $lg,"profname" => $keyword, "callback" => "");
						}
						$ch = curl_init('https://yscec.yonsei.ac.kr/tutoring/sg050_ajax.php');
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$result = curl_exec($ch);
						$result = "[" . substr($result,2,strlen($result2)-2) . "]";
						$array = json_decode($result);
						foreach ($array as $value) {
						   //if (strpos($value->kna, $keyword) !== false || strpos($value->prof, $keyword) !== false || strpos($value->ekna, $keyword) !== false) {
								//if ($lang == "e") {
								//	$tTitle = $value->ekna;
								//} else {
								//	$tTitle = $value->kna;
								//}
								$hakno = $value->hakno;
								$tTitle = $value->classname;
								$tProfessor = $value->prof;
								$tProtal = $value->domain . "|@|" . $value->hakno . "|@|" . $value->bb . "|@|" . $value->sbb . "|@|" . $value->ohakkwa . "|@|" . $value->hakjum;
								?>
								<tr>
									<td style="text-align:center;"><?=$i?></td>
									<td style="text-align:center;"><?=htmlspecialchars($hakno)?></td>
									<td style="text-align:center;"><a href="#" OnClick="Javascript: parent.document.getElementById('tTitle').value='<?=str_replace("'","\\'", $tTitle)?>'; parent.document.getElementById('tProfessor').value='<?=str_replace("'","\\'", $tProfessor)?>'; parent.document.getElementById('tPortal').value='<?=str_replace("'","\\'", $tProtal)?>'; closePop(); return false;"><?=htmlspecialchars($tTitle)?></a></td>
									<td style="text-align:center;"><?=htmlspecialchars($tProfessor)?></td>
								</tr>
								<?php
								$i++;
						   //}
						}
						if ($i == 1) {
							?>
							<tr>
								<td colspan="4" style="text-align:center;"><?=$msg_error_nodata?></td>
							</tr>
							<?php
						}
						?>
						</table>
						<?php
					}
					?>
					<p align="center"><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
					<?php
					break;
				case "subjectinfo":
					//과목개설
					$mSemester = getParam("mSemester");
					$mProgram =  getNumber(getParam("mProgram"));
					$CanApply = getPeriodCheck($db, $mProgram);

					if (true) {

						if ($actt == "mok" && $CanApply == "Y" && $mProgram > 0) {
							$oGubun = getParam("oGubun");
							$mGubun = getParam("mGubun");
							$tTitle = getParam("mTitle");
							$tProfessor = getParam("mProfessor");
							$tPortal = getParam("tPortal") . "|@||@||@||@||@||@||@||@||@||@||@|";
							$mName = getParam("mName");
							$mNameE = getParam("mNameE");
							$mContact = getParam("mContact");
							$mEmail = getParam("mEmail");
							$mSosok = getParam("mSosok");
							$mSosokName = getParam("mSosokName");
							$mGrade = getParam("mGrade");
							$mCredit = getParam("mCredit");
							$mService = getYN(getParam("mCredit"));
							if (isUploadSizeExceeded("mFile", $upload_limit_bytes)) {
								?>
								<script type="text/javascript">
									alert("파일 최대 업로드 사이즈는 <?=$upload_limit_mb?>MB입니다. 업로드 가능한 파일 크기를 초과했습니다.");
									history.go(-1);
								</script>
								<?php
								exit;
							}
							$gFile = file_upload("mFile", $max_upload_size, "document");
							if ($gFile != "n" && $gFile != "") {
								$file_info = explode("|", $gFile);
								$mFile = $file_info[0];
								$mFileName = $file_info[1];
								$mFileSize = $file_info[2];
							} else {
								$mFile = "";
								$mFileName = "";
								$mFileSize = 0;
							}
							$mPurpose = getParam("mPurpose");
							$mMaterial = getParam("mMaterial");
							$mDesc = getParam("mDesc");
							$mTime = getParam("mTime");
							$mRemark = getParam("mRemark");
							$sPortal = explode("|@|", $tPortal);
							$tDomain = $sPortal[0];
							$tHakno = $sPortal[1];
							$tBB = $sPortal[2];
							$tSBB = $sPortal[3];
							$tHakkwa = $sPortal[4];
							$tHakjum = $sPortal[5];

							if ($oGubun != "" && $mGubun != "" && $tTitle != "" && $mName != "" && $mNameE != "" && $mContact != "" && $mEmail != "" && $mSosok != "" && $mSosokName != "" && $mGrade != "") {
								@mysqli_autocommit($db, false);
								try {
									$saveDay = time() + getDay($db, $oCampus)*24*60*60;

									$stmt = @mysqli_prepare($db, "insert into tTutoring (pID, tGubun, tTitle, tProfessor, tDomain, tHakno, tBB, tSBB, tHakkwa, tHakjum) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
									@mysqli_stmt_bind_param($stmt, 'isssssssss', $mProgram, $oGubun, $tTitle, $tProfessor, $tDomain, $tHakno, $tBB, $tSBB, $tHakkwa, $tHakjum);
									$result = @mysqli_stmt_execute($stmt);
									if (!$result) {
										throw new Exception(mysqli_error($db));
									}
									$uid = @mysqli_insert_id($db);
									@mysqli_stmt_close($stmt);

									if ($mGubun == "A") {
										$stmt = @mysqli_prepare($db, "insert into tMember (tID, sID, mSosok, mSosokName, mGrade, mOpen, mGubun, mCredit, mFile, mFileName, mFileSize, mService, mPurpose, mMaterial, mDesc, mTime, mRemark, mDate, mDateMax) values (?, ?, ?, ?, ?, 'Y', 'A', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?)");
										@mysqli_stmt_bind_param($stmt, 'iissssssissssssi', $uid, $userno, $mSosok, $mSosokName, $mGrade, $mCredit, $mFile, $mFileName, $mFileSize, $mService, $mPurpose, $mMaterial, $mDesc, $mTime, $mRemark, $saveDay);
										$result = @mysqli_stmt_execute($stmt);
										if (!$result) {
											throw new Exception(mysqli_error($db));
										}
										@mysqli_stmt_close($stmt);
									} else {
										$stmt = @mysqli_prepare($db, "insert into tMember (tID, sID, mSosok, mSosokName, mGrade, mOpen, mGubun, mCredit, mFile, mFileName, mFileSize, mService, mPurpose, mMaterial, mDesc, mTime, mRemark, mDate, mDateMax) values (?, ?, ?, ?, ?, 'Y', 'B', '', '', '', 0, 'N', '', '', '', '', '', UNIX_TIMESTAMP(), ?)");
										@mysqli_stmt_bind_param($stmt, 'iisssi', $uid, $userno, $mSosok, $mSosokName, $mGrade, $saveDay);
										$result = @mysqli_stmt_execute($stmt);
										if (!$result) {
											throw new Exception(mysqli_error($db));
										}
										@mysqli_stmt_close($stmt);
									}
									$stmt = @mysqli_prepare($db, "update tStudent Set sName=?, sNameE=?, sContact=dbenc(?, ?), sEmail=dbenc(?, ?), sLogin=? where ID=? and sHakbun=?");
									@mysqli_stmt_bind_param($stmt, 'ssssssiis', $mName, $mNameE, $enc_key, $mContact, $enc_key, $mEmail, $saveDay, $userno, $userid);
									$result = @mysqli_stmt_execute($stmt);
									if (!$result) {
										throw new Exception(mysqli_error($db));
									}
									@mysqli_stmt_close($stmt);
									@mysqli_commit($db);
									if ($mGubun == "A") {
										?>
										<script type="text/javascript">
											alert('<?=$app_apply_tutor_success?>');
											closePop();
										</script>
										<?php
									} else {
										?>
										<script type="text/javascript">
											alert('<?=$app_apply_tutee_success?>');
											closePop();
										</script>
										<?php
									}
								} catch(Exception $e) {
									$errMsg = $base_page . ": " . $e->getmessage();
									@systemLog($errMsg);
									@mysqli_rollback($db);
									?>
									<script type="text/javascript">
										alert('<?=$msg_commit_error?>');
										history.go(-1);
									</script>
									<?php
								}
								@mysqli_autocommit($db, true);
							}
						}

						$stmt = @mysqli_prepare($db, "select sName, sNameE, dbdec(?, sContact), dbdec(?, sEmail) from tStudent where ID=? and sHakbun=?");
						@mysqli_stmt_bind_param($stmt, 'ssis', $enc_key, $enc_key, $userno, $userid);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $mName, $mNameE, $mContact, $mEmail);
						@mysqli_stmt_fetch($stmt);
						@mysqli_stmt_close($stmt);


						$stmt = @mysqli_prepare($db, "select pSubject, pTheme from tProgram where pCampus=? and pSemester=? and ID=?");
						@mysqli_stmt_bind_param($stmt, 'ssi', $oCampus, $mSemester, $mProgram);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $pSubject, $pTheme);
						@mysqli_stmt_fetch($stmt);
						@mysqli_stmt_close($stmt);

						if ($pSubject == "Y") {
							$oGubun = "A";
							$oRead = " disabled";
						} else {
							$oGubun = "B";
							$oRead = "";
						}
						$mGubun = "A";
						$mSosok = "A";
						$placeholder = $app_label_forma_6a;

						?>
						<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=htmlspecialchars($app_label_apply_subject)?></b></p>
						<div id="ose_css_icor">
							<div class="ose_icor_head"><a href="#" onclick="Javascript: if (document.getElementById('ose_ico01').style.display=='') { document.getElementById('ose_ico01').style.display='none'; } else { document.getElementById('ose_ico01').style.display=''; } return false" style="text-decoration:none;"><?=htmlspecialchars($app_label_privacy)?><i class="fa fa-search-plus" aria-hidden="true" style="color:#949494; font-size:20px; float:right; margin-top:12px;"></i></a>
							<input type="radio" name="agree1" id="agree1" value="Y">&nbsp;<span style="color:#000000; font-weight:normal;"><?=htmlspecialchars($app_label_agree)?> &nbsp;&nbsp;<input type="radio" name="agree1" value="N">&nbsp;<?=htmlspecialchars($app_label_noagree)?></span>
							</div>
							<div class="ose_icor_box" id="ose_ico01" style="display:none;">
							<?=getContents($db, "m03_" . $oCampus . strtoupper($lang))?>
							</div>
						</div>
						<form name="fform" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>&lang=<?=$lang?>" ENCTYPE="MULTIPART/FORM-DATA">
						<input type="hidden" name="mSemester" value="<?=htmlspecialchars($mSemester)?>">
						<input type="hidden" name="mProgram" value="<?=htmlspecialchars($mProgram)?>">
						<input type="hidden" name="uid" value="<?=htmlspecialchars($uid)?>">
						<input type="hidden" name="actt">
						<input type="hidden" name="vlang" id="lang" value="<?=htmlspecialchars($lang)?>">
						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_forma_1)?></th><td><?=htmlspecialchars($userid)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_2)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mName", "mName", " style=\"width:90%;\" maxlength=\"33\"", "", $mName, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_3)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mNameE", "mNameE", " style=\"width:90%;\" maxlength=\"33\"", "", $mNameE, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_4)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mEmail", "mEmail", " style=\"width:90%;\" maxlength=\"200\"", "", $mEmail, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_5)?></th><td><?=makeInput($db, $CanWrite, "text", "", "mContact", "mContact", " style=\"width:90%;\" maxlength=\"200\" placeholder=\"" . $msg_mobile_format . "\"", "", $mContact, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_6)?></th><td><?=makeInput($db, $CanWrite, "radiolist", "SOSOK", "mSosok", "mSosok", "", "", $mSosok, "Y", "", "", "")?><br><?=makeInput($db, $CanWrite, "text", "", "mSosokName", "mSosokName", " style=\"width:90%;\" maxlength=\"66\" placeholder=\"" . $placeholder . "\"", "", $mSosokName, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_forma_7)?></th><td><?=makeInput($db, $CanWrite, "select", "GRADE", "mGrade", "mGrade", "", "", $mGrade, "Y", $mSosok, $msg_select, "")?></td>
							</tr>
						</table>

						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_formb_0)?></th><td><?=makeInput($db, $CanWrite, "radiolist", "OGUBUN", "oGubun", "oGubun", "", "", $oGubun, "", $pSubject . $pTheme, "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_1)?></th><td><?=makeInput($db, $CanWrite, "text", "", "tTitle", "tTitle", " style=\"width:60%;\" maxlength=\"150\" " . $oRead, "", $tTitle, "", "", "", "")?> <a href="#" OnClick="Javascript: LoadPopUp2('/ose_popform.php?mid=<?=$mid?>&mSemester=<?=$mSemester?>&pact=subjectsearch&lang=<?=$lagn?>', 'n'); return false;" class="button icon search" id="ssearch"><?=$msg_btn_search?></a></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_2)?></th><td><?=makeInput($db, $CanWrite, "text", "", "tProfessor", "tProfessor", " style=\"width:100px; maxlength=\"33\" " . $oRead, "", $tProfessor, "", "", "", "")?><?=makeInput($db, $CanWrite, "hidden", "", "tPortal", "tPortal", "", "", $tPortal, "", "", "", "")?><?=makeInput($db, $CanWrite, "hidden", "", "mProfessor", "mProfessor", "", "", $mProfessor, "", "", "", "")?> <?=makeInput($db, $CanWrite, "hidden", "", "mTitle", "mTitle", "", "", $mTitle, "", "", "", "")?>  </td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_3)?></th><td><?=makeInput($db, $CanWrite, "radiolist", "MGUBUN", "mGubun", "mGubun", "", "", $mGubun, "", "", "", "")?></td>
							</tr>
							<tr id="tutorinfoa">
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_4)?></th><td><?=makeInput($db, $CanWrite, "radiolist", "CREDIT", "mCredit", "mCredit", "", "", $mCredit, "", "", "", "")?></td>
							</tr>
							<tr id="tutorinfob">
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_5)?></th><td><?=makeInput($db, $CanWrite, "file", "", "mFile", "mFile", " style=\"width:90%;\" maxlength=\"200\" onchange=\"checkFileUploadSize(this);\"", "", "", "", "", "", "")?><br><?=htmlspecialchars($app_label_formc_noti_4)?></td>
							</tr>
							<!--<tr id="tutorinfoc">
								<th class="thnormal"><?=htmlspecialchars($app_label_formb_6)?></th><td><?=makeInput($db, $CanWrite, "checkbox", "", "mService", "mService", "", "", $mService, "", "", "", "")?> <?=htmlspecialchars($app_label_formb_7)?><br><?=htmlspecialchars($app_label_formc_noti_5)?></td>
							</tr>-->
						</table>
						<div id="tutorinfo">
						<p><?=htmlspecialchars($app_label_formc_noti_1)?></p>
						<table class="ContentsTableGray" width="100%">
							<tr>
								<th width="130"><?=htmlspecialchars($app_label_formc_1)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mPurpose", "mPurpose", " style=\"width:90%;height:80px;\"", "", $mPurpose, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_2)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mMaterial", "mMaterial", " style=\"width:90%;height:80px;\"", "", $mMaterial, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_3)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mDesc", "mDesc", " style=\"width:90%;height:80px;\"", "", $mDesc, "", "", "", "")?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_4)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mTime", "mTime", " style=\"width:90%;height:80px;\"", "", $mTime, "", "", "", "")?><br><?=htmlspecialchars($app_label_formc_noti_2)?></td>
							</tr>
							<tr>
								<th class="thnormal"><?=htmlspecialchars($app_label_formc_5)?></th><td><?=makeInput($db, $CanWrite, "textarea", "", "mRemark", "mRemark", " style=\"width:90%;height:80px;\"", "", $mRemark, "", "", "", "")?><br><?=htmlspecialchars($app_label_formc_noti_3)?></td>
							</tr>
						</table>
						</div>
						
						</form>
						<p align="center"><?php if ($CanApply == "Y") {?><a href="#" OnClick="Javascript: checkform(); return false;" class="button icon approve"><?php } else {?><a href="#" OnClick="Javascript: alert('<?=$app_label_noperiod?>'); return false;" class="button icon approve"><?php }?><?=htmlspecialchars($app_label_app_submit)?></a><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
						<script type="text/javascript">
							$("input:radio[name=mSosok]").click(function() {
								showLoader();
								var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
								var a = $("input:radio[name=mSosok]:checked").val();
								$.ajax({
									type: "POST",
									async: false,
									url: "/include/ose_ajax.php",
									dataType: "html",
									data: "mid=<?=$mid?>&act=getGrade&a="+a+"&lang=<?=$lang?>&ntime="+ntime,
									success: function(response, status, request) {
										$("#mGrade").find("option").remove().end().append(response);
									}
								});
								if (a == "A") {
									document.getElementById("mSosokName").placeholder = "<?=$app_label_forma_6a?>";
								} else {
									document.getElementById("mSosokName").placeholder = "<?=$app_label_forma_6b?>";
								}
								hideLoader();
							});
							$("input:radio[name=oGubun]").click(function() {
								showLoader();
								var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
								var a = $("input:radio[name=oGubun]:checked").val();
								if (a == "A") {
									document.getElementById("ssearch").style.display = '';
									document.getElementById("tTitle").disabled = true;
									document.getElementById("tProfessor").disabled = true;
								} else {
									document.getElementById("ssearch").style.display = 'none';
									document.getElementById("tTitle").disabled = false;
									document.getElementById("tProfessor").disabled = false;
								}
								hideLoader();
							});

							$("input:radio[name=mGubun]").click(function() {
								showLoader();
								var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
								var a = $("input:radio[name=mGubun]:checked").val();
								if (a == "A") {
									document.getElementById("tutorinfoa").style.display = '';
									document.getElementById("tutorinfob").style.display = '';
									//document.getElementById("tutorinfoc").style.display = '';
									document.getElementById("tutorinfo").style.display = '';
								} else {
									document.getElementById("tutorinfoa").style.display = 'none';
									document.getElementById("tutorinfob").style.display = 'none';
									//document.getElementById("tutorinfoc").style.display = 'none';
									document.getElementById("tutorinfo").style.display = 'none';
								}
								hideLoader();
							});

							$("input:radio[name=mSosok]").click(function() {
								showLoader();
								var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
								var a = $("input:radio[name=mSosok]:checked").val();
								if (a == "A") {
									document.fform.mGubun[1].style.display=''; 
									document.getElementById('mGubunB').style.display='';
								} else {
									document.fform.mGubun[0].checked=true; 
									document.fform.mGubun[1].style.display='none'; 
									document.getElementById('mGubunB').style.display='none'; 
									document.getElementById("tutorinfoa").style.display = '';
									document.getElementById("tutorinfob").style.display = '';
									//document.getElementById("tutorinfoc").style.display = '';
									document.getElementById("tutorinfo").style.display = '';
								}
								hideLoader();
							});

							function checkform() {
								var a = $("input:radio[name=oGubun]:checked").val();
								document.getElementById("mTitle").value = document.getElementById("tTitle").value;
								document.getElementById("mProfessor").value = document.getElementById("tProfessor").value;
								if (validateAgree('agree1', '<?=$app_label_privacy?>') && validateName('mName', 'Y') && validateName('mNameE', 'Y') && validateEmail('mEmail', 'Y') && validateContact('mContact', 'Y') && validateSosok('mSosokName', 'Y') && validateSubject(a)) {
									var b = $("input:radio[name=mGubun]:checked").val();
									if (b == "A") {
										if (validateGrade('mGrade', 'Y') && FileUploadCheck('mFile', 'Y', 1, 'file', '<?=$max_upload_size?>') && validateText('mPurpose', 'Y') && validateText('mMaterial', 'Y') && validateText('mDesc', 'Y') && validateText('mTime', 'Y')) {
											if (confirm('<?=$app_confirm_tutor?>')) {
												showLoader();
												document.fform.actt.value='mok';
												document.fform.submit();
											}
										}
									} else {
										if (confirm('<?=$app_confirm_tutee?>')) {
											showLoader();
											document.fform.actt.value='mok';
											document.fform.submit();
										}
									}
								}
							}
						</script>
						<?php
					} else {
						?>
						<p><?=$msg_error_permission?></p>
						<p align="center"><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove"><?=$msg_btn_close?></a></p>
						<?php
					}
					break;
				case "sendsms":
					if ($userlevel < "7") {
						exit;
					}
					$mCampus = getParam("mCampus");
					$mSemester = getParam("mSemester");
					$mProgram = getParam("mProgram");

					if ($act == "mok") {
						$mSender = getParam("mSender");
						$tmtext = getParam("tmtext");
						$pList = implode(",", $_POST["pID"]);
						$stmt = @mysqli_prepare($db, "select distinct tStudent.sName, dbdec(?, tStudent.sContact) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID inner join tMember on tMember.tID=tTutoring.ID inner join tStudent on tMember.sID=tStudent.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? and tTutoring.ID IN (" . $pList . ")");
						@mysqli_stmt_bind_param($stmt, 'sssi', $enc_key, $mCampus, $mSemester, $mProgram);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $sName, $sContact);
						while (@mysqli_stmt_fetch($stmt)) {
							sendsms($mSender, $sContact, $tmtext);
						}
						@mysqli_stmt_close($stmt);
						?>
						<script type="text/javascript">
							alert("발송되었습니다");
						</script>
						<?php
					}
					?>
					<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>">
					<div id="contents_sbox">
					<?=makeInput($db, $CanRead, "select", "USERCAMPUS", "mCampus", "mCampus", "", "", $mCampus, "", "", "", "")?> <?=makeSemester("mSemester", "mSemester", $mSemester)?> <?=makeInput($db, $CanRead, "select", "USERPROGRAM", "mProgram", "mProgram", "", "", $mProgram, "N", $mCampus . $mSemester, "", "")?> <a href="#" OnClick="Javascript: showLoader(); document.ffsearch.submit(); return false;" class="button icon search">조회</a>
					</div>
					</form>
					<form name="fform" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>">
					<input type="hidden" name="mCampus" value="<?=htmlspecialchars($mCampus)?>">
					<input type="hidden" name="mSemester" value="<?=htmlspecialchars($mSemester)?>">
					<input type="hidden" name="mProgram" value="<?=htmlspecialchars($mProgram)?>">
					<input type="hidden" name="act">
					<div style="width:99.8%;height:400px;overflow-x:auto;border:1px solid #cccccc;">
					<table class="ContentsTableBlue" width="100%" style="margin:0;">
						<tr>
							<th width="40"><input type="checkbox" name="selall" OnClick="Javascript: if (this.checked) { var i=0; var str = ''; if (document.fform['pID[]'].length == undefined) {str = document.fform['pID[]'].checked=true;} else { for (i=0; i < document.fform['pID[]'].length; i++) { document.fform['pID[]'][i].checked = true; }} } else { var i=0; var str = ''; if (document.fform['pID[]'].length == undefined) {str = document.fform['pID[]'].checked=false;} else { for (i=0; i < document.fform['pID[]'].length; i++) { document.fform['pID[]'][i].checked = false; }} }"></th>
							<th width="40">번호</th>
							<th>과목명</th>
							<th width="100">튜터</th>
							<th width="100">튜티</th>
						</tr>
						<?php
						$i = 1;
						$stmt = @mysqli_prepare($db, "select tTutoring.ID, tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, (select tStudent.sName from tStudent inner join tMember on tStudent.ID=tMember.sID where tMember.tID=tTutoring.ID and tMember.mGubun='A'), (select count(ID) from tMember where mGubun='B' and tID=tTutoring.ID), tProgram.pMax from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? order by tTutoring.tTitle ASC");
						@mysqli_stmt_bind_param($stmt, 'ssi', $mCampus, $mSemester, $mProgram);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $uid, $tTitle, $tProfessor, $tGubun, $tName, $tCnt, $tMax);
						while (@mysqli_stmt_fetch($stmt)) {
							if ($tProfessor == "") {
								$tTitle = $tTitle;
							} else {
								$tTitle = $tTitle . " (" . $tProfessor . ")";
							}
							if ($tName == "") {
								$tName = "-";
							} else {
								$tName = htmlspecialchars($tName);
							}
							?>
							<tr>
								<td style="text-align:center;"><input type="checkbox" name="pID[]" value="<?=$uid?>"></td>
								<td style="text-align:center;"><?=$i?></td>
								<td><?=htmlspecialchars($tTitle)?></td>
								<td style="text-align:center;"><?=$tName?></td>
								<td style="text-align:center;"><?=number_format($tCnt,0)?></td>
							</tr>
							<?php
							$i++;
						}
						@mysqli_stmt_close($stmt);
					?>
					</table>
					</div>
					<table class="ContentsTableBlue" width="100%">
						<tr>
							<th width="120">발신번호</th><td class="thtop" style="text-align:left;"><input type="text" name="mSender" id="mSender" style="width:95%;" value="<?=getSMS($db, $mCampus)?>"></td>
						</tr>
						<tr>
							<th class="thnormal">내용<br><span id="text_chk_cnt">0</span> / 1000 바이트</th><td><textarea name="tmtext" id="tmtext" class="solid" style="width:95%;height:100px;" OnKeyDown="document.getElementById('text_chk_cnt').innerHTML=getLength(this.value);" OnKeyUp="document.getElementById('text_chk_cnt').innerHTML=getLength(this.value);"></textarea>
							</td>
						</tr>
					</table>
					<p align="center"><a href="#" OnClick="Javascript: var i=0; var str = ''; if (document.fform['pID[]'].length == undefined) { if (document.fform['pID[]'].checked) { str = document.fform['pID[]'].value; } } else { for (i=0; i < document.fform['pID[]'].length; i++) { if (document.fform['pID[]'][i].checked) { str = document.fform['pID[]'][i].value + ',' + str }}} if (str == '') { alert('과목을 선택하세요'); } else { if (validateTel('mSender', 'Y') && validateText('tmtext', 'Y') && confirm('발송하시겠습니까?')) { showLoader(); document.fform.act.value='mok'; document.fform.submit(); } } return false;" class="button icon approve">발송</a><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove">닫기</a></p>
					<?php
					break;
				case "sendemail":
					if ($userlevel < "7") {
						exit;
					}
					$mCampus = getParam("mCampus");
					$mSemester = getParam("mSemester");
					$mProgram = getParam("mProgram");

					if ($act == "mok") {
						$mSender = getParam("mSender");
						$mTitle = getParam("mTitle");
						$tmtext = getParam("tmtext");
						$pList = implode(",", $_POST["pID"]);
						$stmt = @mysqli_prepare($db, "select distinct tStudent.sName, dbdec(?, tStudent.sEmail) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID inner join tMember on tMember.tID=tTutoring.ID inner join tStudent on tMember.sID=tStudent.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? and tTutoring.ID IN (" . $pList . ")");
						@mysqli_stmt_bind_param($stmt, 'sssi', $enc_key, $mCampus, $mSemester, $mProgram);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $sName, $sEmail);
						while (@mysqli_stmt_fetch($stmt)) {
							sendmail ($mSender, "", $sEmail, "", "", $mTitle, $tmtext, "");
						}
						@mysqli_stmt_close($stmt);
						?>
						<script type="text/javascript">
							alert("발송되었습니다");
						</script>
						<?php
					}
					?>
					<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>">
					<div id="contents_sbox">
					<?=makeInput($db, $CanRead, "select", "USERCAMPUS", "mCampus", "mCampus", "", "", $mCampus, "", "", "", "")?> <?=makeSemester("mSemester", "mSemester", $mSemester)?> <?=makeInput($db, $CanRead, "select", "USERPROGRAM", "mProgram", "mProgram", "", "", $mProgram, "N", $mCampus . $mSemester, "", "")?> <a href="#" OnClick="Javascript: showLoader(); document.ffsearch.submit(); return false;" class="button icon search">조회</a>
					</div>
					</form>
					<form name="fform" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&pact=<?=$pact?>">
					<input type="hidden" name="mCampus" value="<?=htmlspecialchars($mCampus)?>">
					<input type="hidden" name="mSemester" value="<?=htmlspecialchars($mSemester)?>">
					<input type="hidden" name="mProgram" value="<?=htmlspecialchars($mProgram)?>">
					<input type="hidden" name="act">
					<div style="width:99.8%;height:200px;overflow-x:auto;border:1px solid #cccccc;">
					<table class="ContentsTableBlue" width="100%" style="margin:0;">
						<tr>
							<th width="40"><input type="checkbox" name="selall" OnClick="Javascript: if (this.checked) { var i=0; var str = ''; if (document.fform['pID[]'].length == undefined) {str = document.fform['pID[]'].checked=true;} else { for (i=0; i < document.fform['pID[]'].length; i++) { document.fform['pID[]'][i].checked = true; }} } else { var i=0; var str = ''; if (document.fform['pID[]'].length == undefined) {str = document.fform['pID[]'].checked=false;} else { for (i=0; i < document.fform['pID[]'].length; i++) { document.fform['pID[]'][i].checked = false; }} }"></th>
							<th width="40">번호</th>
							<th>과목명</th>
							<th width="100">튜터</th>
							<th width="100">튜티</th>
						</tr>
						<?php
						$i = 1;
						$stmt = @mysqli_prepare($db, "select tTutoring.ID, tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, (select tStudent.sName from tStudent inner join tMember on tStudent.ID=tMember.sID where tMember.tID=tTutoring.ID and tMember.mGubun='A'), (select count(ID) from tMember where mGubun='B' and tID=tTutoring.ID), tProgram.pMax from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=? order by tTutoring.tTitle ASC");
						@mysqli_stmt_bind_param($stmt, 'ssi', $mCampus, $mSemester, $mProgram);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $uid, $tTitle, $tProfessor, $tGubun, $tName, $tCnt, $tMax);
						while (@mysqli_stmt_fetch($stmt)) {
							if ($tProfessor == "") {
								$tTitle = $tTitle;
							} else {
								$tTitle = $tTitle . " (" . $tProfessor . ")";
							}
							if ($tName == "") {
								$tName = "-";
							} else {
								$tName = htmlspecialchars($tName);
							}
							?>
							<tr>
								<td style="text-align:center;"><input type="checkbox" name="pID[]" value="<?=$uid?>"></td>
								<td style="text-align:center;"><?=$i?></td>
								<td><?=htmlspecialchars($tTitle)?></td>
								<td style="text-align:center;"><?=$tName?></td>
								<td style="text-align:center;"><?=number_format($tCnt,0)?></td>
							</tr>
							<?php
							$i++;
						}
						@mysqli_stmt_close($stmt);
					?>
					</table>
					</div>
					<p>※ 연세 메일에서 동시에 발송할 수 있는 이메일 갯수를 넘어가면 발송 중 오류가 발생할 수 있습니다</p>
					<table class="ContentsTableBlue" width="100%">
						<tr>
							<th width="120">발신이메일</th><td class="thtop" style="text-align:left;"><input type="text" name="mSender" id="mSender" style="width:95%;" value="<?=getEmail($db, $mCampus)?>"></td>
						</tr>
						<tr>
							<th class="thnormal">제목</th><td style="text-align:left;"><input type="text" name="mTitle" id="mTitle" style="width:95%;"></td>
						<tr>
							<td colspan="2"><textarea name="tmtext" id="tmtext" class="solid" style="width:95%;height:100px;" OnKeyDown="document.getElementById('text_chk_cnt').innerHTML=getLength(this.value);" OnKeyUp="document.getElementById('text_chk_cnt').innerHTML=getLength(this.value);"></textarea>
							</td>
						</tr>
					</table>
					<p align="center"><a href="#" OnClick="Javascript: var i=0; var str = ''; if (document.fform['pID[]'].length == undefined) { if (document.fform['pID[]'].checked) { str = document.fform['pID[]'].value; } } else { for (i=0; i < document.fform['pID[]'].length; i++) { if (document.fform['pID[]'][i].checked) { str = document.fform['pID[]'][i].value + ',' + str }}} if (str == '') { alert('과목을 선택하세요'); } else { if (validateEmail('mSender', 'Y') && validateTitle('mTitle', 'Y') && validateTinyText('tmtext', 'Y') && confirm('발송하시겠습니까?')) { showLoader(); document.fform.act.value='mok'; document.fform.submit(); } } return false;" class="button icon approve">발송</a><a href="#" OnClick="Javascript: closePop(); return false;" class="button icon remove">닫기</a></p>
					<?php
					TinyMCEInit ($userlevel, "100%", $editor_css, $mid, $cmid, $lang);
					break;
			}
		}
		include ($_SERVER['DOCUMENT_ROOT'] . '/include/ose_loader.php');
		?>
		<div id="pop_up">
			<a class="b-close" id="b-close">x</a>
			<div class="pop_content"></div>
		</div>
		<div id="pop_up2">
			<a class="b-close" id="b-close2">x</a>
			<div class="pop_content2"></div>
		</div>
	</div>
</body>
</html>
<?php
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db_close.php');
?>