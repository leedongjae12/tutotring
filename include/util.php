<?
function file_upload($filename, $maxSize, $boardtype) {
	$upload_base = $_SERVER['DOCUMENT_ROOT'];
	if ($boardtype == "image") {
		$upload_url = $upload_base . "/upload/image";
	} elseif ($boardtype == "file") {
		$upload_url = $upload_base . "/upload/file";
	} else {
		$upload_url = $upload_base . "/upload/document";
	}
	global $msg_upload_fail;
	global $msg_file_allowed;
	global $msg_file_maxsize;

	$dest_str = str_replace("//", "/", $upload_url);

	$file_ext = "ai,psd,hwp,doc,docx,dwg,ppt,pptx,xls,xlsx,jpg,jpeg,gif,png,zip,alz,rar,bmp,avi,wmv,mp3,mdb,mov,mdbx,mpeg,mpg,wav,vsd,txt,pdf";
	$file_allowed = explode(",", $file_ext);
	$file_ext_yn = "n";
	$file_upload_yn = "y";
	$file_str = "";

	if(is_uploaded_file($_FILES[$filename]["tmp_name"])) {
		$myfile_name = stripslashes($_FILES[$filename]["name"]);
		$myfile_size = $_FILES[$filename]["size"];
		$temp_ext = explode(".", $myfile_name);
		$myfile_ext = $temp_ext[sizeof($temp_ext)-1];

		for ($i = 0; $i < sizeof($file_allowed); $i++) {
			if ($file_allowed[$i] == strtolower($myfile_ext)) {
				$file_ext_yn = "y";
			}
		}

		if ($file_ext_yn == "n") {
			$file_upload_yn = "n";
			$file_str = "n";
			?>
			<script type="text/javascript">alert('<?=$myfile_ext?> <?=$msg_file_allowed?>'); history.go(-1);</script>
			<?
		}
		
		if ($myfile_size > $maxSize) {
			$file_upload_yn = "n";
			$file_str = "n";
			?>
			<script type="text/javascript">alert('<?=$msg_file_maxsize?>'); history.go(-1);</script>
			<?
		}

		if ($file_upload_yn == "y") {
			$ntime = explode(" ", microtime());
			$time_prefix = $ntime[0]*100000000;
			for ($tcnt=strlen($time_prefix); $tcnt < 8; $tcnt++) {
				$time_prefix = $time_prefix . "0";
			}
			$dest = $dest_str . "/" . $ntime[1] . $time_prefix . "." . $myfile_ext;
			$dupCheck = false;
			while ($dupCheck == false) {
				if (file_exists($dest)) {
					$ntime = explode(" ", microtime());
					$time_prefix = $ntime[0]*100000000;
					for ($tcnt=strlen($time_prefix); $tcnt < 8; $tcnt++) {
						$time_prefix = $time_prefix . "0";
					}
					$dest = $dest_str . "/" . $ntime[1] . $time_prefix . "." . $myfile_ext;
				} else {
					$dupCheck = true;
				}
			}
			if(!move_uploaded_file($_FILES[$filename]["tmp_name"], $dest)) {
				$file_str = "n";
				?>
				<script type="text/javascript">alert('<?=$msg_upload_fail?>'); history.go(-1);</script>
				<?
			} else {
				$file_str = str_replace($upload_base, "", $dest) . "|" . $myfile_name . "|" . $myfile_size;
			}
		}
	}
	return $file_str;
}

function validMenu($mid, $page, $db) {
	if ($page != "") {
		$cstmt = @mysqli_prepare($db, "select mAuth from tMenu where mPage=? and menuID=? and mShow='Y'");
		@mysqli_stmt_bind_param($cstmt, 'ss', $page, $mid);
	} else {
		$cstmt = mysqli_prepare($db, "select mAuth from tMenu where menuID=? and mShow='Y'");
		mysqli_stmt_bind_param($cstmt, 's', $mid);
	}
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $mAuth);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	return $mAuth;
}

