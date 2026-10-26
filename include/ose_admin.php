<?
error_reporting (E_ALL ^ E_NOTICE); 
//관리자 페이지
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
switch ($mid) {
	case "A01":
		//신청현황
		if ($CanRead != "Y") {
			exit;
		}

		$mCampus = getParam("mCampus");
		$mSemester = getParam("mSemester");
		$mProgram = getParam("mProgram");
		if ($mSemester == "") {
			if (date('m', time()) == "02" || date('m', time()) == "03" || date('m', time()) == "04" || date('m', time()) == "05") {
				$mSemester = date('Y', time()) . "A";	
			} elseif (date('m', time()) == "06" || date('m', time()) == "07") {
				$mSemester = date('Y', time()) . "B";	
			} elseif (date('m', time()) == "08" || date('m', time()) == "09" || date('m', time()) == "10" || date('m', time()) == "11") {
				$mSemester = date('Y', time()) . "C";	
			} elseif (date('m', time()) == "12") {
				$mSemester = date('Y', time()) . "D";	
			} else {
				$mSemester = (date('Y', time())-1) . "D";	
			}
		}

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
		if ($sOptB != "Y" && $sOptB != "N" && $sOptB != "D") {
			$sOptB = "";
		}
		if ($sOptC != "B" && $sOptC != "C") {
			$sOptC = "A";
		}
		if ($pageblock != "20" && $pageblock != "50" && $pageblock != "100") {
			$pageblock = "10";
		}
		?>
		<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<div id="admcontents_sbox"><input type="hidden" name="page" value="<?=htmlspecialchars($page)?>">
		<?=makeInput($db, $CanRead, "select", "USERCAMPUS", "mCampus", "mCampus", "", "", $mCampus, "", "", "", "")?><?if ($mCampus == "") { $mCampus = $oValue; }?> <?=makeSemester("mSemester", "mSemester", $mSemester)?> <?=makeInput($db, $CanRead, "select", "USERPROGRAM", "mProgram", "mProgram", "", "", $mProgram, "N", $mCampus . $mSemester, "", "")?><?if ($mProgram == "") { $mProgram = $oValue; }?>&nbsp;&nbsp;&nbsp;튜터 <input type="radio" name="sOpt" value="" <?if ($sOpt == "") echo "checked";?>> 전체 <input type="radio" name="sOpt" value="Y" <?if ($sOpt == "Y") echo "checked";?>> 유 <input type="radio" name="sOpt" value="N" <?if ($sOpt == "N") echo "checked";?>> 무 &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; 튜티 <input type="radio" name="sOptB" <?if ($sOptB == "") echo "checked";?>> 전체 <input type="radio" name="sOptB" value="Y" <?if ($sOptB == "Y") echo "checked";?>> 튜티1명 <input type="radio" name="sOptB" value="D" <?if ($sOptB == "D") echo "checked";?>> 튜티 2명 이상 <input type="radio" name="sOptB" value="N" <?if ($sOptB == "N") echo "checked";?>> 무<p style="margin-bottom:0;">검색어 <input type="radio" name="sOptC" value="A" <?if ($sOptC == "A") echo "checked";?>> 과목명 <input type="radio" name="sOptC" value="B" <?if ($sOptC == "B") echo "checked";?>> 튜터명 <input type="radio" name="sOptC" value="C" <?if ($sOpt == "C") echo "checked";?>> 튜티명 <input type="text" name="keyword" class="width:100px;" value="<?=htmlspecialchars($keyword)?>"> <select name="pageblock"><option value="10" <?if ($pageblock == "10") echo "selected";?>>10개씩보기</option><option value="20" <?if ($pageblock == "20") echo "selected";?>>20개씩보기</option><option value="10" <?if ($pageblock == "50") echo "selected";?>>50개씩보기</option><option value="100" <?if ($pageblock == "100") echo "selected";?>>100개씩보기</option></select> <a href="#" OnClick="Javascript: document.ffsearch.page.value=''; showLoader(); document.ffsearch.submit(); return false;" class="button icon search">조회</a><a href="#" OnClick="Javascript: document.ffexcel.submit(); return false;" class="button icon download">엑셀다운</a><a href="#" OnClick="Javascript: LoadPopUp('/ose_popform.php?mid=<?=$mid?>&mCampus=<?=$mCampus?>&mSemester=<?=$mSemester?>&mProgram=<?=$mProgram?>&pact=sendsms', 'n'); return false;" class="button icon user" style="margin-left:5px;">SMS</a><a href="#" OnClick="Javascript: LoadPopUp('/ose_popform.php?mid=<?=$mid?>&mCampus=<?=$mCampus?>&mSemester=<?=$mSemester?>&mProgram=<?=$mProgram?>&pact=sendemail', 'n'); return false;" class="button icon mail" style="margin-left:5px;">이메일</a>
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
			$tempWhere = $tempWhere . " and (select count(ID) from tMember where tID=tTutoring.ID and mGubun='B') = 1";
		} elseif ($sOptB == "D") {
			$tempWhere = $tempWhere . " and (select count(ID) from tMember where tID=tTutoring.ID and mGubun='B') >= 2";
		} elseif ($sOptB == "N") {
			$tempWhere = $tempWhere . " and (select count(ID) from tMember where tID=tTutoring.ID and mGubun='B') = 0";
		}

		if ($keyword != "") {
			$keyword_like = "%" . $keyword . "%";
			if ($sOptC == "A") {
				$tempWhere = $tempWhere . " and tTutoring.tTitle like ?";
			} elseif ($sOptC == "B") {
				$tempWhere = $tempWhere . " and (select count(tMember.ID) from tMember inner join tStudent on tMember.sID=tStudent.ID where tMember.tID=tTutoring.ID and tMember.mGubun='A' and tStudent.sName like ?) > 0";
			} elseif ($sOptC == "C") {
				$tempWhere = $tempWhere . " and (select count(tMember.ID) from tMember inner join tStudent on tMember.sID=tStudent.ID where tMember.tID=tTutoring.ID and tMember.mGubun='B' and tStudent.sName like ?) > 0";
			}
		}

		$ExcelQuery = "select '', getCodeName('PROGRAM', tProgram.pCode, tProgram.pCampus, 'k'), tTutoring.tTitle, tTutoring.tProfessor, getCodeName('MGUBUN', tMember.mGubun, '', 'k'), tStudent.sHakbun, tStudent.sName, tStudent.sNameE, dbdec('" . $enc_key . "', tStudent.sContact), dbdec('" . $enc_key . "', tStudent.sEmail), getCodeName('SOSOK', tMember.mSosok, '', 'k'), tMember.mSosokName, getCodeName('GRADE', tMember.mGrade, tMember.mSosok, 'k'), concat(getCodeName('CREDIT', tMember.mCredit, '', 'k'), '|@|', tMember.ID, '|@|', tMember.mFile), tMember.mService from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID inner join tMember on tMember.tID=tTutoring.ID inner join tStudent on tMember.sID=tStudent.ID where tProgram.pCampus='" . str_replace("'","''", $mCampus) . "' and tProgram.pSemester='" . str_replace("'","''", $mSemester) . "' and tProgram.ID=" . $mProgram . str_replace("?", "'" . str_replace("'","''", $keyword_like) . "'", $tempWhere) . " order by tProgram.ID, tTutoring.ID, tMember.mGubun, tStudent.sName";
		$ExcelHeader = "번호,프로그램,과목명,담당교수,구분,학번,이름(국문),이름(영문),연락처,이메일,소속,소속명,학년,성적,사회봉사학점";
		$ExcelType = "N,N,N,N,N,N,N,N,N,N,N,N,N,L,N";
		$ExcelTitle = "신청현황_" . date('Ymd', time());
		$ExcelWidth = "40,300,200,140,140,140,140,140,140,140,140,200,100,100,100";


		//전체 갯수 구하기
		$stmt = @mysqli_prepare($db, "select count(tTutoring.ID) from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=?" . $tempWhere);
		if ($keyword != "") {
			@mysqli_stmt_bind_param($stmt, 'ssis', $mCampus, $mSemester, $mProgram, $keyword_like);
		} else {
			@mysqli_stmt_bind_param($stmt, 'ssi', $mCampus, $mSemester, $mProgram);
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
		?>
		<p class="total_num">Total: <?=number_format($total_record)?> data, Page <?=$page?> of <?=$total_page?>
		<table class="ctable" width="100%">
			<tr>
				<th width="40">번호</th>
				<th>과목명</th>
				<th width="100">튜터</th>
				<th width="100">튜티</th>
			</tr>
			<?
			$i = 1;
			$stmt = @mysqli_prepare($db, "select tTutoring.ID, tTutoring.tTitle, tTutoring.tProfessor, tTutoring.tGubun, (select tStudent.sName from tStudent inner join tMember on tStudent.ID=tMember.sID where tMember.tID=tTutoring.ID and tMember.mGubun='A'), (select count(ID) from tMember where mGubun='B' and tID=tTutoring.ID), tProgram.pMax from tTutoring inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=? and tProgram.pSemester=? and tProgram.ID=?" . $tempWhere . " order by tTutoring.tTitle ASC limit ?, ?");
			if ($keyword != "") {
				@mysqli_stmt_bind_param($stmt, 'ssisii', $mCampus, $mSemester, $mProgram, $keyword_like, $first, $num_per_page);
			} else {
				@mysqli_stmt_bind_param($stmt, 'ssiii', $mCampus, $mSemester, $mProgram, $first, $num_per_page);
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
					$tName = "-";
				} else {
					$tName = "<a href=\"#\" OnClick=\"Javascript: LoadPopUp('/ose_popform.php?mid=" . $mid . "&mCampus=" . $mCampus . "&mSemester=" . $mSemester . "&mProgram=" . $mProgram . "&uid=" . $uid . "&pact=tutorinfo&lang=" . $lang . "', 'n'); return false;\">" . htmlspecialchars($tName) . "</a>";
				}
				?>
				<tr>
					<td style="text-align:center;"><?=$i?></td>
					<td><?=htmlspecialchars($tTitle)?></td>
					<td style="text-align:center;"><?=$tName?></td>
					<td style="text-align:center;"><a href="#" OnClick="Javascript: LoadPopUp('/ose_popform.php?mid=<?=$mid?>&mCampus=<?=$mCampus?>&mSemester=<?=$mSemester?>&mProgram=<?=$mProgram?>&uid=<?=$uid?>&pact=tuteeinfo&lang=<?=$lang?>', 'n'); return false;"><?=number_format($tCnt,0)?></a></td>
				</tr>
				<?
				$i++;
			}
			@mysqli_stmt_close($stmt);
		?>
		</table>
		<?
		$query_string = $PHP_SELF . "?mid=" . $mid . "&cmid=" . $cmid . "&lang=" . $lang . "&act=list&skeyword=" . urlencode($keyword) . "&mCampus=" . urlencode($mCampus) . "&mSemester=" . urlencode($mSemester) . "&mProgram=" . urlencode($mProgram) . "&sOpt=" . urlencode($sOpt) . "&sOptB=" . urlencode($sOptB) . "&sOptC=" . urlencode($sOptC) . "&pageblock=" . urlencode($pageblock);
		$icon_paging = getBBSPage($total_record, $total_page, $page_per_block, $page, $last, $query_string, $lang);
		echo $icon_paging;
		?>
		<script type="text/javascript">
			$("#mCampus").change(function() {
				showLoader();
				var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
				var a = $("#mCampus").val();
				var b = $("#mSemester").val();
				$.ajax({
					type: "POST",
					async: false,
					url: "/include/ose_ajax.php",
					dataType: "html",
					data: "mid=<?=$mid?>&act=getUserProgram&a="+a+b+"&ntime="+ntime,
					success: function(response, status, request) {
						$("#mProgram").find("option").remove().end().append(response);
					}
				});
				hideLoader();
			});
			$("#mSemester").change(function() {
				showLoader();
				var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
				var a = $("#mCampus").val();
				var b = $("#mSemester").val();
				$.ajax({
					type: "POST",
					async: false,
					url: "/include/ose_ajax.php",
					dataType: "html",
					data: "mid=<?=$mid?>&act=getUserProgram&a="+a+b+"&ntime="+ntime,
					success: function(response, status, request) {
						$("#mProgram").find("option").remove().end().append(response);
					}
				});
				hideLoader();
			});
		</script>
		<form name="ffexcel" method="post" action="/include/ose_nexcel.php?mid=<?=$mid?>">
		<input type="hidden" name="ExcelHeader" value="<?=htmlspecialchars($ExcelHeader)?>">
		<input type="hidden" name="ExcelTitle" value="<?=htmlspecialchars($ExcelTitle)?>">
		<input type="hidden" name="ExcelType" value="<?=htmlspecialchars($ExcelType)?>">
		<input type="hidden" name="ExcelWidth" value="<?=htmlspecialchars($ExcelWidth)?>">
