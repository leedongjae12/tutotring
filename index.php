<?

if (isset($oCampus) == false) {
	header("location:/sinchon/index.php");
}
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_session.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util_core.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_lang.php');


if ($mid == "m04") {
	//로그아웃
	$_SESSION = array();
	session_destroy();
	$userno = "";
	$userid = "";
	$username = "";
	$userlevel = "0";
	$mid = "m01";
	if ($oCampus == "B") {
		$url = "https://infra.yonsei.ac.kr/SsoLogout.do?loginProcUrl=http://ceds.yonsei.ac.kr&LOUT_RETURL=https://tutoring.yonsei.ac.kr/wonju";
	} elseif ($oCampus == "C") {
		$url = "https://infra.yonsei.ac.kr/SsoLogout.do?loginProcUrl=http://ceds.yonsei.ac.kr&LOUT_RETURL=https://tutoring.yonsei.ac.kr/international";
	} else {
		$url = "https://infra.yonsei.ac.kr/SsoLogout.do?loginProcUrl=http://ceds.yonsei.ac.kr&LOUT_RETURL=https://tutoring.yonsei.ac.kr/sinchon";
	}
	header("location:$url");
}

$mAuth = validMenu($mid, "", $db);
if ($mAuth == "") {
	include ($_SERVER['DOCUMENT_ROOT'] . '/include/error.php');
	exit;
} else {
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
}

if ($CanRead == "N") {
	if ((isset($_COOKIE["ctl2_userid"]) && $_COOKIE["ctl2_userid"] != "") || $deBug == "Y") {
		if ($deBug == "Y") {
			$sHakbun = "z7002875";
		} else {
			$sHakbun = $_COOKIE["ctl2_userid"];
		} 
		$data = array("studentid" => $sHakbun,"callback" => "");
		$ch = curl_init('https://yscec.yonsei.ac.kr/tutoring/user_ajax.php');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		//$result = substr($result,2,strlen($result)-2);
		$ret = json_decode($result);
		$sName = $ret[0]->firstname;
		$sNameE = $ret[0]->lastname;
		$sEmail = $ret[0]->email;
		$sContact = $ret[0]->phone2;
		$sTutor = $ret[0]->tutor;
		$sTuti = $ret[0]->tuti;
		$sCnt = 0;
		$stmt = @mysqli_prepare($db, "select count(ID) from tStudent where sHakbun=?");
		@mysqli_stmt_bind_param($stmt, 's', $sHakbun);
		@mysqli_stmt_execute($stmt);
		@mysqli_stmt_store_result($stmt);
		@mysqli_stmt_bind_result($stmt, $sCnt);
		@mysqli_stmt_fetch($stmt);
		@mysqli_stmt_close($stmt);
		@mysqli_autocommit($db, false);
		
		try {
			if ($sCnt == 0) {
				$stmt = @mysqli_prepare($db, "insert into tStudent (sHakbun, sName, sNameE, sContact, sEmail, sLogin) values (?, ?, ?, dbenc(?, ?), dbenc(?, ?),1)");
				@mysqli_stmt_bind_param($stmt, 'sssssss', $sHakbun, $sName, $sNameE, $enc_key, $sContact, $enc_key, $sEmail);
			} else {
				$stmt = @mysqli_prepare($db, "update tStudent Set sName=?, sNameE=?, sContact=dbenc(?, ?), sEmail=dbenc(?, ?) where sHakbun=?");
				@mysqli_stmt_bind_param($stmt, 'sssssss', $sName, $sNameE, $enc_key, $sContact, $enc_key, $sEmail, $sHakbun);
			}
			$result = @mysqli_stmt_execute($stmt);
			if (!$result) {
				//throw new Exception(mysqli_error($db));
			}
			@mysqli_stmt_close($stmt);
			@mysqli_commit($db);
			$stmt = @mysqli_prepare($db, "select ID from tStudent where sHakbun=?");
			@mysqli_stmt_bind_param($stmt, 's', $sHakbun);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_store_result($stmt);
			@mysqli_stmt_bind_result($stmt, $userno);
			@mysqli_stmt_fetch($stmt);
			@mysqli_stmt_close($stmt);
			$userlevel = "1";
			$userid = $sHakbun;
			$username = $sName;
			$usersosok = getYN($sTutor) . getYN($sTuti);
			$temp_str = $userno . "|@|" . $sHakbun . "|@|" . $sName . "|@|" . $usersosok . "|@|" . $userlevel . "|@|";
			$temp_str = encrypt_str($temp_str, $enc_key);
			$_SESSION["sLevel"] = $temp_str;
			setcookie ("ctl2_userid", "", 0, "/", ".yonsei.ac.kr");
		} catch(Exception $e) {
			$errMsg = $base_page . ": " . $e->getmessage();
			@systemLog($errMsg);
			@mysqli_rollback($db);
			?>
			<script type="text/javascript">
				alert('<?=$msg_commit_error?>');
				history.go(-1);
			</script>
			<?
		}
		@mysqli_autocommit($db, true);
	} else {
		if ($oCampus == "B") {
			setcookie ("retURL", "https://tutoring.yonsei.ac.kr/wonju/index.php", 0, "/", ".yonsei.ac.kr");
		} elseif ($oCampus == "C") {
			setcookie ("retURL", "https://tutoring.yonsei.ac.kr/international/index.php", 0, "/", ".yonsei.ac.kr");
		} else {
			setcookie ("retURL", "https://tutoring.yonsei.ac.kr/sinchon/index.php", 0, "/", ".yonsei.ac.kr");
		}
		?>
		<script type="text/javascript">
			window.location="https://infra.yonsei.ac.kr/sauth/ctl_login.do";
		</script>
		<?
	}
}
$_SESSION["oCampus"] = $oCampus;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
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
	</script>