function TinyMCEInit ($userlevel, $editor_width, $editor_css, $mid, $cmid, $lang) {
	if ($lang == "k") {
		$mceLang = "ko";
		$mceFont = "굴림체=굴림체;굴림=굴림;돋움체=돋움체;궁서체=궁서체;Tahoma=tahoma,arial,helvetica,sans-serif;Verdana=verdana,geneva;Times New Roman=times new roman,times;Arial=arial,helvetica,sans-serif";
	} else {
		$mceLang = "en";
		$mceFont = "Tahoma=tahoma,arial,helvetica,sans-serif;Verdana=verdana,geneva;Times New Roman=times new roman,times;Arial=arial,helvetica,sans-serif";
	}
	if ($userlevel >= "3") {
		?>
		<script type="text/javascript" src="/script/editor/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
				mode : "exact",
				elements : "tmtext",
				theme : "advanced",
				language : "<?=$mceLang?>",
				width : "<?=$editor_width?>", 
				height : "400px",
				plugins : "safari,pagebreak,style,layer,table,save,advhr,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advimage",
				file_browser_callback : "fileBrowserCallBack",
				theme_advanced_buttons1 :
				"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect",
				theme_advanced_buttons2 : 
				"cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,|,forecolor,backcolor",
				theme_advanced_buttons3 :
				"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,|,code",
				theme_advanced_fonts : "<?=$mceFont?>",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_resizing : false,
				relative_urls : false,
				content_css : ["/css/default.css", "<?=$editor_css?>"]
			});

			function fileBrowserCallBack(field_name, url, type, win) {
				var connector = "/browser.php";
				var enableAutoTypeSelection = true;
				
				var cType;
				tinyfck_field = field_name;
				tinyfck = win;
				
				switch (type) {
					case "image":
						cType = "image";
						break;
					case "file":
						cType = "file";
						break;
				}
				
				if (enableAutoTypeSelection && cType) {
					connector += "?mid=<?=$mid?>&cmid=<?=$cmid?>&actt=" + cType;
				}
				
				window.open(connector, "tinyfck", "modal,width=500,height=600,scrollbars=yes,resizable=yes");
			}
		</script>
		<?
	} else {
		?>
		<script type="text/javascript" src="/script/editor/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
			mode : "exact",
			elements : "tmtext",
			theme : "advanced",
			language : "<?=$mceLang?>",
			width : "<?=$editor_width?>", 
			height : "400px", 
			plugins : "safari,pagebreak,style,layer,table,save,advhr,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
			theme_advanced_buttons1 :
			"formatselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,forecolor,backcolor",
			theme_advanced_buttons2 :
			"",
			theme_advanced_buttons3 :
			"",
			theme_advanced_fonts : "<?=$mceFont?>",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_resizing : false,
			content_css : ["/css/default.css", "<?=$editor_css?>"]
			});
		</script>
		<?
	}
}

function isdate($date) {
	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);
	return checkdate($month, $day, $year);
}

function getmktime($date, $type) {
	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);

	if ($type == "s") {
		$hour = "0";
		$min = "0";
		$sec = "0";
	} elseif ($type == "d") {
		$hour = substr($date, 11, 2);
		$min = substr($date, 14, 2);
		$sec = substr($date, 17, 2);
	} else {
		$hour = "23";
		$min = "59";
		$sec = "59";
	}
	if (isdate($date)) {
		return mktime($hour, $min, $sec, $month, $day, $year);
	} else {
		return 0;
	}
}

function getDateFormat($bDate, $lang) {
	if ($lang == "k") {
		$bDate = date('Y-m-d', $bDate);
	} else {
		$bDate = date('M j, Y', $bDate);
	}
	return $bDate;
}

function getPassword($db, $mPass) {
	$cstmt = @mysqli_prepare($db, "select password(?)");
	@mysqli_stmt_bind_param($cstmt, 's', $mPass);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $mPassword);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	return $mPassword;
}

function getBBSFirst($total_record, $num_per_page, $page) {
	$total_page = ceil($total_record/$num_per_page);

	if ($total_record == 0) {
		$first=1;
	} else {
		$first = $num_per_page*($page-1);
	}
	return $first;
}

function getBBSLast($total_record, $num_per_page, $page) {
	$total_page = ceil($total_record/$num_per_page);

	if ($total_record == 0) {
		$last=0;
	} else {
		$last = $num_per_page*$page; 
	}
	return $last;
}