<!--
		<input type="hidden" name="ExcelQuery" value="<?=encrypt_str($ExcelQuery, $enc_key)?>"> -->
		<input type="hidden" name="ExcelQuery" value="<?=htmlspecialchars($ExcelQuery)?>">
		</form>
		<?
		break;
	case "A02":
		//학기관리
		if ($CanRead != "Y") {
			exit;
		}
		$sOpt = getParam("sOpt");
		if ($sOpt != "B") {
			$sOpt = "A";
		}
		$mCampus = getParam("mCampus");
		$mSemester = getParam("mSemester");
		if ($mSemester == "") {
			if (date('m', time()) == "02" || date('m', time()) == "03" || date('m', time()) == "04" || date('m', time()) == "05") {
				$mSemester = date('Y', time()) . "A";	
			} elseif (date('m', time()) == "06" || date('m', time()) == "07") {
				$mSemester = date('Y', time()) . "B";	
			} elseif (date('m', time()) == "08" || date('m', time()) == "09" || date('m', time()) == "10" || date('m', time()) == "11") {
				$mSemester = date('Y', time()) . "C";	
			} elseif (date('m', time()) == "12") {
				$mSemester = date('Y', time()) . "D";	
			} else {
				$mSemester = (date('Y', time())-1) . "D";	
			}
		}
		?>
		<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<div id="admcontents_sbox">
		<?=makeInput($db, $CanWrite, "select", "USERCAMPUS", "mCampus", "mCampus", "", "", $mCampus, "", "", "", "")?><?if ($mCampus == "") { $mCampus = $oValue; }?> <input type="radio" name="sOpt" value="A" <?if ($sOpt == "A") echo "checked";?>> 학기별 프로그램  <?=makeSemester("mSemester", "mSemester", $mSemester)?> <input type="radio" name="sOpt" value="B" <?if ($sOpt == "B") echo "checked";?>> 운영학기 <a href="#" OnClick="Javascript: showLoader(); document.ffsearch.submit(); return false;" class="button icon search">조회</a>
		</div>
		</form>
		<?
		if ($sOpt == "A") {
			if ($actt == "aok" && $CanWrite == "Y") {
				$pCode = getParam("pCode");
				$pSubject = getYN(getParam("pSubject"));
				$pTheme = getYN(getParam("pTheme"));
				$pMax = getNumber(getParam("pMax"));
				$pFrom = getCal(getParam("pFrom"));
				$pTo = getCal(getParam("pTo"));

				if ($pFrom == "" || $pTo == "") {
					?>
					<script type="text/javascript">
						alert('신청기간을 입력하세요'); 
						history.go(-1);
					</script>
					<?
				} else {
					$cCnt = 0;
					if ($pCode != "") {
						$stmt = @mysqli_prepare($db, "select count(ID) from tProgram where pCampus=? and pSemester=? and pCode=?");
						@mysqli_stmt_bind_param($stmt, 'sss', $mCampus, $mSemester, $pCode);
						@mysqli_stmt_execute($stmt);
						@mysqli_stmt_store_result($stmt);
						@mysqli_stmt_bind_result($stmt, $cCnt);
						@mysqli_stmt_fetch($stmt);
						@mysqli_stmt_close($stmt);
					}
					if ($cCnt == 0) {
						@mysqli_autocommit($db, false);
						try {
							if ($pCode == "") {
								$stmt = @mysqli_prepare($db, "insert into tProgram (pCampus, pSemester, pCode, pSubject, pTheme, pMax, pFrom, pTo) select ?, ?, cCode, ?, ?, ?, ?, ? from tCode where cCodeGroup='PROGRAM' and cCodeRef=? and cShow='Y' and (select count(ID) from tProgram where pCampus=? and pSemester=? and pCode=cCode)=0");
								@mysqli_stmt_bind_param($stmt, 'ssssisssss', $mCampus, $mSemester, $pSubject, $pTheme, $pMax, $pFrom, $pTo, $mCampus, $mCampus, $mSemester);
							} else {
								$stmt = @mysqli_prepare($db, "insert into tProgram (pCampus, pSemester, pCode, pSubject, pTheme, pMax, pFrom, pTo) values (?, ?, ?, ?, ?, ?, ?, ?)");
								@mysqli_stmt_bind_param($stmt, 'sssssiss', $mCampus, $mSemester, $pCode, $pSubject, $pTheme, $pMax, $pFrom, $pTo);
							}
							$result = @mysqli_stmt_execute($stmt);
							if (!$result) {
								throw new Exception(mysqli_error($db));
							}
							$new_id = @mysqli_insert_id($db);
							@mysqli_stmt_close($stmt);
							@mysqli_commit($db);
							@addLog ($userno, "프로그램 추가 [" . $mCampus . "] " . $mSemester  . " - " . $pCode, $db);
						} catch(Exception $e) {
							$errMsg = $base_page . ": " . $e->getmessage();
							@systemLog($errMsg);
							@mysqli_rollback($db);
							?>
							<script type="text/javascript">
								alert('처리중 오류가 발생했습니다');
								history.go(-1);
							</script>
							<?
						}
						@mysqli_autocommit($db, true);
					} else {
						?>
						<script type="text/javascript">
							alert('동일한 프로그램이 존재합니다'); 
							history.go(-1);
						</script>
						<?
					}
				}
			}
			if ($actt == "mok" && $uid > 0 && $CanWrite == "Y") {
				$pCode = getParam("pCode");
				$pSubject = getYN(getParam("pSubject"));
				$pTheme = getYN(getParam("pTheme"));
				$pMax = getNumber(getParam("pMax"));
				$pFrom = getCal(getParam("pFrom"));
				$pTo = getCal(getParam("pTo"));
				@mysqli_autocommit($db, false);
				try {
					$stmt = @mysqli_prepare($db, "update tProgram Set pSubject=?, pTheme=?, pMax=?, pFrom=?, pTo=? where pCampus=? and pSemester=? and pCode=? and ID=?"); 
					@mysqli_stmt_bind_param($stmt, 'ssisssssi', $pSubject, $pTheme, $pMax, $pFrom, $pTo, $mCampus, $mSemester, $pCode, $uid);
					$result = @mysqli_stmt_execute($stmt);
					if (!$result) {
						throw new Exception(mysqli_error($db));
					}
					@mysqli_stmt_close($stmt);
					@mysqli_commit($db);
					@addLog ($userno, "프로그램 수정 [" . $mCampus . "] " . $mSemester  . " - " . $pCode, $db);
				} catch(Exception $e) {
					$errMsg = $base_page . ": " . $e->getmessage();
					@systemLog($errMsg);
					@mysqli_rollback($db);
					?>
					<script type="text/javascript">
						alert('처리중 오류가 발생했습니다'); 
						history.go(-1);
					</script>
					<?
				}
				@mysqli_autocommit($db, true);
			}
			if ($actt == "dok" && $uid > 0 && $CanDelete == "Y") {
				$pCode = getParam("pCode");
				$cCnt = 0;
				$stmt = @mysqli_prepare($db, "select count(ID) from tTutoring where pID=?"); 
				@mysqli_stmt_bind_param($stmt, 'i', $uid);
				@mysqli_stmt_execute($stmt);
				@mysqli_stmt_store_result($stmt);
				@mysqli_stmt_bind_result($stmt, $cCnt);
				@mysqli_stmt_fetch($stmt);
				@mysqli_stmt_close($stmt);

				if ($cCnt == 0) {
					@mysqli_autocommit($db, false);
					try {
						$tempID = getUserID($db, $uid);
						$stmt = @mysqli_prepare($db, "delete from tProgram where pCampus=? and pSemester=? and pCode=? and ID=?"); 
						@mysqli_stmt_bind_param($stmt, 'sssi', $mCampus, $mSemester, $pCode, $uid);
						$result = @mysqli_stmt_execute($stmt);
						if (!$result) {
							throw new Exception(mysqli_error($db));
						}
						@mysqli_stmt_close($stmt);
						@mysqli_commit($db);
						@addLog ($userno, "프로그램 삭제 [" . $mCampus . "] " . $mSemester  . " - " . $pCode, $db);
					} catch(Exception $e) {
						$errMsg = $base_page . ": " . $e->getmessage();
						@systemLog($errMsg);
						@mysqli_rollback($db);
						?>
						<script type="text/javascript">
							alert('처리중 오류가 발생했습니다');
							history.go(-1);
						</script>
						<?
					}
					@mysqli_autocommit($db, true);
				} else {
					?>
					<script type="text/javascript">
						alert('개설된 튜터링 과목이 있는 프로그램은 삭제할 수 없습니다'); 
						history.go(-1);
					</script>
					<?
				}
			}
			?>
			<form name="ffdata">
			<table class="ctable" width="100%" style="margin-top:10px;">
			<tr>
				<th width="40">번호</th>
				<th>프로그램명</th>
				<th width="80">과목개설</th>
				<th width="80">주제개설</th>
				<th width="80">튜티인원</th>
				<th width="250">신청기간</th>
				<th width="160">작업</th>
			</tr>
			<tr class="bg2">
				<td>-<input type="hidden" name="uid_0" id="uid_0" value=""></td>
				<td><?=makeInput($db, $CanWrite, "select", "PROGRAM", "pCode_0", "pCode_0", " style=\"width:90%;\"", "", "", "Y", $mCampus, "전체", "")?></td>
				<td><?=makeInput($db, $CanWrite, "checkbox", "", "pSubject_0", "pSubject_0", "", "", "", "Y", "", "", "")?></td>
				<td><?=makeInput($db, $CanWrite, "checkbox", "", "pTheme_0", "pTheme_0", "", "", "", "Y", "", "", "")?></td>
				<td><?=makeInput($db, $CanWrite, "number", "", "pMax_0", "pMax_0", "  style=\"width:60px;  ime-mode:disabled\" ", "", "", "Y", "", "", "")?></td>
				<td><?=makeInput($db, $CanWrite, "date", "", "pFrom_0", "pFrom_0", " style=\"width:80px;\"", "", "", "Y", "", "", "")?> ~ <?=makeInput($db, $CanWrite, "date", "", "pTo_0", "pTo_0", " style=\"width:80px;\"", "", "", "Y", "", "", "")?></td>
				<td><a href="#" OnClick="Javascript: updateCode('0', 'aok'); return false;" class="button icon add">추가</a></td>
			</tr>
			<?
			$stmt = @mysqli_prepare($db, "select tProgram.ID, tProgram.pCode, tCode.cCodeName, tProgram.pSubject, tProgram.pTheme, tProgram.pMax, tProgram.pFrom, tProgram.pTo from tProgram inner join tCode on tCode.cCodeGroup='PROGRAM' and tCode.cCode=tProgram.pCode and tCode.cCodeRef=tProgram.pCampus where tProgram.pCampus=? and tProgram.pSemester=? order by tCode.cOrder ASC");
			@mysqli_stmt_bind_param($stmt, 'ss', $mCampus, $mSemester);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_store_result($stmt);
			@mysqli_stmt_bind_result($stmt, $uid, $pCode, $pName, $pSubject, $pTheme, $pMax, $pFrom, $pTo);
			$i = 1;
			while (@mysqli_stmt_fetch($stmt)) {
				if ($i % 2 == 0) {
					$OddLine = " class=\"oddline\"";
				} else {
					$OddLine = "";
				}
				?>
				<tr<?=$OddLine?>>
					<td class="center"><?=$i?><input type="hidden" name="uid_<?=$i?>" id="uid_<?=$i?>" value="<?=$uid?>"></td>
					<td align="center"><?=htmlspecialchars($pName)?><input type="hidden" name="pCode_<?=$i?>" id="pCode_<?=$i?>" value="<?=$pCode?>"></td>
					<td align="center"><?=makeInput($db, $CanWrite, "checkbox", "", "pSubject_" . $i, "pSubject_" . $i, "", "", $pSubject, "Y", "", "", "")?></td>
					<td align="center"><?=makeInput($db, $CanWrite, "checkbox", "", "pTheme_" . $i, "pTheme_" . $i, "", "", $pTheme, "Y", "", "", "")?></td>
					<td align="center"><?=makeInput($db, $CanWrite, "number", "", "pMax_" . $i, "pMax_" . $i, "  style=\"width:60px;  ime-mode:disabled\"", "", $pMax, "Y", "", "", "")?></td>
					<td align="center"><?=makeInput($db, $CanWrite, "date", "", "pFrom_" . $i, "pFrom_" . $i, " style=\"width:80px;\"", "", $pFrom, "Y", "", "", "")?> ~ <?=makeInput($db, $CanWrite, "date", "", "pTo_" . $i, "pTo_" . $i, "", " style=\"width:80px;\"", $pTo, "Y", "", "", "")?></td>
					<td class="center"><?if ($CanWrite=="Y") {?><a href="#" OnClick="Javascript: updateCode('<?=$i?>', 'mok'); return false;" class="button icon edit">수정</a><?}?><?if ($CanDelete=="Y") {?><a href="#" OnClick="Javascript: if (confirm('정말로 삭제하겠습니까?')) { updateCode('<?=$i?>', 'dok'); } return false;" class="button danger icon trash" style="margin-left:5px;">삭제</a><?}?></td>
				</tr>
				<?
				$i++;
			}
			@mysqli_stmt_close($stmt);
			?>
			</table>
			</form>
			<form name="ffadmin" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
			<input type="hidden" name="actt">
			<input type="hidden" name="uid">
			<input type="hidden" name="sOpt" value="<?=htmlspecialchars($sOpt)?>">
			<input type="hidden" name="mCampus" value="<?=htmlspecialchars($mCampus)?>">
			<input type="hidden" name="mSemester" value="<?=htmlspecialchars($mSemester)?>">
			<input type="hidden" name="pCode">
			<input type="hidden" name="pSubject">
			<input type="hidden" name="pTheme">
			<input type="hidden" name="pMax">
			<input type="hidden" name="pFrom">
			<input type="hidden" name="pTo">
			</form>

			<script type="text/javascript">
				function updateCode(idx, actname) {
					document.ffadmin.uid.value=document.getElementById('uid_'+idx).value;
					document.ffadmin.pCode.value=document.getElementById('pCode_'+idx).value;
					document.ffadmin.pMax.value=document.getElementById('pMax_'+idx).value;
					document.ffadmin.pFrom.value=document.getElementById('pFrom_'+idx).value;
					document.ffadmin.pTo.value=document.getElementById('pTo_'+idx).value;
					if (document.getElementById('pSubject_'+idx).checked == true) {
						document.ffadmin.pSubject.value="Y";
					} else {
						document.ffadmin.pSubject.value="N";
					}
					if (document.getElementById('pTheme_'+idx).checked == true) {
						document.ffadmin.pTheme.value="Y";
					} else {
						document.ffadmin.pTheme.value="N";
					}
					document.ffadmin.actt.value=actname;
					if (document.ffadmin.pTheme.value == "N" && document.ffadmin.pSubject.value == "N")	{
						alert("과목 또는 주제개설 여부를 선택하세요");
						return false;
					} else {
						if (validateDate("pFrom_"+idx, "Y") && validateDate("pTo_"+idx, "Y")) {
							showLoader();
							document.ffadmin.submit();
						}
					}
				}
			</script>
			<?
		} else {
			if ($actt == "mok" && $CanWrite == "Y") {
				$cSemester = getParam("cSemester");
				@mysqli_autocommit($db, false);
				try {
					$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode=concat('CONFIG', ?)");
					@mysqli_stmt_bind_param($stmt, 'ss', $cSemester, $mCampus);
					$result = @mysqli_stmt_execute($stmt);
					if (!$result) {
						throw new Exception(mysqli_error($db));
					}
					@mysqli_stmt_close($stmt);
					@mysqli_commit($db);
				} catch(Exception $e) {
					$errMsg = $base_page . ": " . $e->getmessage();
					@systemLog($errMsg);
					@mysqli_rollback($db);
					?>
					<script type="text/javascript">
						alert('처리중 오류가 발생했습니다');
						history.go(-1);
					</script>
					<?
				}
				@mysqli_autocommit($db, true);
			}

			$stmt = @mysqli_prepare($db, "select cCodeDesc from tCode where cCodeGroup='SYSTEM' and cCode=concat('CONFIG', ?)");
			@mysqli_stmt_bind_param($stmt, 's', $mCampus);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_store_result($stmt);
			@mysqli_stmt_bind_result($stmt, $cCodeDesc);
			@mysqli_stmt_fetch($stmt);
			@mysqli_stmt_close($stmt);
			?>
			<form name="ffdata" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
			<input type="hidden" name="actt">
			<input type="hidden" name="sOpt" value="<?=$sOpt?>">
			<input type="hidden" name="mCampus" value="<?=$mCampus?>">
			<table class="ctable" width="400" style="margin-top:10px;">
				<tr>
					<th>캠퍼스</th>
					<th>운영학기</th>
					<th width="80">작업</th>
				</tr>
				<tr>
					<td class="center"><?=getCodeName($db, "CAMPUS", $mCampus, "")?></td>
					<td class="center"><?=makeSemester("cSemester", "cSemester", $cCodeDesc)?></td>
					<td class="center"><?if ($CanWrite == "Y") {?><a href="#" OnClick="Javascript: showLoader(); document.ffdata.actt.value='mok'; document.ffdata.submit(); return false;" class="button icon edit">수정</a><?}?></td>

				</tr>
			</table>
			</form>
			<?
		}
		break;
	case "A03":
		//통계
		if ($CanRead != "Y") {
			exit;
		}
		$sOpt = getParam("sOpt");
		if ($sOpt != "B") {
			$sOpt = "A";
		}
		$mCampus = getParam("mCampus");
		$mSemFrom = getParam("mSemFrom");
		$mSemTo = getParam("mSemTo");
		$mProgram = getParam("mProgram");
		if ($mSemFrom == "") {
			if (date('m', time()) == "02" || date('m', time()) == "03" || date('m', time()) == "04" || date('m', time()) == "05") {
				$mSemFrom = date('Y', time()) . "A";	
			} elseif (date('m', time()) == "06" || date('m', time()) == "07") {
				$mSemFrom = date('Y', time()) . "B";	
			} elseif (date('m', time()) == "08" || date('m', time()) == "09" || date('m', time()) == "10" || date('m', time()) == "11") {
				$mSemFrom = date('Y', time()) . "C";	
			} elseif (date('m', time()) == "12") {
				$mSemFrom = date('Y', time()) . "D";	
			} else {
				$mSemFrom = (date('Y', time())-1) . "D";	
			}
		}
		if ($mSemTo == "") {
			if (date('m', time()) == "02" || date('m', time()) == "03" || date('m', time()) == "04" || date('m', time()) == "05") {
				$mSemTo = date('Y', time()) . "A";	
			} elseif (date('m', time()) == "06" || date('m', time()) == "07") {
				$mSemTo = date('Y', time()) . "B";	
			} elseif (date('m', time()) == "08" || date('m', time()) == "09" || date('m', time()) == "10" || date('m', time()) == "11") {
				$mSemTo = date('Y', time()) . "C";	
			} elseif (date('m', time()) == "12") {
				$mSemTo = date('Y', time()) . "D";	
			} else {
				$mSemTo = (date('Y', time())-1) . "D";	
			}
		}
		?>
		<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<div id="admcontents_sbox">
		<?=makeInput($db, $CanWrite, "select", "USERCAMPUS", "mCampus", "mCampus", "", "", $mCampus, "", "", "", "")?><?if ($mCampus == "") { $mCampus = $oValue; }?> <?=makeSemester("mSemFrom", "mSemFrom", $mSemFrom)?> ~ <?=makeSemester("mSemTo", "mSemTo", $mSemTo)?> <?=makeInput($db, $CanWrite, "select", "PROGRAM", "mProgram", "mProgram", "", "", $mProgram, "N", $mCampus, "전체", "")?>  <a href="#" OnClick="Javascript: showLoader(); document.ffsearch.submit(); return false;" class="button icon search">조회</a><a href="#" OnClick="Javascript: document.ffexcel.submit(); return false;" class="button icon download">엑셀다운</a>
		</div>
		</form>
		<table class="ctable" width="100%">
		<tr>
			<th width="80">번호</th>
			<th>프로그램</th>
			<th width="120">총 과목수</th>
			<th width="120">튜터없는 과목</th>
			<th width="120">튜티없는 과목</th>
			<th width="120">튜터수</th>
			<th width="120">튜티수</th>
		</tr>
		<?
		if ($mProgram != "") {
			$ExcelQuery = "select '', A.pName, (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "'), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "') - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "' and tMember.mGubun='A')), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "') - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "' and tMember.mGubun='B')), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='A' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "'), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on  tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='B' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "') from (select distinct getCodeName('PROGRAM', pCode, pCampus, 'k') pName, pCampus, pCode from tProgram where pCampus='" . str_replace("'","''", $mCampus) . "' and pSemester >='" . str_replace("'","''", $mSemFrom) . "' and pSemester <='" . str_replace("'","''", $mSemTo) . "' and pCode='" . str_replace("'","''", $mProgram) . "') A order by A.pName";
		} else {
			$ExcelQuery = "select '', A.pName, (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "'), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "') - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "' and tMember.mGubun='A')), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "') - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "' and tMember.mGubun='B')), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='A' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "'), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on  tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='B' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >='" . str_replace("'","''", $mSemFrom) . "' and tProgram.pSemester <='" . str_replace("'","''", $mSemTo) . "') from (select distinct getCodeName('PROGRAM', pCode, pCampus, 'k') pName, pCampus, pCode from tProgram where pCampus='" . str_replace("'","''", $mCampus) . "' and pSemester >='" . str_replace("'","''", $mSemFrom) . "' and pSemester <='" . str_replace("'","''", $mSemTo) . "') A order by A.pName";
		}
		$ExcelHeader = "번호,프로그램,총 과목수,튜터없는 과목,튜티없는 과목,튜터수,튜티수";
		$ExcelType = "N,N,D,D,D,D,D";
		$ExcelTitle = "통계_" . date('Ymd', time());	
		$ExcelWidth = "40,300,140,140,140,140,140";
		if ($mProgram != "") {
			$stmt = @mysqli_prepare($db, "select A.pName, (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >=? and tProgram.pSemester <=?), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=?) - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=? and tMember.mGubun='A')), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=?) - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=? and tMember.mGubun='B')), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='A' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >=? and tProgram.pSemester <=?), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on  tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='B' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >=? and tProgram.pSemester <=?) from (select distinct getCodeName('PROGRAM', pCode, pCampus, 'k') pName, pCampus, pCode from tProgram where pCampus=? and pSemester >=? and pSemester <=? and pCode=?) A order by A.pName");
			@mysqli_stmt_bind_param($stmt, 'ssssssssssssssssss', $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mCampus, $mSemFrom, $mSemTo, $mProgram);
		} else {
			$stmt = @mysqli_prepare($db, "select A.pName, (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >=? and tProgram.pSemester <=?), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=?) - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=? and tMember.mGubun='A')), ((select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=?) - (select count(distinct tMember.tID) from tMember inner join tTutoring on tMember.tID=tTutoring.ID inner join tProgram on tTutoring.pID=tProgram.ID where tProgram.pCampus=A.pCampus and tProgram.pCode=A.pCode and tProgram.pSemester >=? and tProgram.pSemester <=? and tMember.mGubun='B')), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='A' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >=? and tProgram.pSemester <=?), (select count(distinct tTutoring.ID) from tProgram inner join tTutoring on  tTutoring.pID=tProgram.ID inner join tMember on tTutoring.ID=tMember.tID and tMember.mGubun='B' where tProgram.pCode=A.pCode and tProgram.pCampus=A.pCampus and tProgram.pSemester >=? and tProgram.pSemester <=?) from (select distinct getCodeName('PROGRAM', pCode, pCampus, 'k') pName, pCampus, pCode from tProgram where pCampus=? and pSemester >=? and pSemester <=?) A order by A.pName");
			@mysqli_stmt_bind_param($stmt, 'sssssssssssssssss', $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mSemFrom, $mSemTo, $mCampus, $mSemFrom, $mSemTo);
		}
		@mysqli_stmt_execute($stmt);
		@mysqli_stmt_store_result($stmt);
		@mysqli_stmt_bind_result($stmt, $pName, $pCntA, $pCntB, $pCntC, $pCntD, $pCntE);
		$i = 1;
		while (@mysqli_stmt_fetch($stmt)) {
			if ($i % 2 == 0) {
				$OddLine = " class=\"oddline\"";
			} else {
				$OddLine = "";
			}
			?>
			<tr<?=$OddLine?>>
				<td class="center"><?=$i?></td>
				<td class="center"><?=htmlspecialchars($pName)?></td>
				<td class="center"><?=number_format($pCntA,0)?></td>
				<td class="center"><?=number_format($pCntB,0)?></td>
				<td class="center"><?=number_format($pCntC,0)?></td>
				<td class="center"><?=number_format($pCntD,0)?></td>
				<td class="center"><?=number_format($pCntE,0)?></td>
			</tr>
			<?
			$i++;
		}
		@mysqli_stmt_close($stmt);
		?>
		</table>
		<form name="ffexcel" method="post" action="/include/ose_nexcel.php?mid=<?=$mid?>">
		<input type="hidden" name="ExcelHeader" value="<?=htmlspecialchars($ExcelHeader)?>">
		<input type="hidden" name="ExcelTitle" value="<?=htmlspecialchars($ExcelTitle)?>">
		<input type="hidden" name="ExcelType" value="<?=htmlspecialchars($ExcelType)?>">
		<input type="hidden" name="ExcelWidth" value="<?=htmlspecialchars($ExcelWidth)?>">
