<?
$temp_str = "";
$userno = "";
$userid = "";
$username = "";
$usersosok = "";
$userlevel = "";


$olang = getLang($db, $oCampus);

$tempself = $_SERVER['PHP_SELF'];
$temppage = explode(".", $tempself);
$PHP_SELF = $temppage[0] . ".php";
$base_page = basename($PHP_SELF);

$mid = getParam("mid");
$lang = getParam("lang");

if ($lang != "e" && $lang != "k") {
	$lang = $olang;
}

if ($mid == "") {
	if ($base_page == "oseadmin.php") {
		$mid = "A00";
	} else {
		$mid = "m01";
	}
}

if ($base_page == "oseadmin.php" || substr($mid,0,1) == "A") {
	if (isset($_SESSION["aLevel"])) {
		$temp_str = $_SESSION["aLevel"];
	} else {
		$temp_str = "";
	}
} else {
	if (isset($_SESSION["sLevel"])) {
		$temp_str = $_SESSION["sLevel"];
	} else {
		$temp_str = "";
	}
}

if ($temp_str != "") {
	$temp_str = decrypt_str($temp_str, $enc_key);
	$session_str = explode("|@|", $temp_str);
	$userno = $session_str[0];
	$userid = $session_str[1];
	$username = $session_str[2];
	$usersosok = $session_str[3];
	$userlevel = $session_str[4];
}

if (!$userlevel) {
	$userlevel = "0";
}

$default_url = "http://" . $_SERVER["HTTP_HOST"] . "/";
$ssl_url = "https://" . $_SERVER["HTTP_HOST"] . "/";

$editor_css = "/css/default.css";
$html_css = "/css/default.css";

if ($userlevel >= "7") {
	$max_upload_size = 102400000;
	$requirement_file = "(Max 100MB)";
	$max_upload_size_string = "100MB";
	$max_uploadNo = 5;
} else {
	$max_upload_size = 20480000;
	$requirement_file = "(Max 20MB)";
	$max_upload_size_string = "20MB";
	$max_uploadNo = 2;
}

global $deBug;

$deBug = "N";

$page_per_block = 10;

$ssl_check = "N";

if (isset($_SERVER['HTTPS'])) {
	if ('on' == strtolower($_SERVER['HTTPS'])) {
		$ssl_check = "Y";
	}
	if ('1' == $_SERVER['HTTPS']) {
		$ssl_check = "Y";
	} elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'])) {
		$ssl_check = "Y";
    }
}

if ($deBug == "N" && $ssl_check == "N" && $base_page != "downloadfile.php") {
	if ($oCampus == "B") {
		$url = $ssl_url . "wonju";
	} elseif ($oCampus == "C") {
		$url = $ssl_url . "international";
	} else {
		$url = $ssl_url . "sinchon";
	}
	header("location:$url");
}
?>