function getBBSPage($total_record, $total_page, $page_per_block, $page, $last, $query_string, $lang) {
	global $msg_board_prev;
	global $msg_board_prev;
	global $msg_board_next;
	global $msg_board_pprev;
	global $msg_board_nnext;
	global $msg_board_page;

	$total_block = ceil($total_page/$page_per_block);
	$block = ceil($page/$page_per_block);
	$first_page = ($block-1)*$page_per_block;
	$last_page = $block*$page_per_block;
	if ($total_block <= $block) {
		$last_page = $total_page;
	}
	$icon_prev = "";
	$icon_next = "";
	$icon_pprev = "";
	$icon_nnext = "";

	if ($page > 1) {
		$page_num = $page - 1;
	} else {
		$page_num = 1;
	}
	$icon_prev = " <li><a href=\"" . $query_string . "&page=" . $page_num . "\" title=\"" . $msg_board_prev . "\"><i class=\"fa fa-angle-left\" aria-hidden=\"true\"></i></a></li>";
			 
	//next icon 
	if ($total_record > $last) {
		$page_num = $page + 1;   
	} else {
		$page_num = $total_page;
	}
	$icon_next = " <li><a href=\"" . $query_string . "&page=" . $page_num . "\" title=\"" . $msg_board_next . "\"><i class=\"fa fa-angle-right\" aria-hidden=\"true\"></i></a></li>";


	//if($block > 1) {
		$page_num = 1;
		$icon_pprev = "<li><a href=\"" . $query_string . "&page=" . $page_num . "\" title=\"" . $msg_board_pprev . "\"><i class=\"fa fa-angle-double-left\" aria-hidden=\"true\"></i></a></li>";
	//}

	//if($block < $total_block) {
		$page_num = $total_page;
		$icon_nnext = "<li><a href=\"" . $query_string . "&page=" . $page_num . "\" title=\"" . $msg_board_nnext . "\"><i class=\"fa fa-angle-double-right\" aria-hidden=\"true\"></i></a></li>";
	//}

	//page navigator
	$icon_navi = "";
	for($direct_page = $first_page+1; $direct_page <= $last_page; $direct_page++) {
		if($page == $direct_page) {
			$icon_navi = $icon_navi . "<li><a href=\"" . $query_string . "&page=" . $direct_page . "\" title=\"" . $direct_page . $msg_board_page . "\" class=\"active\" style=\"font-weight:bold;\">" . $direct_page . "</a></li>";
		} else {
			$icon_navi = $icon_navi . "<li><a href=\"" . $query_string . "&page=" . $direct_page . "\" title=\"" . $direct_page . $msg_board_page . "\">" . $direct_page . "</a></li>";
		}
	}

	return "<div style=\"clear:both; text-align:center; margin-top:16px;\"><ul class=\"bbspagination\">" . $icon_pprev . " " . $icon_prev . $icon_navi . $icon_next . " " . $icon_nnext . "</ul></div>";
}

function random_string($length) { 
    $randomcode = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'X', 'J', 'K', 'B', 'M', 'N', 'Y', 'P', 'R', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'); 
    
	mt_srand((double)microtime()*1000000); 
    for($i=1;$i<=$length;$i++) {
		$Rstring .= $randomcode[mt_rand(1, count($randomcode))]; 
	}
    return $Rstring; 
} 

function random_newstring($length) { 
    $randomcode = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'X', 'J', 'K', 'B', 'M', 'N', 'Y', 'P', 'R', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'. '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '+', '-', '=', '{', '[', '}', ']', '<', '>', '?'); 

	mt_srand((double)microtime()*1000000); 
    for($i=1;$i<=$length;$i++) {
		$Rstring .= $randomcode[mt_rand(1, count($randomcode))]; 
	}
	return $Rstring;
}

function getNewPassword() {
	$npw = random_newstring(9);
	$chk = "N";
	while ($chk == "N") {
		$chk = chkPassword($npw);
		if ($chk == "N") {
			$npw = random_newstring(9);
		}
	}
	return $npw;
}

function random_number($length) { 
    $randomcode = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0'); 
    
	mt_srand((double)microtime()*1000000); 
    for($i=1;$i<=$length;$i++) {
		$Rstring .= $randomcode[mt_rand(1, count($randomcode))]; 
	}
    return $Rstring; 
} 

function fnUnicodeToUTF8($string) {
	return html_entity_decode(preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($string)), null, 'UTF-8');
}

function utf8_substr($str, $len) {
    $str_split = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    $str_slice = array_slice($str_split, 0, $len);
    $str = join("", $str_slice);

    return $str;
}

function getImg($content){ 
	$content=str_replace("IMG","img",$content); 
	if (0 == preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $content, $match)) {
		return "";
	} else {
		return $match[1];
	}
} 

function chkPassword($pwd) {
	if (0 == preg_match("/^(?=.*?[A-Za-z])(?=.*?[0-9])(?=.*?[`~\!@#\$%\^\&\*\(\)_\+\-=\{\}\[\]\|\\\:\";'\<>\?,\.\/]).{9,15}$/", $pwd, $match)) {
	    return "N"; 
	} else {
		return "Y";
	}
}

function getFileExt($str) {
	$temp_ext = explode(".", $str);
	$myfile_ext = $temp_ext[sizeof($temp_ext)-1];								
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/images/ext_" . strtolower($myfile_ext) . ".gif")) {
		$img_ext = "/images/ext_" . strtolower($myfile_ext) . ".gif";
	} else {
		$img_ext = "/images/ext_default.gif";
	}
	return $img_ext;
}

function FormatDate($str) {
	if (strlen($str) == 8) {
		$result = substr($str,0,4) . "-" . substr($str,4,2) . "-" . substr($str, 6,2);
	} elseif (strlen($str) == 6) {
		$result = substr($str,0,4) . "-" . substr($str,4,2);
	} elseif (strlen($str) == 4) {
		$result = substr($str,0,4);
	} else {
		$result = "";
	}
	return $result;
}