</head>
<body>
	<div id="wrapper">
		<div id="topwrap">
			<div id="toparea">
				<H1 class="notext">Tutoring Application System</H1>
				<H3 class="notext">Logo and Language</H3>
				<div id="top_logo">
					<ul id="top_area">
						<?
						if ($lang == "k") {
							?>
							<li id="top_area01"><a href="./index.php?lang=<?=$lang?>" title="<?=$app_label_logo?>"><img src="/images/OSE_main_logo.jpg" border="0" alt="<?=$app_label_logo?>"></a></li>
							<li id="top_area02"><!--<a href="./index.php?lang=e">English</a>--></li>
							<?
						} else {
							?>
							<li id="top_area01"><a href="./index.php?lang=<?=$lang?>" title="<?=$app_label_logo?>"><img src="/images/OSE_main_logoe.jpg" border="0" alt="<?=$app_label_logo?>"></a></li>
							<li id="top_area02"><a href="./index.php?lang=k">한국어</a></li>
							<?
						}
						?>
						<li id="top_area03" style="font-family:'Nanum Gothic', 'serif';"><?=getCodeName($db, "CAMPUS", $oCampus, "")?></li>
						<li id="top_area04">Tutoring Application System</li>
					</ul>
				</div>
			</div>
		</div>
		<div id="middlewrap">
			<div id="middlearea">
				<div id="ose_tab">
					<ul class="ose_tablist">
						<li <?if ($mid=="m01") echo "class=\"tab_on\"";?>><a href="./index.php?mid=m01&lang=<?=$lang?>"><?=htmlspecialchars($app_lable_tab1)?></a></li>
						<li <?if ($mid=="m02") echo "class=\"tab_on\"";?>><a href="./index.php?mid=m02&lang=<?=$lang?>"><?=htmlspecialchars($app_lable_tab2)?></a></li>
						<li <?if ($mid=="m03") echo "class=\"tab_on\"";?>><a href="./index.php?mid=m03&lang=<?=$lang?>"><?=htmlspecialchars($app_lable_tab3)?></a></li>
						<?
						if ($userlevel == "1") {
							?>
							<li <?if ($mid=="m04") echo "class=\"tab_on\"";?>><a href="./index.php?mid=m04&lang=<?=$lang?>"><?=htmlspecialchars($app_lable_tab4)?></a></li>
							<?
						}
						?>
					</ul>
				</div>
				<?
				switch ($mid) {
					case "m01":
						$contents = "m01_" . $oCampus . strtoupper($lang);
						?>
						<?=getContents($db, $contents)?>
						<script type="text/javascript">
							jQuery(window).load(function() {
								var divWidth = $("#middlearea").width();
								var div = document.getElementById('middlearea');
								var imgtag = "";
								var img = div.getElementsByTagName("img");
								for (i=0; i < img.length; i++) {
									ori_imgwidth = img[i].width;
									ori_imgheight = img[i].height;
									if (divWidth == '960') {
										if (parseInt(ori_imgwidth) >= 960)	{
											$(img[i]).css({"width": "960px", "height": "auto"});
										}
									} else if (divWidth == '680') {
										if (parseInt(ori_imgwidth) >= 680)	{
											$(img[i]).css({"width": "680px", "height": "auto"});
										}
									} else {
										if (parseInt(ori_imgwidth) >= 440)	{
											$(img[i]).css({"width": "440px", "height": "auto"});
										}
									}
								}
							});
						</script>
						<?
						break;
					case "m02":
						$CONFIG_SEM = getSem($db, $oCampus);
						$mProgram = getParam("mProgram");
						$sOpt = getParam("sOpt");
						$sOptB = getParam("sOptB");
						$sOptC = getParam("sOptC");
						$keyword = getParam("keyword");
						$pageblock = getParam("pageblock");
						$page = getNumber(getParam("page"));
						if ($page == 0) {
							$page = "1";
						}

						if ($sOpt != "Y" && $sOpt != "N") {
							$sOpt = "";
						}
						if ($sOptB != "Y" && $sOptB != "N") {
							$sOptB = "";
						}
						if ($sOptC != "B" && $sOptC != "C") {
							$sOptC = "A";
						}
						if ($pageblock != "20" && $pageblock != "50" && $pageblock != "100") {
							$pageblock = "10";
						}

						$nDate = date('Ymd', time());
						$nCnt = 0;
						$stmt = @mysqli_prepare($db, "select count(ID) from tProgram where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.pFrom <=? and tProgram.pTo >=?");
						@mysqli_stmt_bind_param($stmt, 'ssss', $oCampus, $CONFIG_SEM, $nDate, $nDate);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $nCnt);
						@mysqli_stmt_fetch($stmt);
						@mysqli_stmt_close($stmt);
		
						if ($nCnt == 0) {
							?>
							<p>※ <?=htmlspecialchars($app_label_noprogram)?></p>
							<?
						} else {
							?>
							<form name="ffsearch" method="post" action="./index.php?mid=<?=$mid?>&lang=<?=$lang?>">
							<div id="contents_sbox">
							<?=makeInput($db, "Y", "select", "USERPROGRAM", "mProgram", "mProgram", "", " OnChange=\"Javascript: document.ffsearch.page.value=''; showLoader(); document.ffsearch.submit();\"", $mProgram, "N", $oCampus . $CONFIG_SEM, "", "")?><?if ($mProgram == "") { $mProgram = $oValue; }?>
							<br>(<?=htmlspecialchars($app_label_period)?><span id="mPeriod"><?=getPeriod($db, $mProgram)?></span>)
							<p style="margin-bottom:0;"><?=htmlspecialchars($app_label_tutor)?> <input type="radio" name="sOpt" value="" <?if ($sOpt == "") echo "checked";?>> <?=htmlspecialchars($app_label_optall)?> <input type="radio" name="sOpt" value="Y" <?if ($sOpt == "Y") echo "checked";?>> <?=htmlspecialchars($app_label_opty)?> <input type="radio" name="sOpt" value="N" <?if ($sOpt == "N") echo "checked";?>> <?=htmlspecialchars($app_label_optn)?> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <?=htmlspecialchars($app_label_tutee)?> <input type="radio" name="sOptB" <?if ($sOptB == "") echo "checked";?>> <?=htmlspecialchars($app_label_optall)?> <input type="radio" name="sOptB" value="Y" <?if ($sOptB == "Y") echo "checked";?>> <?=htmlspecialchars($app_label_opty)?> <input type="radio" name="sOptB" value="N" <?if ($sOptB == "N") echo "checked";?>> <?=htmlspecialchars($app_label_optn)?>
							<br><input type="radio" name="sOptC" value="A" <?if ($sOptC == "A") echo "checked";?>> <?=htmlspecialchars($app_label_search_subject)?> <input type="radio" name="sOptC" value="B" <?if ($sOptC == "B") echo "checked";?>> <?=htmlspecialchars($app_label_search_tutor)?> <input type="radio" name="sOptC" value="C" <?if ($sOpt == "C") echo "checked";?>> <?=htmlspecialchars($app_label_search_tutee)?> <input type="text" name="keyword" class="width:100px;" value="<?=htmlspecialchars($keyword)?>"> <select name="pageblock"><option value="10" <?if ($pageblock == "10") echo "selected";?>>10<?=htmlspecialchars($app_label_search_page)?></option><option value="20" <?if ($pageblock == "20") echo "selected";?>>20<?=htmlspecialchars($app_label_search_page)?></option><option value="50" <?if ($pageblock == "50") echo "selected";?>>50<?=htmlspecialchars($app_label_search_page)?></option><option value="100" <?if ($pageblock == "100") echo "selected";?>>100<?=htmlspecialchars($app_label_search_page)?></option></select> <a href="#" OnClick="Javascript: document.ffsearch.page.value=''; showLoader(); document.ffsearch.submit(); return false;" class="button icon search"><?=htmlspecialchars($msg_btn_search)?></a>
							<input type="hidden" name="page" value="<?=htmlspecialchars($page)?>">
							</div>
							</form>
							<?
							$total_record = 0;
							$num_per_page = $pageblock;

							$tempWhere = "";
							if ($sOpt == "Y") {
								$tempWhere = $tempWhere . " and (select count(ID) from tMember where tID=tTutoring.ID and mGubun='A') > 0";
							} elseif ($sOpt == "N") {
								$tempWhere = $tempWhere . " and (select count(ID) from tMember where tID=tTutoring.ID and mGubun='A') = 0";
							}
							if ($sOptB == "Y") {
								$tempWhere = $tempWhere . " and (select count(ID) from tMember where tID=tTutoring.ID and mGubun='B') > 0";
							} elseif ($sOptB == "N") {
								$tempWhere = $tempWhere . " and (select count(ID) from tMember where tID=tTutoring.ID and mGubun='B') = 0";
							}

							if ($keyword != "") {
								$keyword_like = "%" . $keyword . "%";
								if ($sOptC == "A") {
									$tempWhere = $tempWhere . " and tTutoring.tTitle like ?";
								} elseif ($sOptC == "B") {
									if ($lang == "k") {
										$tempWhere = $tempWhere . " and (select count(tMember.ID) from tMember inner join tStudent on tMember.sID=tStudent.ID where tMember.tID=tTutoring.ID and tMember.mGubun='A' and tStudent.sName like ?) > 0";
									} else {
										$tempWhere = $tempWhere . " and (select count(tMember.ID) from tMember inner join tStudent on tMember.sID=tStudent.ID where tMember.tID=tTutoring.ID and tMember.mGubun='A' and tStudent.sNameE like ?) > 0";
									}
								} elseif ($sOptC == "C") {
									if ($lang == "k") {
										$tempWhere = $tempWhere . " and (select count(tMember.ID) from tMember inner join tStudent on tMember.sID=tStudent.ID where tMember.tID=tTutoring.ID and tMember.mGubun='B' and tStudent.sName like ?) > 0";
									} else {
										$tempWhere = $tempWhere . " and (select count(tMember.ID) from tMember inner join tStudent on tMember.sID=tStudent.ID where tMember.tID=tTutoring.ID and tMember.mGubun='B' and tStudent.sNameE like ?) > 0";
									}
								}
							}

							//전체 갯수 구하기
							$stmt = @mysqli_prepare($db, "select count(tTutoring.ID) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=?" . $tempWhere);
							if ($keyword != "") {
								@mysqli_stmt_bind_param($stmt, 'ssis', $oCampus, $CONFIG_SEM, $mProgram, $keyword_like);
							} else {
								@mysqli_stmt_bind_param($stmt, 'ssi', $oCampus, $CONFIG_SEM, $mProgram);
							}
							@mysqli_stmt_execute($stmt);
							@mysqli_stmt_store_result($stmt);
							@mysqli_stmt_bind_result($stmt, $cCnt);
							@mysqli_stmt_fetch($stmt);
							@mysqli_stmt_close($stmt);

							//페이지수 계산
							$total_record = $cCnt;
							$total_page = ceil($total_record/$num_per_page);
							if (!$page) {
								$page = 1;
							}
							$first = getBBSFirst($total_record, $num_per_page, $page);
							$last = getBBSLast($total_record, $num_per_page, $page);


							$CanApply = getPeriodCheck($db, $mProgram);
?>
									<p><a href="#" OnClick="Javascript: LoadPopUp('/ose_popform.php?mid=<?=$mid?>&mSemester=<?=$CONFIG_SEM?>&mProgram=<?=$mProgram?>&pact=subjectinfo&lang=<?=$lagn?>', 'y'); return false;" class="button icon add"><?=htmlspecialchars($app_label_add)?></a></p>
							<p class="total_num" style="margin-top:-30px;">Total: <?=number_format($total_record)?> data, Page <?=$page?> of <?=$total_page?>
							<table class="ContentsTableBlue" width="100%">
							<tr>
								<th width="40" class="hideColumn"><?=htmlspecialchars($app_label_result_a)?></th>
								<th><?=htmlspecialchars($app_label_result_b)?></th>
								<th width="80"><?=htmlspecialchars($app_label_result_c)?></th>
								<th width="80"><?=htmlspecialchars($app_label_result_d)?></th>
								<th width="80"><?=htmlspecialchars($app_label_result_e)?></th>
							</tr>
							<?
							$i = 1;
							if ($lang == "k") {
								$stmt = @mysqli_prepare($db, "select tTutoring.ID, tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, (select tStudent.sName from tStudent inner join tMember on tStudent.ID=tMember.sID where tMember.tID=tTutoring.ID and tMember.mGubun='A'), (select count(ID) from tMember where mGubun='B' and tID=tTutoring.ID), tProgram.pMax from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=?" . $tempWhere . " order by tTutoring.tTitle ASC limit ?, ?");
							} else {
								$stmt = @mysqli_prepare($db, "select tTutoring.ID, tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, (select tStudent.sNameE from tStudent inner join tMember on tStudent.ID=tMember.sID where tMember.tID=tTutoring.ID and tMember.mGubun='A'), (select count(ID) from tMember where mGubun='B' and tID=tTutoring.ID), tProgram.pMax from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=?" . $tempWhere . " order by tTutoring.tTitle ASC limit ?, ?");
							}
							if ($keyword != "") {
								@mysqli_stmt_bind_param($stmt, 'ssisii', $oCampus, $CONFIG_SEM, $mProgram, $keyword_like, $first, $num_per_page);
							} else {
								@mysqli_stmt_bind_param($stmt, 'ssiii', $oCampus, $CONFIG_SEM, $mProgram, $first, $num_per_page);
							}
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
									if ($CanApply == "Y") {
										if (substr($usersosok,0,1) == "Y") {
											$tName = "<a href=\"#\" OnClick=\"Javascript: LoadPopUp('/ose_popform.php?mid=" . $mid . "&mSemester=" . $CONFIG_SEM . "&mProgram=" . $mProgram . "&uid=" . $uid . "&pact=applytutor&lang=" . $lang . "', 'y'); return false;\" class=\"button\">" . htmlspecialchars($app_label_apply_tutor) . "</a>";
										} else {
											$tName = "<a href=\"#\" OnClick=\"Javascript: alert('" . $msg_error_permission . "'); return false;\" class=\"button\">" . htmlspecialchars($app_label_apply_tutor) . "</a>";
										}
									} else {
										$tName = "<a href=\"#\" OnClick=\"Javascript: alert('" . $app_label_noperiod . "'); return false;\" class=\"button\">" . htmlspecialchars($app_label_apply_tutor) . "</a>";
									}
								} else {
									$tName = "<a href=\"#\" OnClick=\"Javascript: LoadPopUp('/ose_popform.php?mid=" . $mid . "&mSemester=" . $CONFIG_SEM . "&mProgram=" . $mProgram . "&uid=" . $uid . "&pact=tutorinfo&lang=" . $lang . "', 'n'); return false;\">" . htmlspecialchars($tName) . "</a>";;
								}
								if ($tCnt < $tMax) {
									if ($CanApply == "Y") {
										if (substr($usersosok,1,1) == "Y") {
											$tApply = "<a href=\"#\" OnClick=\"Javascript: LoadPopUp('/ose_popform.php?mid=" . $mid . "&mSemester=" . $CONFIG_SEM . "&mProgram=" . $mProgram . "&uid=" . $uid . "&pact=applytutee&lang=" . $lang . "', 'y'); return false;\" class=\"button\">" . htmlspecialchars($app_label_apply_tutee) . "</a>";;
										} else {
											$tApply = "<a href=\"#\" OnClick=\"Javascript: alert('" . $msg_error_permission . "'); return false;\" class=\"button\">" . htmlspecialchars($app_label_apply_tutee) . "</a>";;
										}
									} else {
										$tApply = "<a href=\"#\" OnClick=\"Javascript: alert('" . $app_label_noperiod . "'); return false;\" class=\"button\">" . htmlspecialchars($app_label_apply_tutee) . "</a>";;
									}
								} else {
									$tApply = htmlspecialchars($app_label_apply_close);
								}
								?>
								<tr>
									<td style="text-align:center;" class="hideColumn"><?=($num_per_page*($page-1) + $i)?></td>
									<td><?=htmlspecialchars($tTitle)?></td>
									<td style="text-align:center;"><?=$tName?></td>
									<td style="text-align:center;"><a href="#" OnClick="Javascript: LoadPopUp('/ose_popform.php?mid=<?=$mid?>&mSemester=<?=$CONFIG_SEM?>&mProgram=<?=$mProgram?>&uid=<?=$uid?>&pact=tuteeinfo&lang=<?=$lang?>', 'n'); return false;"><?=number_format($tCnt,0)?></a></td>
									<td style="text-align:center;"><?=$tApply?></td>
								</tr>
								<?
								$i++;
							}
							if ($i == 1) {
								?>
								<tr>
									<td colspan="5" style="text-align:center;"><?=$msg_error_nodata?></td>
								</tr>
								<?
							}
							@mysqli_stmt_close($stmt);
							?>
							</table>
							<?
							$query_string = $PHP_SELF . "?mid=" . $mid . "&cmid=" . $cmid . "&lang=" . $lang . "&act=list&skeyword=" . urlencode($keyword) . "&mProgram=" . urlencode($mProgram) . "&sOpt=" . urlencode($sOpt) . "&sOptB=" . urlencode($sOptB) . "&sOptC=" . urlencode($sOptC) . "&pageblock=" . urlencode($pageblock);
							$icon_paging = getBBSPage($total_record, $total_page, $page_per_block, $page, $last, $query_string, $lang);
							echo $icon_paging;
							?>
							<script type="text/javascript">
								$("#mProgram").change(function() {
									showLoader();
									var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
									var a = $("#mProgram").val();
									$.ajax({
										type: "POST",
										async: false,
										url: "/include/ose_ajax.php",
										dataType: "html",
										data: "mid=<?=$mid?>&act=getPeriod&a="+a+"&lang=<?=$lang?>&ntime="+ntime,
										success: function(response, status, request) {
											$("#mPeriod").html(response);
										}
									});
									hideLoader();
								});
							</script>
							<?
						}
						break;
					case "m03":
						$CONFIG_SEM = getSem($db, $oCampus);
						if ($act == "dok" && $uid > 0) {
							$pid = getNumber(getParam("pid"));
							if (getPeriodCheck($db, $pid) == "Y") {
								@mysqli_autocommit($db, false);
								try {
									$stmt = @mysqli_prepare($db, "delete from tMember where ID=? and sID=?");
									@mysqli_stmt_bind_param($stmt, 'ii', $uid, $userno);
									$result = @mysqli_stmt_execute($stmt);
									if (!$result) {
										throw new Exception(mysqli_error($db));
									}
									@mysqli_stmt_close($stmt);
								} catch(Exception $e) {
									$errMsg = $base_page . ": " . $e->getmessage();
									@systemLog($errMsg);
									@mysqli_rollback($db);
									?>
									<script type="text/javascript">
										alert('<?=$msg_commit_error?>');
										history.go(-1);
									</script>
									<?
								}
								@mysqli_autocommit($db, true);
							}
						}
						?>
						<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=getSemesterName($db, $CONFIG_SEM)?></b></p>
						<table class="ContentsTableBlue" width="100%">
						<tr>
							<th width="40" class="hideColumn"><?=htmlspecialchars($app_label_result_a)?></th>
							<th class="hideColumn"><?=htmlspecialchars($app_label_result_g)?></th>
							<th><?=htmlspecialchars($app_label_result_b)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_c)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_h)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_i)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_j)?></th>
						</tr>
						<?
						$i = 1;
						$stmt = @mysqli_prepare($db, "select tMember.ID, tProgram.ID, getCodeName('PROGRAM', tProgram.pCode, ?, ?), tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, (select (case when ?='k' Then tStudent.sName Else tStudent.sNameE end) from tStudent inner join tMember on tStudent.ID=tMember.sID where tMember.tID=tTutoring.ID and tMember.mGubun='A'), (select count(ID) from tMember where mGubun='B' and tID=tTutoring.ID), tMember.mGubun, getCodeName('MGUBUN', tMember.mGubun, '', ?) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID where tProgram.pCampus=? and tProgram.pSemester=? and tMember.sID=?");
						@mysqli_stmt_bind_param($stmt, 'ssssssi', $oCampus, $lang, $lang, $lang, $oCampus, $CONFIG_SEM, $userno);
						@mysqli_stmt_execute($stmt);
						echo mysqli_error($db);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $uid, $pID, $StrProgram, $tTitle, $tProfessor, $tGubun, $tName, $tCnt, $mGubun, $StrGubun);
						while (@mysqli_stmt_fetch($stmt)) {
							if ($tProfessor == "") {
								$tTitle = $tTitle;
							} else {
								$tTitle = $tTitle . " (" . $tProfessor . ")";
							}
							?>
							<tr>
								<td style="text-align:center;" class="hideColumn"><?=$i?></td>
								<td style="text-align:center;" class="hideColumn"><?=htmlspecialchars($StrProgram)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($tTitle)?></td>
								<td style="text-align:center;"><?=$tName?></td>
								<td style="text-align:center;"><?=number_format($tCnt,0)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($StrGubun)?></td>
								<?
								if (getPeriodCheck($db, $pID) == "Y") {
									?>
									<td style="text-align:center;"><a href="#" OnClick="Javascript: if (confirm('<?=$msg_cancel_check?>')) { showLoader(); document.ffdata.uid.value='<?=$uid?>'; document.ffdata.pid.value='<?=$pID?>'; document.ffdata.act.value='dok'; document.ffdata.submit(); } return false; " class="button icon danger trash"><?=$msg_btn_cancel?></a></td>
									<?
								} else {
									?>
									<td style="text-align:center;">-</td>
									<?
								}
								?>
							</tr>
							<?
							$i++;
						}
						if ($i == 1) {
							?>
							<tr>
								<td colspan="7" style="text-align:center;"><?=$msg_error_nodata?></td>
							</tr>
							<?
						}
						@mysqli_stmt_close($stmt);
						?>
						</table>
						<p><img src="/images/bullet.gif" border="0" align="absmiddle"> <b><?=htmlspecialchars($app_last_semester)?></b></p>
						<table class="ContentsTableBlue" width="100%">
						<tr>
							<th width="40" class="hideColumn"><?=htmlspecialchars($app_label_result_a)?></th>
							<th width="120"><?=htmlspecialchars($app_label_result_k)?></th>
							<th class="hideColumn"><?=htmlspecialchars($app_label_result_g)?></th>
							<th><?=htmlspecialchars($app_label_result_b)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_c)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_h)?></th>
							<th width="80"><?=htmlspecialchars($app_label_result_i)?></th>
						</tr>
						<?
						$i = 1;
						$stmt = @mysqli_prepare($db, "select tTutoring.ID, tProgram.ID, tProgram.pSemester, getCodeName('PROGRAM', tProgram.pCode, ?, ?), tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, (select (case when ?='k' Then tStudent.sName Else tStudent.sNameE end) from tStudent inner join tMember on tStudent.ID=tMember.sID where tMember.tID=tTutoring.ID and tMember.mGubun='A'), (select count(ID) from tMember where mGubun='B' and tID=tTutoring.ID), tMember.mGubun, getCodeName('MGUBUN', tMember.mGubun, '', ?) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID where tProgram.pCampus=? and tProgram.pSemester < ? and tMember.sID=?");
						@mysqli_stmt_bind_param($stmt, 'ssssssi', $oCampus, $lang, $lang, $lang, $oCampus, $CONFIG_SEM, $userno);
						@mysqli_stmt_execute($stmt);
						echo mysqli_error($db);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $uid, $pID, $pSemester, $StrProgram, $tTitle, $tProfessor, $tGubun, $tName, $tCnt, $mGubun, $StrGubun);
						while (@mysqli_stmt_fetch($stmt)) {
							if ($tProfessor == "") {
								$tTitle = $tTitle;
							} else {
								$tTitle = $tTitle . " (" . $tProfessor . ")";
							}
							?>
							<tr>
								<td style="text-align:center;" class="hideColumn"><?=$i?></td>
								<td style="text-align:center;"><?=getSemesterName($db, $pSemester)?></td>
								<td style="text-align:center;" class="hideColumn"><?=htmlspecialchars($StrProgram)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($tTitle)?></td>
								<td style="text-align:center;"><?=$tName?></td>
								<td style="text-align:center;"><?=number_format($tCnt,0)?></td>
								<td style="text-align:center;"><?=htmlspecialchars($StrGubun)?></td>
							</tr>
							<?
							$i++;
						}
						if ($i == 1) {
							?>
							<tr>
								<td colspan="7" style="text-align:center;"><?=$msg_error_nodata?></td>
							</tr>
							<?
						}
						@mysqli_stmt_close($stmt);
						?>
						</table>

						<form name="ffdata" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>&lang=<?=$lang?>">
						<input type="hidden" name="uid">
						<input type="hidden" name="pid">
						<input type="hidden" name="act">
						</form>
						<?
						break;
				}
				?>
			</div>
		</div>
		<div id="footerwrap">
			<div id="footerarea">
				<div>
				</div>
				<div id="footer_add">
					<?=$label_copyright?>
				</div>
			</div>
		</div>
		<?
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
<?
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db_close.php');
?>