<!--
		<input type="hidden" name="ExcelQuery" value="<?=encrypt_str($ExcelQuery, $enc_key)?>"> -->
		<input type="hidden" name="ExcelQuery" value="<?=htmlspecialchars($ExcelQuery)?>">

		</form>
		<script type="text/javascript">
			$("#mCampus").change(function() {
				showLoader();
				var ntime = parseInt(new Date().getTime().toString().substring(0, 10));
				var a = $("#mCampus").val();
				$.ajax({
					type: "POST",
					async: false,
					url: "/include/ose_ajax.php",
					dataType: "html",
					data: "mid=<?=$mid?>&act=getProgram&a="+a+"&ntime="+ntime,
					success: function(response, status, request) {
						$("#mProgram").find("option").remove().end().append(response);
					}
				});
				hideLoader();
			});
		</script>
		<?
		break;
	case "A04":
		//콘텐츠 관리
		if ($CanRead != "Y") {
			exit;
		}
		$cmid = getParam("cmid");
		if ($cmid == "") {
			$cmid = "m01_AK";
		}
		?>
		<form name="ffadmsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<div id="admcontents_sbox">
		<select name="cmid" OnChange="Javascript: showLoader(); document.ffadmsearch.submit();">
		<option value="m01_AK" <?if ($cmid == "m01_AK") echo "selected"?>>신청안내 국문 (신촌)</option>
		<option value="m01_AE" <?if ($cmid == "m01_AE") echo "selected"?>>신청안내 영문 (신촌)</option>
		<option value="m01_BK" <?if ($cmid == "m01_BK") echo "selected"?>>신청안내 국문 (원주)</option>
		<option value="m01_BE" <?if ($cmid == "m01_BE") echo "selected"?>>신청안내 영문 (원주)</option>
		<option value="m01_CK" <?if ($cmid == "m01_CK") echo "selected"?>>신청안내 국문 (국제)</option>
		<option value="m01_CE" <?if ($cmid == "m01_CE") echo "selected"?>>신청안내 영문 (국제)</option>
		<option value="m03_AK" <?if ($cmid == "m03_AK") echo "selected"?>>개인정보 수집 및 이용동의 국문 (신촌)</option>
		<option value="m03_AE" <?if ($cmid == "m03_AE") echo "selected"?>>개인정보 수집 및 이용동의 영문 (신촌)</option>
		<option value="m03_BK" <?if ($cmid == "m03_BK") echo "selected"?>>개인정보 수집 및 이용동의 국문 (원주)</option>
		<option value="m03_BE" <?if ($cmid == "m03_BE") echo "selected"?>>개인정보 수집 및 이용동의 영문 (원주)</option>
		<option value="m03_CK" <?if ($cmid == "m03_CK") echo "selected"?>>개인정보 수집 및 이용동의 국문 (국제)</option>
		<option value="m03_CE" <?if ($cmid == "m03_CE") echo "selected"?>>개인정보 수집 및 이용동의 영문 (국제)</option>
		</select>
		</div>
		</form>
		<div style="position:relative; float:left; width:920px; border:0; padding:0; margin:0; margin-top:15px;">
		<?
		include ($_SERVER['DOCUMENT_ROOT'] . '/include/ose_contents.php');
		?>
		</div>
		<?
		break;
	case "A05":
		//코드관리
		if ($CanRead != "Y") {
			exit;
		}
		$sOpt = getParam("sOpt");
		if ($sOpt == "") {
			$sOpt = "IPLIST";
		}
			
		if ($actt == "aok" && $CanWrite == "Y") {
			$cCode = getParam("cCode");
			$cCodeName = getParam("cCodeName");
			$cCodeNameE = getParam("cCodeNameE");
			$cCodeDesc = getParam("cCodeDesc");
			$cCodeRef = getParam("cCodeRef");
			$cOrder = getNumber(getParam("cOrder"));
			$cShow = getYN(getParam("cShow"));
			if ($cCode == "" || $cCodeName == "") {
				?>
				<script type="text/javascript">
					alert('코드 또는 설명을 입력하세요'); 
					history.go(-1);
				</script>
				<?
			} else {
				$cCnt = 0;
				$stmt = @mysqli_prepare($db, "select count(ID) from tCode where cCodeGroup=? and cCode=?");
				@mysqli_stmt_bind_param($stmt, 'ss', $sOpt, $cCode);
				@mysqli_stmt_execute($stmt);
				@mysqli_stmt_store_result($stmt);
				@mysqli_stmt_bind_result($stmt, $cCnt);
				@mysqli_stmt_fetch($stmt);
				@mysqli_stmt_close($stmt);
				if ($cCnt == 0) {
					@mysqli_autocommit($db, false);
					try {
						$stmt = @mysqli_prepare($db, "insert into tCode (cCodeGroup, cCode, cCodeName, cCodeNameE, cCodeDesc, cCodeRef, cShow, cOrder) values (?, ?, ?, ?, ?, ?, ?, ?)");
						@mysqli_stmt_bind_param($stmt, 'sssssssi', $sOpt, $cCode, $cCodeName, $cCodeNameE, $cCodeDesc, $cCodeRef, $cShow, $cOrder);
						$result = @mysqli_stmt_execute($stmt);
						if (!$result) {
							throw new Exception(mysqli_error($db));
						}
						$new_id = @mysqli_insert_id($db);
						@mysqli_stmt_close($stmt);
						@mysqli_commit($db);
						@addLog ($userno, "코드추가 [" . $sOpt . "] " . $cCode, $db);
					} catch(Exception $e) {
						$errMsg = $base_page . ": " . $e->getmessage();
						@systemLog($errMsg);
						@mysqli_rollback($db);
						?>
						<script type="text/javascript">
							alert('처리중 오류가 발생했습니다');
							history.go(-1);
						</script>
						<?
					}
					@mysqli_autocommit($db, true);
				} else {
					?>
					<script type="text/javascript">
						alert('동일한 코드가 존재합니다'); 
						history.go(-1);
					</script>
					<?
				}
			}
		}
			
		if ($actt == "mok" && $CanWrite == "Y" && $uid > 0) {
			$cCode = getParam("cCode");
			$cCodeName = getParam("cCodeName");
			$cCodeNameE = getParam("cCodeNameE");
			$cCodeDesc = getParam("cCodeDesc");
			$cCodeRef = getParam("cCodeRef");
			$cOrder = getNumber(getParam("cOrder"));
			$cShow = getYN(getParam("cShow"));
			if ($cCodeName == "") {
				?>
				<script type="text/javascript">
					alert('설명을 입력하세요'); 
					history.go(-1);
				</script>
				<?
			} else {
				@mysqli_autocommit($db, false);
				try {
					$stmt = @mysqli_prepare($db, "update tCode Set cCodeName=?, cCodeNameE=?, cCodeDesc=?, cCodeRef=?, cShow=?, cOrder=? where ID=?"); 
					@mysqli_stmt_bind_param($stmt, 'sssssii', $cCodeName, $cCodeNameE, $cCodeDesc, $cCodeRef, $cShow, $cOrder, $uid);
					$result = @mysqli_stmt_execute($stmt);
					if (!$result) {
						throw new Exception(mysqli_error($db));
					}
					@mysqli_stmt_close($stmt);
					@mysqli_commit($db);
					@addLog ($userno, "코드수정 [" . $sOpt . "] " . $cCode, $db);
				} catch(Exception $e) {
					$errMsg = $base_page . ": " . $e->getmessage();
					@systemLog($errMsg);
					@mysqli_rollback($db);
					?>
					<script type="text/javascript">
						alert('처리중 오류가 발생했습니다');
						history.go(-1);
					</script>
					<?
				}
				@mysqli_autocommit($db, true);
			}
		}

		if ($actt == "mok2" && $CanWrite == "Y") {
			$cCodeDesc = getParam("cCodeDesc");
			@mysqli_autocommit($db, false);
			try {
				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='IPLIST'"); 
				@mysqli_stmt_bind_param($stmt, 's', $cCodeDesc);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);
				@mysqli_commit($db);
				@addLog ($userno, "IP접근목록수정", $db);
			} catch(Exception $e) {
				$errMsg = $base_page . ": " . $e->getmessage();
				@systemLog($errMsg);
				@mysqli_rollback($db);
				?>
				<script type="text/javascript">
					alert('처리중 오류가 발생했습니다');
					history.go(-1);
				</script>
				<?
			}
			@mysqli_autocommit($db, true);
		}

		if ($actt == "mok3" && $CanWrite == "Y") {
			$cCodeNameA = getParam("cCodeNameA");
			$cCodeNameEA = getParam("cCodeNameEA");
			$cSMSA = getParam("cSMSA");
			$cEmailA = getParam("cEmailA");
			$cLangA = getParam("cLangA");
			$cSaveDayA = getNumber(getParam("cSaveDayA"));
			$cCodeNameB = getParam("cCodeNameB");
			$cCodeNameEB = getParam("cCodeNameEB");
			$cSMSB = getParam("cSMSB");
			$cEmailB = getParam("cEmailB");
			$cLangB = getParam("cLangB");
			$cSaveDayB = getNumber(getParam("cSaveDayB"));
			$cCodeNameC = getParam("cCodeNameC");
			$cCodeNameEC = getParam("cCodeNameEC");
			$cSMSC = getParam("cSMSC");
			$cEmailC = getParam("cEmailC");
			$cLangC = getParam("cLangC");
			$cSaveDayC = getNumber(getParam("cSaveDayC"));
			@mysqli_autocommit($db, false);
			try {
				$stmt = @mysqli_prepare($db, "update tCode Set cCodeName=?, cCodeNameE=? where cCodeGroup='CAMPUS' and cCode='A'");
				@mysqli_stmt_bind_param($stmt, 'ss', $cCodeNameA, $cCodeNameEA);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeName=?, cCodeNameE=? where cCodeGroup='CAMPUS' and cCode='B'");
				@mysqli_stmt_bind_param($stmt, 'ss', $cCodeNameB, $cCodeNameEB);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeName=?, cCodeNameE=? where cCodeGroup='CAMPUS' and cCode='C'");
				@mysqli_stmt_bind_param($stmt, 'ss', $cCodeNameC, $cCodeNameEC);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='EMAILA'");
				@mysqli_stmt_bind_param($stmt, 's', $cEmailA);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='EMAILB'");
				@mysqli_stmt_bind_param($stmt, 's', $cEmailB);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='EMAILC'");
				@mysqli_stmt_bind_param($stmt, 's', $cEmailC);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='SMSA'");
				@mysqli_stmt_bind_param($stmt, 's', $cSMSA);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='SMSB'");
				@mysqli_stmt_bind_param($stmt, 's', $cSMSB);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='SMSC'");
				@mysqli_stmt_bind_param($stmt, 's', $cSMSC);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='LANGA'");
				@mysqli_stmt_bind_param($stmt, 's', $cLangA);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='LANGB'");
				@mysqli_stmt_bind_param($stmt, 's', $cLangB);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='LANGC'");
				@mysqli_stmt_bind_param($stmt, 's', $cLangC);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='SAVEDAYA'");
				@mysqli_stmt_bind_param($stmt, 's', $cSaveDayA);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='SAVEDAYB'");
				@mysqli_stmt_bind_param($stmt, 's', $cSaveDayB);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);

				$stmt = @mysqli_prepare($db, "update tCode Set cCodeDesc=? where cCodeGroup='SYSTEM' and cCode='SAVEDAYC'");
				@mysqli_stmt_bind_param($stmt, 's', $cSaveDayC);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);
				@mysqli_commit($db);
				@addLog ($userno, "캠퍼스 코드 수정", $db);
			} catch(Exception $e) {
				$errMsg = $base_page . ": " . $e->getmessage();
				@systemLog($errMsg);
				@mysqli_rollback($db);
				?>
				<script type="text/javascript">
					alert('처리중 오류가 발생했습니다');
					history.go(-1);
				</script>
				<?
			}
			@mysqli_autocommit($db, true);
		}

		if ($actt == "dok" && $CanDelete == "Y" && $uid > 0) {
			if ($sOpt == "PROGRAM" || $sOpt == "SOSOK" || $sOpt == "GRADE" || $sOpt == "CREDIT") {
				$cCode = getParam("cCode");
				if ($sOpt == "PROGRAM") {
					$stmt = @mysqli_prepare($db, "select count(ID) from tProgram where pCode=?");
				} elseif ($sOpt == "SOSOK") {
					$stmt = @mysqli_prepare($db, "select count(ID) from tMember where mSosok=?");
				} elseif ($sOpt == "GRADE") {
					$stmt = @mysqli_prepare($db, "select count(ID) from tMember where mGrade=?");
				} elseif ($sOpt == "CREDIT") {
					$stmt = @mysqli_prepare($db, "select count(ID) from tMember where mCredit=?");
				}
				@mysqli_stmt_bind_param($stmt, 's', $cCode);
				@mysqli_stmt_execute($stmt);
				@mysqli_stmt_store_result($stmt);
				@mysqli_stmt_bind_result($stmt, $cCnt);
				@mysqli_stmt_fetch($stmt);
				@mysqli_stmt_close($stmt);
				if ($cCnt > 0) {
					?>
					<script type="text/javascript">
						alert('사용중인 코드는 삭제할 수 없습니다');
						history.go(-1);
					</script>
					<?
				} else {
					@mysqli_autocommit($db, false);
					try {
						$stmt = @mysqli_prepare($db, "delete from tCode where ID=?"); 
						@mysqli_stmt_bind_param($stmt, 'i', $uid);
						$result = @mysqli_stmt_execute($stmt);
						if (!$result) {
							throw new Exception(mysqli_error($db));
						}
						@mysqli_stmt_close($stmt);
						@mysqli_commit($db);
						@addLog ($userno, "코드삭제 [" . $sOpt . "] " . $cCode, $db);
					} catch(Exception $e) {
						$errMsg = $base_page . ": " . $e->getmessage();
						@systemLog($errMsg);
						@mysqli_rollback($db);
						?>
						<script type="text/javascript">
							alert('처리중 오류가 발생했습니다');
							history.go(-1);
						</script>
						<?
					}
					@mysqli_autocommit($db, true);
				}
			}
		}

		$cGroupName = array ("IP제한목록", "캠퍼스", "학기", "프로그램", "개설구분", "참여자구분", "소속", "학년", "성적");
		$cGroup = array ("IPLIST", "CAMPUS", "SEMESTER", "PROGRAM", "OGUBUN", "MGUBUN", "SOSOK", "GRADE", "CREDIT");
		?>
		<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>" style="margin-top:10px;">
		<div id="admcontents_sbox">
		<?
		for ($i = 0; $i < sizeof($cGroup); $i++) {
			?>
			<input type="radio" name="sOpt" value="<?=htmlspecialchars($cGroup[$i])?>" <?if ($sOpt == $cGroup[$i]) echo "checked";?>> <?=htmlspecialchars($cGroupName[$i])?>
			<?
		}
		?>
		<a href="#" OnClick="Javascript: showLoader(); document.ffsearch.submit(); return false;" class="button icon search">조회</a>
		</div>
		</form>
		<form name="ffdata" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<input type="hidden" name="actt">
		<input type="hidden" name="sOpt" value="<?=$sOpt?>">
		<table class="ctable" width="100%" style="margin-top:10px;">
			<?
			if ($sOpt == "IPLIST") {
				?>
				<tr>
					<th>IP목록 (콤마구분하여 입력)</th>
					<th width="80">작업</th>
				</tr>
				<?
			} elseif ($sOpt == "CAMPUS") {
				?>
				<tr>
					<th width="40">번호</th>
					<th width="80">코드</th>
					<th>캠퍼스명(국)</th>
					<th>캠퍼스명(영)</th>
					<th>발송이메일</th>
					<th>SMS발송번호</th>
					<th width="80">기본언어</th>
					<th width="150">개인정보 보유기한(일)</th>
					<th width="80">작업</th>
				</tr>
				<?
			} elseif ($sOpt == "GRADE" || $sOpt == "PROGRAM")  {
				?>
				<tr>
					<th width="40">번호</th>
					<th width="80">코드</th>
					<th width="120">상위코드</th>
					<th>설명(국문)</th>
					<th>설명(영문)</th>
					<th width="60">표시순서</th>
					<th width="60">사용여부</th>
					<th width="150">작업</th>
				</tr>
				<?
			} else {
				?>
				<tr>
					<th width="40">번호</th>
					<th width="80">코드</th>
					<th>설명(국문)</th>
					<th>설명(영문)</th>
					<th width="60">표시순서</th>
					<th width="60">사용여부</th>
					<th width="150">작업</th>
				</tr>
				<?
			}
			if (($sOpt == "GRADE" || $sOpt == "PROGRAM" || $sOpt == "CREDIT") && $CanWrite == "Y") {
				?>
				<tr class="bg2">
					<td>-<input type="hidden" name="uid_0" id="uid_0" value=""></td>
					<td><input type="text" name="cCode_0" id="cCode_0" style="width:75px;" maxlength="20"></td>
					<?
					if ($sOpt == "GRADE") {
						?>
						<td><?=makeInput($db, $CanWrite, "select", "SOSOK", "cCodeRef_0", "cCodeRef_0", "", "", "", "", "", "", "")?></td>
						<?
					} elseif ($sOpt == "PROGRAM") {
						?>
						<td><?=makeInput($db, $CanWrite, "select", "CAMPUS", "cCodeRef_0", "cCodeRef_0", "", "", "", "", "", "", "")?></td>
						<?
					}
					?>
					<td><input type="text" name="cCodeName_0" id="cCodeName_0" style="width:90%; " maxlength="200"></td>
					<td><input type="text" name="cCodeNameE_0" id="cCodeNameE_0" style="width:90%; " maxlength="200"></td>
					<td><input type="text" name="cOrder_0" id="cOrder_0" style="width:40px;  ime-mode:disabled" maxlength="200" OnKeyPress="Javascript: return onlyNum(this);" OnKeyUp="Javascript: this.value=number_format(this.value);"></td>
					<td><input type="checkbox" name="cShow_0" id="cShow_0" value="Y"></td>
					<td><a href="#" OnClick="Javascript: updateCode('0', 'aok'); return false;" class="button icon add">추가</a></td>
				</tr>
			<?
			}
			if ($sOpt == "IPLIST") {
				$stmt = @mysqli_prepare($db, "select ID, cCode, cCodeName, cCodeNameE, cCodeDesc, cCodeRef, cShow, cOrder from tCode where cCodeGroup='SYSTEM' and cCode='IPLIST'");
				@mysqli_stmt_execute($stmt);
				@mysqli_stmt_store_result($stmt);
				@mysqli_stmt_bind_result($stmt, $uid, $cCode, $cCodeName, $cCodeNameE, $cCodeDesc, $cCodeRef, $cShow, $cOrder);
				$i = 1;
				while (@mysqli_stmt_fetch($stmt)) {
					if ($i % 2 == 0) {
						$OddLine = " class=\"oddline\"";
					} else {
						$OddLine = "";
					}
					?>
					<tr<?=$OddLine?>>
						<td align="center"><input type="text" name="cCodeDesc" id="cCodeDesc" style="width:98%;" value="<?=htmlspecialchars($cCodeDesc)?>"></td>
						<td class="center"><?if ($CanWrite == "Y") {?><a href="#" OnClick="Javascript: showLoader(); document.ffdata.actt.value='mok2'; document.ffdata.submit(); return false;" class="button icon edit">수정</a><?}?></td>
					</tr>
					<?
					$i++;
				}
				@mysqli_stmt_close($stmt);
			} elseif ($sOpt == "CAMPUS") {
				$stmt = @mysqli_prepare($db, "select ID, cCode, cCodeName, cCodeNameE, (select CC.cCodeDesc from tCode CC where CC.cCodeGroup='SYSTEM' and CC.cCode=concat('EMAIL', tCode.cCode)), (select CC.cCodeDesc from tCode CC where CC.cCodeGroup='SYSTEM' and CC.cCode=concat('SMS', tCode.cCode)), (select CC.cCodeDesc from tCode CC where CC.cCodeGroup='SYSTEM' and CC.cCode=concat('LANG', tCode.cCode)), (select CC.cCodeDesc from tCode CC where CC.cCodeGroup='SYSTEM' and CC.cCode=concat('SAVEDAY', tCode.cCode)), cShow, cOrder from tCode where cCodeGroup='CAMPUS'");
				@mysqli_stmt_execute($stmt);
				@mysqli_stmt_store_result($stmt);
				@mysqli_stmt_bind_result($stmt, $uid, $cCode, $cCodeName, $cCodeNameE, $cEmail, $cSMS, $cLang, $cSaveDay, $cShow, $cOrder);
				$i = 1;
				while (@mysqli_stmt_fetch($stmt)) {
					if ($i % 2 == 0) {
						$OddLine = " class=\"oddline\"";
					} else {
						$OddLine = "";
					}
					?>
					<tr<?=$OddLine?>>
						<td align="center"><?=$i?></td>
						<td align="center"><?=htmlspecialchars($cCode)?></td>
						<td align="center"><input type="text" name="cCodeName<?=$cCode?>" style="width:90%;" value="<?=htmlspecialchars($cCodeName)?>"></td>
						<td align="center"><input type="text" name="cCodeNameE<?=$cCode?>" style="width:90%;" value="<?=htmlspecialchars($cCodeNameE)?>"></td>
						<td align="center"><input type="text" name="cEmail<?=$cCode?>" id="cEmail<?=$cCode?>" style="width:90%;" value="<?=htmlspecialchars($cEmail)?>"></td>
						<td align="center"><input type="text" name="cSMS<?=$cCode?>" id="cSMS<?=$cCode?>" style="width:90%;" value="<?=htmlspecialchars($cSMS)?>"></td>
						<td align="center"><select name="cLang<?=$cCode?>"><option value="k" <?if ($cLang == "k") echo "selected";?>>국문</option><option value="e" <?if ($cLang == "e") echo "selected";?>>영문</option></select></td>
						<td align="center"><input type="text" name="cSaveDay<?=$cCode?>" style="width:90%;" value="<?=number_format($cSaveDay,0)?>" OnKeyPress="Javascript: return onlyNum(this);" OnKeyUp="Javascript: this.value=number_format(this.value);"></td>
						<?
						if ($i == 1) {
							?>
							<td class="center" rowspan="3"><?if ($CanWrite == "Y") {?><a href="#" OnClick="Javascript: if (validateEmail('cEmailA', 'Y') && validateContact('cSMSA', 'Y') && validateEmail('cEmailB', 'Y') && validateContact('cSMSB', 'Y') && validateEmail('cEmailC', 'Y') && validateContact('cSMSC', 'Y')) { showLoader(); document.ffdata.actt.value='mok3'; document.ffdata.submit(); } return false;" class="button icon edit">수정</a><?}?></td>
							<?
						}
						?>
					</tr>
					<?
					$i++;
				}
				@mysqli_stmt_close($stmt);
			} else {
				$stmt = @mysqli_prepare($db, "select ID, cCode, cCodeName, cCodeNameE, cCodeDesc, cCodeRef, cShow, cOrder from tCode where cCodeGroup=? order by cCodeRef ASC, cOrder ASC");
				@mysqli_stmt_bind_param($stmt, 's', $sOpt);
				@mysqli_stmt_execute($stmt);
				@mysqli_stmt_store_result($stmt);
				@mysqli_stmt_bind_result($stmt, $uid, $cCode, $cCodeName, $cCodeNameE, $cCodeDesc, $cCodeRef, $cShow, $cOrder);
				$i = 1;
				while (@mysqli_stmt_fetch($stmt)) {
					if ($i % 2 == 0) {
						$OddLine = " class=\"oddline\"";
					} else {
						$OddLine = "";
					}
					?>
					<tr<?=$OddLine?>>
						<td class="center"><?=$i?><input type="hidden" name="uid[]" id="uid_<?=$i?>" value="<?=$uid?>"></td>
						<td align="center"><?=htmlspecialchars($cCode)?><input type="hidden" name="cCode_<?=$i?>" id="cCode_<?=$i?>" value="<?=htmlspecialchars($cCode)?>"></td>
						<?
						if ($sOpt == "GRADE") {
							?>
							<td align="center"><?=makeInput($db, $CanWrite, "select", "SOSOK", "cCodeRef_" . $i, "cCodeRef_" . $i, "", "", $cCodeRef, "", "", "", "")?></td>
							<?
						} elseif ($sOpt == "PROGRAM") {
							?>
							<td align="center"><?=makeInput($db, $CanWrite, "select", "CAMPUS", "cCodeRef_" . $i, "cCodeRef_" . $i, "", "", $cCodeRef, "", "", "", "")?></td>
							<?
						}
						?>
						<td align="center"><input type="text" name="cCodeName_<?=$i?>" id="cCodeName_<?=$i?>" style="width:90%; " maxlength="100" value="<?=htmlspecialchars($cCodeName)?>"></td>
						<td align="center"><input type="text" name="cCodeNameE_<?=$i?>" id="cCodeNameE_<?=$i?>" style="width:90%; " maxlength="100" value="<?=htmlspecialchars($cCodeNameE)?>"></td>
						<td align="center"><input type="text" name="cOrder_<?=$i?>" id="cOrder_<?=$i?>" style="width:40px;  ime-mode:disabled" maxlength="200" value="<?=number_format($cOrder,0)?>" OnKeyPress="Javascript: return onlyNum(this);" OnKeyUp="Javascript: this.value=number_format(this.value);"></td>
						<?
						if ($sOpt == "SEMESTER" || $sOpt == "OGUBUN" || $sOpt == "MGUBUN") {
							?>
							<td align="center"><?=$cShow?></td>
							<?
						} else {
							?>
							<td align="center"><input type="checkbox" name="cShow_<?=$i?>" id="cShow_<?=$i?>" value="Y" <?if ($cShow=="Y") echo "checked";?>></td>
							<?
						}
						?>
						<td class="center"><?if ($CanWrite == "Y") {?><a href="#" OnClick="Javascript: updateCode('<?=$i?>', 'mok'); return false;" class="button icon edit">수정</a><?}?><?if ($CanDelete == "Y" && ($sOpt == "PROGRAM" || $sOpt == "GRADE" || $sOpt == "CREDIT")) {?><a href="#" OnClick="Javascript: if (confirm('정말로 삭제하겠습니까?')) { updateCode('<?=$i?>', 'dok'); } return false;" class="button danger icon trash" style="margin-left:5px;">삭제</a><?}?></td>
					</tr>
					<?
					$i++;
				}
				@mysqli_stmt_close($stmt);
			}
			?>
		</table>
		</form>
		<form name="ffadmin" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<input type="hidden" name="actt">
		<input type="hidden" name="uid">
		<input type="hidden" name="cCode">
		<input type="hidden" name="cCodeName">
		<input type="hidden" name="cCodeNameE">
		<input type="hidden" name="cCodeDesc">
		<input type="hidden" name="cCodeRef">
		<input type="hidden" name="cShow">
		<input type="hidden" name="cOrder">
		<input type="hidden" name="sOpt" value="<?=htmlspecialchars($sOpt)?>">
		<input type="hidden" name="skeyword" value="<?=htmlspecialchars($skeyword)?>">
		</form>

		<script type="text/javascript">
			function updateCode(idx, actname) {
				var sOpt = '<?=$sOpt?>';
				document.ffadmin.uid.value=document.getElementById('uid_'+idx).value;
				document.ffadmin.cCode.value=document.getElementById('cCode_'+idx).value;
				document.ffadmin.cCodeName.value=document.getElementById('cCodeName_'+idx).value;
				document.ffadmin.cCodeNameE.value=document.getElementById('cCodeNameE_'+idx).value;
				document.ffadmin.cOrder.value=document.getElementById('cOrder_'+idx).value;
				if (sOpt == "PROGRAM" || sOpt == "GRADE" || sOpt == "CREDIT" || sOpt == "SOSOK") {
					if (document.getElementById('cShow_'+idx).checked == true) {
						document.ffadmin.cShow.value="Y";
					} else {
						document.ffadmin.cShow.value="N";
					}
				} else {
					document.ffadmin.cShow.value="Y";
				}
				if (sOpt == "GRADE" || sOpt == "PROGRAM") {
					document.ffadmin.cCodeRef.value=document.getElementById('cCodeRef_'+idx).value;
				}
				document.ffadmin.actt.value=actname;
				showLoader();
				document.ffadmin.submit();
			}
		</script>
		<?
		break;
	case "A06":
		if ($CanRead != "Y") {
			exit;
		}
		//관리자관리
		if ($actt == "aok" && $CanWrite == "Y") {
			$sUserID = getParam("sUserID");
			$sName = getParam("sName");
			$sEmail = getParam("sEmail");
			$sContact = getParam("sContact");
			$sPassword = getParam("sPassword");
			$sAuth = getParam("sAuth");
			$sGubun = getYN(getParam("sGubunA")) . getYN(getParam("sGubunB")) . getYN(getParam("sGubunC"));
			if ($sAuth != "A" && $sAuth != "B") {
				$sAuth = "C";
			}

			if ($sUserID == "" || $sPassword == "") {
				?>
				<script type="text/javascript">
					alert('아이디 또는 비밀번호를 입력하세요'); 
					history.go(-1);
				</script>
				<?
			} else {
				$cCnt = 0;
				$stmt = @mysqli_prepare($db, "select count(ID) from tAdmin where sUserID=?");
				@mysqli_stmt_bind_param($stmt, 's', $sUserID);
				@mysqli_stmt_execute($stmt);
				@mysqli_stmt_store_result($stmt);
				@mysqli_stmt_bind_result($stmt, $cCnt);
				@mysqli_stmt_fetch($stmt);
				@mysqli_stmt_close($stmt);
				if ($cCnt == 0) {
					@mysqli_autocommit($db, false);
					try {
						$stmt = @mysqli_prepare($db, "insert into tAdmin (sUserID, sName, sEmail, sContact, sPassword, sAuth, sGubun) values (?, ?, dbenc(?, ?), dbenc(?, ?), password(?), ?, ?)");
						@mysqli_stmt_bind_param($stmt, 'sssssssss', $sUserID, $sName, $enc_key, $sEmail, $enc_key, $sContact, $sPassword, $sAuth, $sGubun);
						$result = @mysqli_stmt_execute($stmt);
						if (!$result) {
							throw new Exception(mysqli_error($db));
						}
						$new_id = @mysqli_insert_id($db);
						@mysqli_stmt_close($stmt);
						@mysqli_commit($db);
						@addLog ($userno, "관리자 추가: " . $sUserID, $db);
					} catch(Exception $e) {
						$errMsg = $base_page . ": " . $e->getmessage();
						@systemLog($errMsg);
						@mysqli_rollback($db);
						?>
						<script type="text/javascript">
							alert('처리중 오류가 발생했습니다');
							history.go(-1);
						</script>
						<?
					}
					@mysqli_autocommit($db, true);
				} else {
					?>
					<script type="text/javascript">
						alert('동일한 아이디가 존재합니다'); 
						history.go(-1);
					</script>
					<?
				}
			}
		}
		if ($actt == "mok" && $uid > 0 && $CanWrite == "Y") {
			$sName = getParam("sName");
			$sEmail = getParam("sEmail");
			$sContact = getParam("sContact");
			$sPassword = getParam("sPassword");
			$sAuth = getParam("sAuth");
			$sGubun = getYN(getParam("sGubunA")) . getYN(getParam("sGubunB")) . getYN(getParam("sGubunC"));
			if ($sAuth != "A" && $sAuth != "B") {
				$sAuth = "C";
			}

			@mysqli_autocommit($db, false);
			try {
				if ($sPassword != "") {
					$stmt = @mysqli_prepare($db, "update tAdmin Set sName=?, sEmail=dbenc(?, ?), sContact=dbenc(?, ?), sPassword=password(?), sAuth=?, sGubun=? where ID=?"); 
					@mysqli_stmt_bind_param($stmt, 'ssssssssi', $sName, $enc_key, $sEmail, $enc_key, $sContact, $sPassword, $sAuth, $sGubun, $uid);
				} else {
					$stmt = @mysqli_prepare($db, "update tAdmin Set sName=?, sEmail=dbenc(?, ?), sContact=dbenc(?, ?), sAuth=?, sGubun=? where ID=?"); 
					@mysqli_stmt_bind_param($stmt, 'sssssssi', $sName, $enc_key, $sEmail, $enc_key, $sContact, $sAuth, $sGubun, $uid);
				}
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);
				@mysqli_commit($db);
				@addLog ($userno, "관리자 수정: " . getUserID($db, $uid), $db);
			} catch(Exception $e) {
				$errMsg = $base_page . ": " . $e->getmessage();
				@systemLog($errMsg);
				@mysqli_rollback($db);
				?>
				<script type="text/javascript">
					alert('처리중 오류가 발생했습니다'); 
					history.go(-1);
				</script>
				<?
			}
			@mysqli_autocommit($db, true);
		}
		if ($actt == "dok" && $uid > 0 && $CanDelete == "Y") {
			@mysqli_autocommit($db, false);
			try {
				$tempID = getUserID($db, $uid);
				$stmt = @mysqli_prepare($db, "delete from tAdmin where ID=?");
				@mysqli_stmt_bind_param($stmt, 'i', $uid);
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);
				@mysqli_commit($db);
				@addLog ($userno, "관리자 삭제: " . $tempID, $db);
			} catch(Exception $e) {
				$errMsg = $base_page . ": " . $e->getmessage();
				@systemLog($errMsg);
				@mysqli_rollback($db);
				?>
				<script type="text/javascript">
					alert('처리중 오류가 발생했습니다');
					history.go(-1);
				</script>
				<?
			}
			@mysqli_autocommit($db, true);
		}

		$StrAuth = makeInput($db, $CanWrite, "select", "ADM", "sAuth", "sAuth", "", "", "", "Y", "", "", "Y");
		?>
		<form name="ffsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<div id="admcontents_sbox">
		※ <span style="color:red;">관리자 업무 종료시 삭제하시마시고 권한을 &quot;없음&quot;으로 설정하시기 바랍니다.</span><br>※ 비밀번호는 영문, 숫자 및 특수문자 조합으로 9~15자리로 지정해야 합니다.
		</div>
		</form>
		<form name="ffdata">
		<table class="ctable" width="100%" style="margin-top:10px;">
		<tr>
			<th width="40">번호</th>
			<th>아이디</th>
			<th>이름</th>
			<th>이메일</th>
			<th>연락처</th>
			<th>권한</th>
			<th>신촌</th>
			<th>원주</th>
			<th>국제</th>
			<th>비밀번호</th>
			<th width="160">작업</th>
		</tr>
		<tr class="bg2">
			<td>-<input type="hidden" name="uid_0" id="uid_0" value=""></td>
			<td><input type="text" name="sUserID_0" id="sUserID_0" style="width:90%;" maxlength="20"></td>
			<td><input type="text" name="sName_0" id="sName_0" style="width:90%;" maxlength="33"></td>
			<td><input type="text" name="sEmail_0" id="sEmail_0" style="width:90%;" maxlength="200"></td>
			<td><input type="text" name="sContact_0" id="sContact_0" style="width:90%;" maxlength="200"></td>
			<td><select name="sAuth_0" id="sAuth_0"><?=$StrAuth?></select></td>
			<td><?=makeInput($db, $CanWrite, "checkbox", "", "sGubunA_0", "sGubunA_0", "", "", "", "Y", "", "", "")?></td>
			<td><?=makeInput($db, $CanWrite, "checkbox", "", "sGubunB_0", "sGubunB_0", "", "", "", "Y", "", "", "")?></td>
			<td><?=makeInput($db, $CanWrite, "checkbox", "", "sGubunC_0", "sGubunC_0", "", "", "", "Y", "", "", "")?></td>
			<td><input type="password" name="sPassword_0" id="sPassword_0" style="width:90%;" maxlength="20"></td>
			<td><a href="#" OnClick="Javascript: updateCode('0', 'aok'); return false;" class="button icon add">추가</a></td>
		</tr>
		<?
		$stmt = @mysqli_prepare($db, "select ID, sUserID, sName, dbdec(?, sEmail), dbdec(?, sContact), sAuth, substring(sGubun,1,1), substring(sGubun,2,1), substring(sGubun,3,1) from tAdmin order by ID ASC");
		@mysqli_stmt_bind_param($stmt, 'ss', $enc_key, $enc_key);
		@mysqli_stmt_execute($stmt);
		@mysqli_stmt_store_result($stmt);
		@mysqli_stmt_bind_result($stmt, $uid, $sUserID, $sName, $sEmail, $sContact, $sAuth, $sGubunA, $sGubunB, $sGubunC);
		$i = 1;
		while (@mysqli_stmt_fetch($stmt)) {
			if ($i % 2 == 0) {
				$OddLine = " class=\"oddline\"";
			} else {
				$OddLine = "";
			}
			?>
			<tr<?=$OddLine?>>
				<td class="center"><?=$i?><input type="hidden" name="uid[]" id="uid_<?=$i?>" value="<?=$uid?>"></td>
				<td align="center"><?=htmlspecialchars($sUserID)?></td>
				<td align="center"><input type="text" name="sName_<?=$i?>" id="sName_<?=$i?>" style="width:90%;" maxlength="33" value="<?=htmlspecialchars($sName)?>"><input type="hidden" name="sUserID_<?=$i?>" id="sUserID_<?=$i?>"></td>
				<td align="center"><input type="text" name="sEmail_<?=$i?>" id="sEmail_<?=$i?>" style="width:90%;" maxlength="200" value="<?=htmlspecialchars($sEmail)?>"></td>
				<td align="center"><input type="text" name="sContact_<?=$i?>" id="sContact_<?=$i?>" style="width:90%;" maxlength="200" value="<?=htmlspecialchars($sContact)?>"></td>
				<td align="center"><select name="sAuth_<?=$i?>" id="sAuth_<?=$i?>"><?=str_replace("value=\"" . $sAuth . "\"", "value=\"" . $sAuth . "\" selected", $StrAuth)?></select></td>
				<td align="center"><?=makeInput($db, $CanWrite, "checkbox", "", "sGubunA_" . $i, "sGubunA_" . $i, "", "", $sGubunA, "Y", "", "", "")?></td>
				<td align="center"><?=makeInput($db, $CanWrite, "checkbox", "", "sGubunB_" . $i, "sGubunB_" . $i, "", "", $sGubunB, "Y", "", "", "")?></td>
				<td align="center"><?=makeInput($db, $CanWrite, "checkbox", "", "sGubunC_" . $i, "sGubunC_" . $i, "", "", $sGubunC, "Y", "", "", "")?></td>
				<td align="center"><input type="password" name="sPassword_<?=$i?>" id="sPassword_<?=$i?>" style="width:90%;" maxlength="20"></td>
				<td class="center"><?if ($CanWrite=="Y") {?><a href="#" OnClick="Javascript: updateCode('<?=$i?>', 'mok'); return false;" class="button icon edit">수정</a><?}?><?if ($CanDelete=="Y") {?><a href="#" OnClick="Javascript: if (confirm('정말로 삭제하겠습니까?')) { updateCode('<?=$i?>', 'dok'); } return false;" class="button danger icon trash" style="margin-left:5px;">삭제</a><?}?></td>
			</tr>
			<?
			$i++;
		}
		@mysqli_stmt_close($stmt);
		?>
		</table>
		</form>
		<form name="ffadmin" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<input type="hidden" name="actt">
		<input type="hidden" name="uid">
		<input type="hidden" name="sUserID">
		<input type="hidden" name="sName">
		<input type="hidden" name="sEmail">
		<input type="hidden" name="sContact">
		<input type="hidden" name="sPassword">
		<input type="hidden" name="sAuth">
		<input type="hidden" name="sGubunA">
		<input type="hidden" name="sGubunB">
		<input type="hidden" name="sGubunC">
		</form>

		<script type="text/javascript">
			function updateCode(idx, actname) {
				document.ffadmin.uid.value=document.getElementById('uid_'+idx).value;
				document.ffadmin.sUserID.value=document.getElementById('sUserID_'+idx).value;
				document.ffadmin.sName.value=document.getElementById('sName_'+idx).value;
				document.ffadmin.sEmail.value=document.getElementById('sEmail_'+idx).value;
				document.ffadmin.sContact.value=document.getElementById('sContact_'+idx).value;
				document.ffadmin.sPassword.value=document.getElementById('sPassword_'+idx).value;
				document.ffadmin.sAuth.value=document.getElementById('sAuth_'+idx).value;
				if (document.getElementById('sGubunA_'+idx).checked == true) {
					document.ffadmin.sGubunA.value="Y";
				} else {
					document.ffadmin.sGubunA.value="N";
				}
				if (document.getElementById('sGubunB_'+idx).checked == true) {
					document.ffadmin.sGubunB.value="Y";
				} else {
					document.ffadmin.sGubunB.value="N";
				}
				if (document.getElementById('sGubunC_'+idx).checked == true) {
					document.ffadmin.sGubunC.value="Y";
				} else {
					document.ffadmin.sGubunC.value="N";
				}
				if (document.ffadmin.sGubunA.value == "N" && document.ffadmin.sGubunB.value == "N" && document.ffadmin.sGubunC.value == "N") {
					alert("캠퍼스를 선택하세요");
					return false;
				} else {
					document.ffadmin.actt.value=actname;
					if ((idx == "0" && validateUserID("sUserID_0", "Y") && validatePassword("sPassword_0", "Y")) || (idx != "0" && validatePassword("sPassword_"+idx, "N"))) {
						showLoader();
						document.ffadmin.submit();
					}
				}
			}
		</script>
		<?
		break;
	case "A07":
		if ($CanRead != "Y") {
			exit;
		}
		$sFrom = getParam("sFrom");
		if ($sFrom == "") {
			$sFrom = date('Y-m-d', time()-24*60*60*7);
		}
		$sTo = getParam("sTo");
		if ($sTo == "") {
			$sTo = date('Y-m-d', time());
		}
		?>
		<form name="ffadmsearch" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<div id="admcontents_sbox">
		접속기간 <?=makeInput($db, $CanWrite, "date", "", "sFrom", "sFrom", " style=\"width:80px;\"", "", getCal($sFrom), "", "", "", "")?> ~ <?=makeInput($db, $CanWrite, "date", "", "sTo", "sTo", " style=\"width:80px;\"", "", getCal($sTo), "", "", "", "")?> <a href="#" OnClick="Javascript: showLoader(); document.ffadmsearch.submit(); return false;" class="button icon search">조회</a><a href="#" OnClick="Javascript: document.ffexcel.submit(); return false;" class="button icon download">엑셀다운</a>
		</div>
		</form>
		<table class="ctable" width="100%">
		<tr>
			<th width="80">번호</th>
			<th>아이디</th>
			<th>이름</th>
			<th>접속일시</th>
			<th>IP</th>
			<th>작업내용</th>
		</tr>
		<?
		$sFromTime = strtotime($sFrom . " 00:00:00");
		$sToTime = strtotime($sTo . " 23:59:59");

		$ExcelQuery = "select tLog.ID, (select sUserID from tAdmin where ID=aID), (select sName from tAdmin where ID=aID), tLog.aDate, tLog.aIP, tLog.aTask from tLog where aDate >=" . $sFromTime . " and aDate <=" . $sToTime . " order by aDate DESC";
		$ExcelHeader = "번호,아이디,이름,접속일시,IP,작업내용";
		$ExcelType = "N,N,N,T,N,N";
		$ExcelTitle = "접속기록_" . date('Ymd', time());	
		$ExcelWidth = "40,180,140,180,140,400";

		$stmt = @mysqli_prepare($db, "select (select sUserID from tAdmin where ID=aID), (select sName from tAdmin where ID=aID), tLog.aDate, tLog.aIP, tLog.aTask from tLog where aDate >=? and aDate <=? order by aDate DESC");
		@mysqli_stmt_bind_param($stmt, 'ii', $sFromTime, $sToTime);
		@mysqli_stmt_execute($stmt);
		@mysqli_stmt_store_result($stmt);
		@mysqli_stmt_bind_result($stmt, $sUserID, $sName, $aDate, $aIP, $aTask);
		$i = 1;
		while (@mysqli_stmt_fetch($stmt)) {
			if ($i % 2 == 0) {
				$OddLine = " class=\"oddline\"";
			} else {
				$OddLine = "";
			}
			?>
			<tr<?=$OddLine?>>
				<td class="center"><?=$i?></td>
				<td class="center"><?=htmlspecialchars($sUserID)?></td>
				<td class="center"><?=htmlspecialchars($sName)?></td>
				<td class="center"><?=date('Y.m.d H:i:s', $aDate)?></td>
				<td class="center"><?=htmlspecialchars($aIP)?></td>
				<td class="center"><?=htmlspecialchars($aTask)?></td>
			</tr>
			<?
			$i++;
		}
		@mysqli_stmt_close($stmt);
		?>
		</table>
		<form name="ffexcel" method="post" action="/include/ose_nexcel.php?mid=<?=$mid?>">
		<input type="hidden" name="ExcelHeader" value="<?=htmlspecialchars($ExcelHeader)?>">
		<input type="hidden" name="ExcelTitle" value="<?=htmlspecialchars($ExcelTitle)?>">
		<input type="hidden" name="ExcelType" value="<?=htmlspecialchars($ExcelType)?>">
		<input type="hidden" name="ExcelWidth" value="<?=htmlspecialchars($ExcelWidth)?>">
		<input type="hidden" name="ExcelQuery" value="<?=encrypt_str($ExcelQuery, $enc_key)?>">
		</form>
		<?
		@addLog ($userno, "개인정보접속로그 조회: " . $sFrom . " ~ " . $sTo, $db);
		break;
	case "A08":
		//개인정보수정
		if ($CanRead != "Y") {
			exit;
		}
		if ($actt == "mok" && $CanWrite == "Y") {
			$sName = getParam("sName");
			$sEmail = getParam("sEmail");
			$sContact = getParam("sContact");
			$sPassword = getParam("sPassword");
			@mysqli_autocommit($db, false);
			try {
				if ($sPassword != "") {
					$stmt = @mysqli_prepare($db, "update tAdmin Set sName=?, sEmail=dbenc(?, ?), sContact=dbenc(?, ?), sPassword=password(?) where ID=?"); 
					@mysqli_stmt_bind_param($stmt, 'ssssssi', $sName, $enc_key, $sEmail, $enc_key, $sContact, $sPassword, $userno);
				} else {
					$stmt = @mysqli_prepare($db, "update tAdmin Set sName=?, sEmail=dbenc(?, ?), sContact=dbenc(?, ?) where ID=?"); 
					@mysqli_stmt_bind_param($stmt, 'sssssi', $sName, $enc_key, $sEmail, $enc_key, $sContact, $userno);
				}
				$result = @mysqli_stmt_execute($stmt);
				if (!$result) {
					throw new Exception(mysqli_error($db));
				}
				@mysqli_stmt_close($stmt);
				@mysqli_commit($db);
				@addLog ($userno, "개인정보수정", $db);
			} catch(Exception $e) {
				$errMsg = $base_page . ": " . $e->getmessage();
				@systemLog($errMsg);
				@mysqli_rollback($db);
				?>
				<script type="text/javascript">
					alert('처리중 오류가 발생했습니다'); 
					history.go(-1);
				</script>
				<?
			}
			@mysqli_autocommit($db, true);
		}
		?>
		<p>※ 비밀번호는 영문, 숫자 및 특수문자 조합으로 9~15자리로 지정해야 합니다.</p>
		<form name="ffdata">
		<table class="ctable" width="100%" style="margin-top:10px;">
		<tr>
			<th>아이디</th>
			<th>이름</th>
			<th>이메일</th>
			<th>연락처</th>
			<th>권한</th>
			<th>신촌</th>
			<th>원주</th>
			<th>국제</th>
			<th>비밀번호</th>
			<th width="80">작업</th>
		</tr>
		<?
		$stmt = @mysqli_prepare($db, "select ID, sUserID, sName, dbdec(?, sContact), dbdec(?, sEmail), sAuth, sGubun from tAdmin where ID=?");
		@mysqli_stmt_bind_param($stmt, 'ssi', $enc_key, $enc_key, $userno);
		@mysqli_stmt_execute($stmt);
		@mysqli_stmt_store_result($stmt);
		@mysqli_stmt_bind_result($stmt, $uid, $sUserID, $sName, $sContact, $sEmail, $sAuth, $sGubun);
		$i = 1;
		while (@mysqli_stmt_fetch($stmt)) {
			if ($i % 2 == 0) {
				$OddLine = " class=\"oddline\"";
			} else {
				$OddLine = "";
			}
			?>
			<tr<?=$OddLine?>>
				<td align="center"><?=htmlspecialchars($sUserID)?></td>
				<td align="center"><input type="text" name="sName_<?=$i?>" id="sName_<?=$i?>" style="width:90%;" maxlength="33" value="<?=htmlspecialchars($sName)?>"><input type="hidden" name="sUserID_<?=$i?>" id="sUserID_<?=$i?>"></td>
				<td align="center"><input type="text" name="sEmail_<?=$i?>" id="sEmail_<?=$i?>" style="width:90%;" maxlength="200" value="<?=htmlspecialchars($sEmail)?>"></td>
				<td align="center"><input type="text" name="sContact_<?=$i?>" id="sContact_<?=$i?>" style="width:90%;" maxlength="200" value="<?=htmlspecialchars($sContact)?>"></td>
				<td align="center"><?=makeInput($db, "N", "select", "ADM", "sAuth", "sAuth", "", "", $sAuth, "", "", "", "")?></td>
				<td align="center"><?=makeInput($db, "N", "checkbox", "", "sGubunA", "sGubunA", "", "", substr($sGubun,0,1), "", "", "", "")?></td>
				<td align="center"><?=makeInput($db, "N", "checkbox", "", "sGubunB", "sGubunB", "", "", substr($sGubun,1,1), "", "", "", "")?></td>
				<td align="center"><?=makeInput($db, "N", "checkbox", "", "sGubunC", "sGubunC", "", "", substr($sGubun,2,1), "", "", "", "")?></td>
				<td align="center"><input type="password" name="sPassword_<?=$i?>" id="sPassword_<?=$i?>" style="width:90%;" maxlength="20"></td>
				<td class="center"><a href="#" OnClick="Javascript: updateCode('<?=$i?>', 'mok'); return false;" class="button icon edit">수정</a></td>
			</tr>
			<?
			$i++;
		}
		@mysqli_stmt_close($stmt);
		?>
		</table>
		</form>
		<form name="ffadmin" method="post" action="<?=$PHP_SELF?>?mid=<?=$mid?>">
		<input type="hidden" name="actt">
		<input type="hidden" name="sUserID">
		<input type="hidden" name="sName">
		<input type="hidden" name="sEmail">
		<input type="hidden" name="sContact">
		<input type="hidden" name="sPassword">
		</form>
		<script type="text/javascript">
			function updateCode(idx, actname) {
				document.ffadmin.sUserID.value=document.getElementById('sUserID_'+idx).value;
				document.ffadmin.sName.value=document.getElementById('sName_'+idx).value;
				document.ffadmin.sEmail.value=document.getElementById('sEmail_'+idx).value;
				document.ffadmin.sContact.value=document.getElementById('sContact_'+idx).value;
				document.ffadmin.sPassword.value=document.getElementById('sPassword_'+idx).value;
				document.ffadmin.actt.value=actname;
				if (validatePassword("sPassword_"+idx, "N")) {
					showLoader();
					document.ffadmin.submit();
				}
			}
		</script>
		<?
		break;
}
?>