function sendsms($sender, $receiver, $msg) {
	global $deBug;
	if ($deBug == "N") {
		$sender = str_replace("-", "", $sender);
		if ($sender == "") {
			$sender = "15991885";
		}
		$receiver = str_replace("-", "", $receiver);

		$msg = urlencode(iconv("utf-8", "euc-kr", $msg));
		$msg = str_replace(chr(13) . chr(10), "%0A", $msg);
		$msg = str_replace(chr(13), "%0A", $msg);
		$msg = str_replace(chr(10), "%0A", $msg);
		
		$smsURL = "http://message.yonsei.ac.kr/sms/SendMsgSystem.jsp?usercode=sms0022&localcode=sms0022&msg=" . $msg . "&callback=" . $sender . "&destination=" . $receiver . "&msgtitle=" . urlencode(iconv("utf-8", "euc-kr", "튜터링신청시스템"));
		ob_start();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $smsURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_exec($ch);
		curl_close($ch);
		ob_end_clean();
	} else {
		echo $receiver . " " . $msg;
	}
}

function makeInput($db, $pEdit, $pType, $pGroup, $pID, $pName, $pStyle, $pScript, $pValue, $pShow, $pRef, $pAll, $pAjax) {
	global $mid;
	global $lang;
	global $oValue;

	if ($pType == "text") {
		if ($pEdit == "Y") {
			$strResult = "<input type=\"text\" name=\"" . $pName . "\" id=\"" . $pID . "\"" . $pStyle . " value=\"" . htmlspecialchars($pValue) . "\"" . $pScript . ">";
		} else {
			$strResult = htmlspecialchars($pValue);
		}
	} elseif ($pType == "password") {
		if ($pEdit == "Y") {
			$strResult = "<input type=\"password\" name=\"" . $pName . "\" id=\"" . $pID . "\"" . $pStyle . " " . $pScript . ">";
		} else {
			$strResult = "";
		}
	} elseif ($pType == "date") {
		if ($pEdit == "Y") {
			$uTime = FormatDate($pValue);
			$jDate = substr($uTime,0,4) . ", " . ((int)substr($uTime,5,2) - 1) . ", " . (int)substr($uTime,8,2);
			if ($pValue == "") {
				$strResult = "<input type=\"text\" name=\"" . $pName . "\" id=\"" . $pID . "\"" . $pStyle . " value=\"" . FormatDate($pValue) . "\"" . $pScript . " class=\"datepicker-here\">";
			} else {
				if ($pRef != "") {
					$jMaxDate = substr($pRef,0,4) . ", " . ((int)substr($pRef,5,2) - 1) . ", " . (int)substr($pRef,8,2);
					$strResult = "<input type=\"text\" name=\"" . $pName . "\" id=\"" . $pID . "\"" . $pStyle . " value=\"" . FormatDate($pValue) . "\"" . $pScript . " class=\"datepicker-here\"><script type=\"text/javascript\">$('#" . $pID . "').datepicker({ minDate: new Date(), maxDate: new Date(" . $jMaxDate . ") }); $('#" . $pID . "').data('datepicker').selectDate(new Date(" . $jDate . "));</script>";
				} else {
					$strResult = "<input type=\"text\" name=\"" . $pName . "\" id=\"" . $pID . "\"" . $pStyle . " value=\"" . FormatDate($pValue) . "\"" . $pScript . " class=\"datepicker-here\"><script type=\"text/javascript\">$('#" . $pID . "').datepicker({  }); $('#" . $pID . "').data('datepicker').selectDate(new Date(" . $jDate . "));</script>";
				}
			}
		} else {
			$strResult = FormatDate($pValue);
		}
	} elseif ($pType == "number") {
		if ($pValue !== "") {
			if (is_numeric($pValue)) {
				$pValue = number_format($pValue,0);
			}
		} else {
			$pValue = "";
		}
		if ($pEdit == "Y") {
			$strResult = "<input type=\"text\" name=\"" . $pName . "\" id=\"" . $pID . "\"" . $pStyle . " value=\"" . $pValue . "\"" . $pScript . " OnKeyPress=\"Javascript: return onlyNum(this);\" OnKeyUp=\"Javascript: this.value=number_format(this.value);\">";
		} else {
			$strResult = $pValue;
		}
	} elseif ($pType == "float") {
		if ($pValue != "") {
			$pValue = number_format($pValue,2);
		} else {
			$pValue = "";
		}
		if ($pEdit == "Y") {
			$strResult = "<input type=\"text\" name=\"" . $pName . "\" id=\"" . $pID . "\"" . $pStyle . " value=\"" . $pValue . "\"" . $pScript . " OnKeyPress=\"Javascript: return onlyFloat(this);\" OnKeyUp=\"Javascript: this.value=number_format(this.value);\">";
		} else {
			$strResult = $pValue;
		}
	} elseif ($pType == "textarea") {
		if ($pEdit == "Y") {
			$strResult = "<textarea name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . ">" . htmlspecialchars($pValue) . "</textarea>";
		} else {
			$strResult = nl2br(htmlspecialchars($pValue));
		}
	} elseif ($pType == "textareatiny") {
		if ($pEdit == "Y") {
			$strResult = "<textarea name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . ">" . $pValue . "</textarea>";
		} else {
			$strResult = $pValue;
		}
	} elseif ($pType == "file") {
		if ($pEdit == "Y") {
			if ($pValue != "") {
				$strResult = $pValue . "<br><input type=\"file\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . ">";
			} else {
				$strResult = "<input type=\"file\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . ">";
			}
		} else {
			$strResult = $pValue;
		}
	} elseif ($pType == "checkbox") {
		if ($pEdit == "Y") {
			if ($pValue == "Y") {
				$strResult = "<input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"Y\" checked>";
			} else {
				$strResult = "<input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"Y\">";
			}
		} else {
			if ($pValue == "Y") {
				$strResult = "<input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"Y\" checked disabled>";
			} else {
				$strResult = "<input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"Y\" disabled>";
			}
		}
	} elseif ($pType == "hidden") {
		$strResult = "<input type=\"hidden\" name=\"" . $pName . "\" id=\"" . $pID . "\" value=\"" . $pValue . "\">";
	} elseif ($pType == "select" || $pType == "radiolist" || $pType == "checkboxlist") {
		if ($pEdit == "Y") {
			if ($pAjax != "Y" && $pType == "select") {
				$strResult = "<select name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . ">";
			} else {
				$strResult = "";
			}
			if ($pAll != "") {
				if ($pType == "select") {
					$strResult = $strResult . "<option value=\"\">" . htmlspecialchars($pAll) . "</option>";
				} else {
					if ($pValue == "") {
						$strResult = $strResult . "<input type=\"radio\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"\" checked>" . htmlspecialchars($pAll);
					} else {
						$strResult = $strResult . "<input type=\"radio\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"\">" . htmlspecialchars($pAll);
					}
				}
			}
			$pWhere = "";
			if ($pRef != "") {
				$pWhere = $pWhere . " and cCodeRef='" . str_replace("'","''", $pRef) . "'";
			}
			if ($pShow == "Y") {
				$pWhere = $pWhere . " and cShow='Y'";
			}
			if ($pGroup == "USERCAMPUS") {
				global $usersosok;
				$pWhere2 = "";
				if (substr($usersosok,0,1) == "Y") {
					if ($pWhere2 == "") {
						$pWhere2 = " cCode='A'";
					} else {
						$pWhere2 = $pWhere2 . " or cCode='A'";
					}
				}
				if (substr($usersosok,1,1) == "Y") {
					if ($pWhere2 == "") {
						$pWhere2 = " cCode='B'";
					} else {
						$pWhere2 = $pWhere2 . " or cCode='B'";
					}
				}
				if (substr($usersosok,2,1) == "Y") {
					if ($pWhere2 == "") {
						$pWhere2 = " cCode='C'";
					} else {
						$pWhere2 = $pWhere2 . " or cCode='C'";
					}
				}
				if ($pWhere2 != "") {
					$pWhere = $pWhere . " and (" . $pWhere2 . ")";
				}
				$pGroup = "CAMPUS";
			}
			if ($pGroup == "USERPROGRAM") {
				if ($lang == "k") {
					$cstmt = @mysqli_prepare($db, "select tProgram.ID, cCodeName from tProgram inner join tCode on cCodeGroup='PROGRAM' and cCode=pCode where concat(pCampus,pSemester)='" . $pRef . "' order by cOrder");
				} else {
					$cstmt = @mysqli_prepare($db, "select tProgram.ID, cCodeNameE from tProgram inner join tCode on cCodeGroup='PROGRAM' and cCode=pCode where concat(pCampus,pSemester)='" . $pRef . "' order by cOrder");
				}
			} elseif ($pGroup == "OGUBUN") {
				if ($pRef == "YN") {
					if ($lang == "k") {
						$cstmt = @mysqli_prepare($db, "select cCode, cCodeName from tCode where cCodeGroup=? and cCode='A' order by cOrder");
					} else {
						$cstmt = @mysqli_prepare($db, "select cCode, cCodeNameE as cCodeName from tCode where cCodeGroup=? and cCode='A' order by cOrder");
					}
				} elseif ($pRef == "NY") {
					if ($lang == "k") {
						$cstmt = @mysqli_prepare($db, "select cCode, cCodeName from tCode where cCodeGroup=? and cCode='B' order by cOrder");
					} else {
						$cstmt = @mysqli_prepare($db, "select cCode, cCodeNameE as cCodeName from tCode where cCodeGroup=? and cCode='B' order by cOrder");
					}
				} else {
					if ($lang == "k") {
						$cstmt = @mysqli_prepare($db, "select cCode, cCodeName from tCode where cCodeGroup=? order by cOrder");
					} else {
						$cstmt = @mysqli_prepare($db, "select cCode, cCodeNameE as cCodeName from tCode where cCodeGroup=? order by cOrder");
					}
				}
			} else {
				if ($lang == "k") {
					$cstmt = @mysqli_prepare($db, "select cCode, cCodeName from tCode where cCodeGroup=? " . $pWhere . " order by cOrder");
				} else {
					$cstmt = @mysqli_prepare($db, "select cCode, cCodeNameE as cCodeName from tCode where cCodeGroup=? " . $pWhere . " order by cOrder");
				}
			}
			@mysqli_stmt_bind_param($cstmt, 's', $pGroup);
			@mysqli_stmt_execute($cstmt);
			@mysqli_stmt_store_result($cstmt);
			@mysqli_stmt_bind_result($cstmt, $cCode, $cCodeName);
			while (@mysqli_stmt_fetch($cstmt)) {
				if ($pValue == "" && $pAll == "" && trim($pStyle) != "multiple") {
					$oValue = $cCode;
					$pValue = $cCode;
				}
				if ($pType == "select") {
					if (trim($pStyle) == "multiple") {
						if (strpos(str_replace("'","", $pValue) . ",", $cCode . ",") !== false) {
							$strResult = $strResult . "<option value=\"" . htmlspecialchars($cCode) . "\" selected>" . htmlspecialchars($cCodeName) . "</option>";
						} else {
							$strResult = $strResult . "<option value=\"" . htmlspecialchars($cCode) . "\">" . htmlspecialchars($cCodeName) . "</option>";
						}
					} else {
						if ($pValue == $cCode) {
							$strResult = $strResult . "<option value=\"" . htmlspecialchars($cCode) . "\" selected>" . htmlspecialchars($cCodeName) . "</option>";
						} else {
							$strResult = $strResult . "<option value=\"" . htmlspecialchars($cCode) . "\">" . htmlspecialchars($cCodeName) . "</option>";
						}
					}
				} elseif ($pType == "radiolist") {
					if ($pValue == $cCode) {
						$strResult = $strResult . " <input type=\"radio\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"" . htmlspecialchars($cCode) . "\" checked><span id=\"" . $pID . $cCode . "\">" . htmlspecialchars($cCodeName) . "</span>";
					} else {
						$strResult = $strResult . " <input type=\"radio\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"" . htmlspecialchars($cCode) . "\"><span id=\"" . $pID . $cCode . "\">" . htmlspecialchars($cCodeName) . "</span>";
					}
				} elseif ($pType == "checkboxlist") {
					if (strpos($pValue, $cCode . ",") === false) {
						if ($strResult == "") {
							$strResult = $strResult . "<input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"" . htmlspecialchars($cCode) . "\">" . htmlspecialchars($cCodeName);
						} else {
							$strResult = $strResult . "<br><input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"" . htmlspecialchars($cCode) . "\">" . htmlspecialchars($cCodeName);
						}
					} else {
						if ($strResult == "") {
							$strResult = $strResult . "<input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"" . htmlspecialchars($cCode) . "\" checked>" . htmlspecialchars($cCodeName);
						} else {
							$strResult = $strResult . "<br><input type=\"checkbox\" name=\"" . $pName . "\" id=\"" . $pID . "\" " . $pStyle . " " . $pScript . " value=\"" . htmlspecialchars($cCode) . "\" checked>" . htmlspecialchars($cCodeName);
						}
					}
				}
			}
			@mysqli_stmt_close($cstmt);
			if ($pAjax != "Y" && $pType == "select") {
				$strResult = trim($strResult) . "</select>";
			}
		} else {
			$strResult = getCodeName($db, $pGroup, $pValue, $pRef);
		}
	}
	return $strResult;
}

