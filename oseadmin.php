<?php
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_session.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util_core.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/util.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/include/main_config_lang.php');
//권한체크
$mAuth = validMenu($mid, "", $db);
if ($mAuth == "") {
	include ($_SERVER['DOCUMENT_ROOT'] . '/include/error.php');
	exit;
} else {
	if ($userlevel < substr($mAuth,0,1)) {
		$url = "/oseadmin.php";
		header("location:$url");
	}
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
	<link rel="stylesheet" type="text/css" href="/css/admbase.css" />
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
	<?php
	$userip = $_SERVER['REMOTE_ADDR'];
	$stmt = @mysqli_prepare($db, "select cCodeDesc from tCode where cCodeGroup='SYSTEM' and cCode='IPLIST'");
	@mysqli_stmt_execute($stmt);
	@mysqli_stmt_store_result($stmt);
	@mysqli_stmt_bind_result($stmt, $IPList);
	@mysqli_stmt_fetch($stmt);
	@mysqli_stmt_close($stmt);

	$ipCheck = false;
	if ($IPList != "") {
		$nIP = explode(",", $IPList);
		for ($i=0; $i < sizeof($nIP); $i++) {
			if ($userip == trim($nIP[$i])) {
				$ipCheck = true;
				break;
			}
		}
	} else {
		$ipCheck = true;
	}

	if ($ipCheck == false) {
		?>
		<script type="text/javascript">
			alert("허용되지 않은 IP입니다");
		</script>
		<?php
	} else {
		if ($mid == "A09") {
			//로그아웃
			@addLog($userno, "로그아웃", $db);
			$_SESSION = array();
			session_destroy();
			?>
			<script type="text/javascript">
				window.location='/oseadmin.php';
			</script>
			</body>
			</html>
			<?php
			exit;
		}
		if ($actt == "lok") {
			$id = getParam("loginid");
			$pwd = getParam("loginpw");
			$pwCnt = $_SESSION["pwCnt"];
			$pwCnt = getNumber($pwCnt);

			$stmt = @mysqli_prepare($db, "select ID, sName, (case when sPassword=password(?) then 'Y' else 'N' end) pw, sGubun, sAuth from tAdmin where sUserID=? and (sAuth='A' or sAuth='B')");
			@mysqli_stmt_bind_param($stmt, 'ss', $pwd, $id);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_store_result($stmt);
			@mysqli_stmt_bind_result($stmt, $userno, $username, $pw, $sGubun, $sAuth);
			@mysqli_stmt_fetch($stmt);
			@mysqli_stmt_close($stmt);

			if ($userno == "") {
				?>
				<script type="text/javascript">
					alert('<?=$msg_login_error?>'); 
				</script>
				<?php
			} else {
				if ($pw == "Y") {
					$_SESSION["pwCnt"] = 0;
					$userid = $id;
					$usersosok = $sGubun;
					if ($sAuth == "A") {
						$userlevel = "9";
					} else {
						$userlevel = "7";
					}
					$temp_str = $userno . "|@|" . $userid . "|@|" . $username . "|@|" . $usersosok . "|@|" . $userlevel . "|@|";
					$temp_str = encrypt_str($temp_str, $enc_key);
					$_SESSION["aLevel"] = $temp_str;
					@addLog($userno, "로그인", $db);

					//개인정보 삭제
					if ($userlevel == "9") {
						$stmt = @mysqli_prepare($db, "select sID, mFile from tMember where mDateMax <= UNIX_TIMESTAMP() and mSosokName <> ''");
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $nID, $nFile);
						while (@mysqli_stmt_fetch($stmt)) {
							if ($nFile != "") {
								@unlink ($_SERVER['DOCUMENT_ROOT'] . $nFile);
							}
							$stmt2 = @mysqli_prepare($db, "update tStudent Set sName='', sNameE='', sContact=dbenc(?, ''), sEmail=dbenc(?, '') where ID=? and sLogin <= UNIX_TIMESTAMP() and sName <> ''");
							@mysqli_stmt_bind_param($stmt2, 'ssi', $enc_key, $enc_key, $nID);
							@mysqli_stmt_execute($stmt2);
							@mysqli_stmt_close($stmt2);
						}
						@mysqli_stmt_close($stmt);

						$stmt = @mysqli_prepare($db, "update tMember Set mSosokName='', mFile='', mFileSize=0, mFileName='' where mDateMax <= UNIX_TIMESTAMP() and mSosokName <> ''");
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_close($stmt);
					}
				} else {
					$pwCnt = $pwCnt + 1;
					$_SESSION["pwCnt"] = $pwCnt;
					if ($pwCnt < 5) {
						?>
						<script type="text/javascript">
							alert('비밀번호 <?=$pwCnt?>회 오류입니다. 5회 오류시 권한이 해제됩니다.'); 
						</script>
						<?php
					} else {
						$stmt = @mysqli_prepare($db, "update tAdmin Set sAuth='C' where sUserID=?");
						@mysqli_stmt_bind_param($stmt, 's', $id);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_close($stmt);					
						@addLog(0, "비밀번호 오류 권한해제: " . $id, $db);
						?>
						<script type="text/javascript">
							alert('비밀번호 5회 초과 오류로 권한이 해제되었습니다.'); 
						</script>
						<?php
					}
				}
			}
		}
		If ((int)$userlevel < 7) {
			?>
			<div id="css_s_top_wrapper">
				<div id="css_s_toparea">
					<div id="css_s_toplogo"><a href="oseadmin.php" title="연세대학교 OSE 튜터링 신청 관리 시스템"></a></div>
				</div>	
			</div>
			<div id="css_s_middle_wrapper">
				<h3 class="notext">컨텐츠 내용 시작</h3>
				<div id="css_s_login">
					<div id="css_s_login_title" title="로그인"></div>
					<div id="css_s_form">
						<form name="fflogin" method="post" align="center" action="<?=$PHP_SELF?>?mid=<?=$mid?>&lang=<?=$lang?>" OnSubmit="Javascript: if (!validateUserID('loginid', 'Y') || !validatePassword('loginpw', 'Y')) { return false; }">
							<input type="hidden" name="actt" value="lok">
							<input type="hidden" id="lang" name="jslang" value="<%=FormatHTML(lang)%>">
							<fieldset>
								<legend class="notext">로그인하기</legend>
								<ul class="css_list">
									<li>
										<label for="loginid" class="notext">아이디</label>
										<input type="text" name="loginid" id="loginid" style="width:275px;height:26px;margin-left:93px;margin-top:15px;border-color:#9a9a9a;" title="아이디">
									</li>
									<li>
										<label for="loginpw" class="notext">비밀번호</label>
										<input type="password" name="loginpw" id="loginpw" style="width:275px;height:26px;margin-left:93px;margin-top:8px;border-color:#9a9a9a;" title="비밀번호">
									</li>
									<li style="margin-top: -83px; margin-left: 390px;">
										<label for="loginbtn" class="notext">로그인 </label>
										<input type="image" id="loginbtn" src="/images/login_btn.gif" title="로그인" style="border:0;">
									</li>
								</ul>
								<p style="text-align:center; color:#666666; background-color:#f2f2f2; padding:8px 0;">연세대학교 OSE 튜터링 신청 관리 시스템입니다.</p>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<?php
		} else {
			if ($mid == "A01") {
				$pageTitle = "신청현황";
			} elseif ($mid == "A02") {
				$pageTitle = "학기관리";
			} elseif ($mid == "A03") {
				$pageTitle = "통계";
			} elseif ($mid == "A04") {
				$pageTitle = "콘텐츠관리";
			} elseif ($mid == "A05") {
				$pageTitle = "코드관리";
			} elseif ($mid == "A06") {
				$pageTitle = "관리자관리";
			} elseif ($mid == "A07") {
				$pageTitle = "개인정보접속로그";
			} elseif ($mid == "A08") {
				$pageTitle = "개인정보수정";
			} elseif ($mid == "A09") {
				$pageTitle = "로그아웃";
			} else {
				$mid = "A01";
				$pageTitle = "신청현황";
			}
			?>
			<div id="css_s_top_wrapper">
				<div id="css_s_toparea">
					<div id="css_s_toplogo"><a href="oseadmin.php" title="연세대학교 OSE 튜터링 신청 관리 시스템"></a></div>
				</div>
				<h2 class="notext">메인메뉴</h2>
				<div id="toparea_menu">
					<div id="css_s_menu">
						<ul class="css_s_list">
							<li class="menu_list<?php if ($mid == "A01") echo "on";?>"><a href="oseadmin.php?mid=A01" title="신청현황">신청현황</a></li>
							<li class="menu_list<?php if ($mid == "A02") echo "on";?>"><a href="oseadmin.php?mid=A02" title="학기관리">학기관리</a></li>
							<li class="menu_list<?php if ($mid == "A03") echo "on";?>"><a href="oseadmin.php?mid=A03" title="통계">통계</a></li>
							<li class="menu_list<?php if ($mid == "A04") echo "on";?>"><a href="oseadmin.php?mid=A04" title="콘텐츠관리">콘텐츠관리</a></li>
							<?php
							if ((int)$userlevel == 9) {
								?>
								<li class="menu_list<?php if ($mid == "A05") echo "on";?>"><a href="oseadmin.php?mid=A05" title="코드관리">코드관리</a></li>
								<li class="menu_list<?php if ($mid == "A06") echo "on";?>"><a href="oseadmin.php?mid=A06" title="관리자관리">관리자관리</a></li>
								<li class="menu_list<?php if ($mid == "A07") echo "on";?>"><a href="oseadmin.php?mid=A07" title="개인정보접속로그">개인정보접속로그</a></li>
								<?php
							}
							?>
							<li class="menu_list<?php if ($mid == "A08") echo "on";?>"><a href="oseadmin.php?mid=A08" title="개인정보수정">개인정보수정</a></li>
							<li class="menu_list<?php if ($mid == "A09") echo "on";?>"><a href="oseadmin.php?mid=A09" title="로그아웃">로그아웃</a></li>
						</ul>
					</div>
				</div>
			</div>
			<div id="css_s_middle_wrapper">
				<h3 class="notext">컨텐츠 내용 시작</h3>
				<div id="css_smiddleareawrapper">
					<div id="css_smiddlearea">
						<div id="css_s_ctitle"><img src="/images/bullet_title.gif" align="absmiddle">&nbsp;&nbsp;<?=htmlspecialchars($pageTitle)?></div>
						<?php
						include ($_SERVER['DOCUMENT_ROOT'] . '/include/ose_admin.php');
						?>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<h2 class="notext">연락처 및 저작권</h2>
		<div id="css_s_footer_wrapper">
			<div id="css_s_footerarea"><img src="/images/css_s_footerlogo_ose.gif" style="float:left;">
				<div id="css_s_footext"><?=$label_copyright?></div>
			</div>
		</div>
		<?php
	}
	?>
	<?php
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