function getCodeName($db, $cGroup, $cCode, $cRef) {
	global $lang;
	$ret = "";
	if ($lang == "k") {
		$cstmt = @mysqli_prepare($db, "select cCodeName from tCode where cCodeGroup=? and cCode=? and cCodeRef=?");
	} else {
		$cstmt = @mysqli_prepare($db, "select cCodeNameE from tCode where cCodeGroup=? and cCode=? and cCodeRef=?");
	}
	@mysqli_stmt_bind_param($cstmt, 'sss', $cGroup, $cCode, $cRef);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $cName);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	$ret = $cName;
	return $ret;
}

function getNull($str) {
	if (is_null($str)) {
		$str = "";
	}
	return $str;
}

function makeSemester($StrID, $StrName, $cVal) {
	global $db;
	$SemName1 = getCodeName($db, "SEMESTER", "A", "");
	$SemName2 = getCodeName($db, "SEMESTER", "B", "");
	$SemName3 = getCodeName($db, "SEMESTER", "C", "");
	$SemName4 = getCodeName($db, "SEMESTER", "D", "");
	$SemValue1 = "A";
	$SemValue2 = "B";
	$SemValue3 = "C";
	$SemValue4 = "D";

	echo "<select name=\"" . $StrName . "\" id=\"" . $StrID . "\">";
	for ($i=date('Y',time())+1; $i >= 2008; $i--) {
		for ($j=1; $j <=4 ; $j++) {
			$tempSem = $i . ${"SemValue".$j};
			if ($tempSem == $cVal) {
				echo "<option value=\"" . $i . ${"SemValue".$j} . "\" selected>" . $i . "년 " . ${"SemName".$j} . "</option>";
			} else {
				echo "<option value=\"" . $i . ${"SemValue".$j} . "\">" . $i . "년 " . ${"SemName".$j} . "</option>";
			}
		}
	}
	echo "</select>";
}

function stmt_bind_assoc (&$stmt, &$out) {
    $data = mysqli_stmt_result_metadata($stmt);
    $fields = array();
    $out = array();

    $fields[0] = $stmt;
    $count = 1;

    while($field = mysqli_fetch_field($data)) {
        $fields[$count] = &$out[$field->name];
        $count++;
    }    
    call_user_func_array(mysqli_stmt_bind_result, $fields);
}

function sendmail ($from, $fromname, $to, $cc, $bcc, $title, $mailbody, $attach) {
	global $deBug;

	$mailbody = str_replace("<p>", "<p style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<div>", "<div style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<a>", "<a style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<b>", "<b style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<th>", "<th style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<span>", "<span style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<td>", "<td style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<li>", "<li style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("<ol>", "<ol style=\"font-size:14px;\">", $mailbody);
	$mailbody = str_replace("url('/images/", "url('" . $default_url . "images/", $mailbody);
	$mailbody = str_replace("url('./images/", "url('" . $default_url . "images/", $mailbody);
	$mailbody = str_replace("url('images/", "url('" . $default_url . "images/", $mailbody);
	$mailbody = str_replace("src=\"/images/", "src=\"" . $default_url . "images/", $mailbody);
	$mailbody = str_replace("src=\"./images/", "src=\"" . $default_url . "images/", $mailbody);
	$mailbody = str_replace("src=\"images/", "src=\"" . $default_url . "images/", $mailbody);
	$mailbody = str_replace("href=\"/", "href=\"" . $default_url . "", $mailbody);
	$mailbody = str_replace("href=\"./", "href=\"" . $default_url . "", $mailbody);
	$mailbody = str_replace("src=\"/upload/", "src=\"" . $default_url . "upload/", $mailbody);
	$mailbody = str_replace("src=\"./upload/", "src=\"" . $default_url . "upload/", $mailbody);
	$mailbody = str_replace("src=\"upload/", "src=\"" . $default_url . "upload/", $mailbody);

	if ($deBug == "Y") {
		echo $mailbody;
	} else {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/script/class.phpmailer.php");
		if ($from == "") {
			$from = "ose_center@yonsei.ac.kr";
		}
		if ($fromname == "") {
			$fromname = "OSE센터";
		}
		$reply = $from;
		$replyname = $fromname;

		$mail = new PHPMailer();
		$mail->SetLanguage("en", $_SERVER['DOCUMENT_ROOT'] . "/script/");
		$mail->From = $from;
		$mail->FromName = $fromname;
		$mail->IsSMTP();            // set mailer to use SMTP or IsSendmail()
		$mail->Host = "smtp-relay.gmail.com";  // specify main and backup server
		$mail->SMTPAuth = false;     //$mail->Username / $mail->Password

		if ($to != "") {
			$nto = explode(";", $to);
			for ($i=0; $i < sizeof($nto); $i++) {
				if ($nto[$i] != "") {
					$mail->AddAddress($nto[$i]);
				}
			}

			if ($cc != "") {
				$ncc = explode(";", $cc);
				for ($i=0; $i < sizeof($ncc); $i++) {
					if ($ncc[$i] != "") {
						$mail->AddCC($ncc[$i]);
					}
				}
			}
			if ($bcc != "") {
				$nbcc = explode(";", $bcc);
				for ($i=0; $i < sizeof($nbcc); $i++) {
					if ($nbcc[$i] != "") {
						$mail->AddBCC($nbcc[$i]);
					}
				}
			}

			if ($replyname == "") {
				$mail->AddReplyTo($reply);
			} else {
				$mail->AddReplyTo($reply, $replyname);
			}

			if ($attach != "") {
				$attachlist = explode("|", $attach);
				for ($i = 0; $i < sizeof($attachlist); $i++) {
					$mail->AddAttachment($attachlist[$i]);
				}
			}
			$mail->IsHTML(true);
			$mail->Subject = $title;
			$mail->Body    = $mailbody;
			$mail->Send();
		}
	}
}

function makeLength($str, $len) {
	$ret = $str;
	for ($i=1; $i <= $len - strlen($str); $i++) {
		$ret = "0" . $ret;
	}
	return $ret;
}

function addLog($userno, $task, $db) {
	$bIP = $_SERVER['REMOTE_ADDR'];
	$cstmt = @mysqli_prepare($db, "insert into tLog (aID, mID, aDate, aTask, aIP) values (?, 0, UNIX_TIMESTAMP(), ?, ?)");
	@mysqli_stmt_bind_param($cstmt, 'iss', $userno, $task, $bIP);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_close($cstmt);
}

function getLang($db, $oCampus) {
	$ret = "";
	if ($oCampus == "") {
		$ret = "k";
	} else {
		$cstmt = @mysqli_prepare($db, "select cCodeDesc from tCode where cCodeGroup='SYSTEM' and cCode='LANG" . $oCampus . "'");
		@mysqli_stmt_execute($cstmt);
		@mysqli_stmt_store_result($cstmt);
		@mysqli_stmt_bind_result($cstmt, $cCodeDesc);
		@mysqli_stmt_fetch($cstmt);
		@mysqli_stmt_close($cstmt);
		@mysqli_stmt_close($cstmt);
		$ret = $cCodeDesc;
	}
	return $ret;
}

function getUserID($db, $uid) {
	$cstmt = mysqli_prepare($db, "select sUserID from tAdmin where ID=?");
	@mysqli_stmt_bind_param($cstmt, 'i', $uid);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $sUserID);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	$ret = $sUserID;
	return $ret;
}

function getContents($db, $cVal) {
	$cstmt = mysqli_prepare($db, "select cText from tContents where menuID=?");
	@mysqli_stmt_bind_param($cstmt, 's', $cVal);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $cText);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	$ret = $cText;
	return $ret;
}

function getSem($db, $oCampus) {
	$ret = "";
	$cstmt = @mysqli_prepare($db, "select cCodeDesc from tCode where cCodeGroup='SYSTEM' and cCode='CONFIG" . $oCampus . "'");
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $cCodeDesc);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	$ret = $cCodeDesc;
	return $ret;
}

function getDay($db, $oCampus) {
	$ret = "";
	$cstmt = @mysqli_prepare($db, "select cCodeDesc from tCode where cCodeGroup='SYSTEM' and cCode='SAVEDAY" . $oCampus . "'");
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $cCodeDesc);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	$ret = getNumber($cCodeDesc);
	return $ret;
}

function getSMS($db, $oCampus) {
	$ret = "";
	$cstmt = @mysqli_prepare($db, "select cCodeDesc from tCode where cCodeGroup='SYSTEM' and cCode='SMS" . $oCampus . "'");
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $cCodeDesc);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	$ret = $cCodeDesc;
	return $ret;
}

function getEmail($db, $oCampus) {
	$ret = "";
	$cstmt = @mysqli_prepare($db, "select cCodeDesc from tCode where cCodeGroup='SYSTEM' and cCode='EMAIL" . $oCampus . "'");
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $cCodeDesc);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);
	$ret = $cCodeDesc;
	return $ret;
}


function getPeriod($db, $pID) {
	global $lang;
	$ret = "";
	$cstmt = @mysqli_prepare($db, "select pFrom, pTo from tProgram where ID=?");
	@mysqli_stmt_bind_param($cstmt, 'i', $pID);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $pFrom, $pTo);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);

	$ret = getDateFormat(strtotime(FormatDate($pFrom)), $lang) . " ~ " . getDateFormat(strtotime(FormatDate($pTo)), $lang);
	return $ret;
}

function getPeriodCheck($db, $pID) {
	$ret = "N";
	$cstmt = @mysqli_prepare($db, "select pFrom, pTo from tProgram where ID=?");
	@mysqli_stmt_bind_param($cstmt, 'i', $pID);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $pFrom, $pTo);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);

	if (date('Ymd', time()) >= $pFrom && date('Ymd', time()) <= $pTo) {
		$ret = "Y";
	}
	return $ret;
}

function getTutoringInfo($db, $tID) {
	$ret = "";
	$cstmt = @mysqli_prepare($db, "select tTitle, tProfessor from tTutoring where ID=?");
	@mysqli_stmt_bind_param($cstmt, 'i', $tID);
	@mysqli_stmt_execute($cstmt);
	@mysqli_stmt_store_result($cstmt);
	@mysqli_stmt_bind_result($cstmt, $tTitle, $tProfessor);
	@mysqli_stmt_fetch($cstmt);
	@mysqli_stmt_close($cstmt);

	if ($tProfessor != "") {
		$ret = $tTitle . " (" . $tProfessor . ")";
	} else {
		$ret = $tTitle;
	}
	return $ret;
}

function getSemesterName($db, $cVal) {
	global $lang;
	if ($lang == "k") {
		$ret = substr($cVal,0,4) . "년 " .  getCodeName($db, "SEMESTER", substr($cVal,4,1), "");
	} else {
		$ret = getCodeName($db, "SEMESTER", substr($cVal,4,1), "") . ", " . substr($cVal,0,4);
	}
	return $ret;
}

?>