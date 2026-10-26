<?php
error_reporting(E_ALL);
$enc_key ='os#^&*!etutr!@%#';

function encrypt_str($text, $enc_key) {
	return trim(base64_encode(openssl_encrypt($text, 'AES-128-CBC', $enc_key, OPENSSL_RAW_DATA, str_repeat(chr(0), 16))));
}

function decrypt_str($text, $enc_key) {
	return trim(openssl_decrypt(base64_decode($text), 'AES-128-CBC', $enc_key, OPENSSL_RAW_DATA, str_repeat(chr(0), 16)));
}

//변수 받기
function getParam($name) {
	$result = "";
	if (isset($_POST[$name])) {
		$result = $_POST[$name];
		if (isset($_GET[$name])) {
			$result = $_GET[$name];
		}
	} else {
		if (isset($_GET[$name])) {
			$result = $_GET[$name];
		}
	}
	$result = is_scalar($result) ? trim((string) $result) : "";
	return $result;
}

function getNumber($str) {
	$str = str_replace(",","", $str);
	if (!is_numeric($str)) {
		$str = 0;
	} else {
		$str = $str + 0;
		if (!is_int($str)) {
			$str = 0;
		}
	}
	return $str;
}

function getFloat($str) {
	$str = str_replace(",","", $str);
	if (!is_numeric($str)) {
		$str = 0;
	} else {
		$str = $str + 0.0;
		if (!is_float($str)) {
			$str = 0;
		}
	}
	return $str;
}

function getYN($str) {
	if ($str != "Y") {
		$str = "N";
	}
	return $str;
}

function getCal($str) {
	$str = str_replace("-", "", $str);
	return $str;
}

function systemLog($s) {
	$logFileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/error.log';
	$fp = fopen($logFileName, 'a');
	$ss = "[" . date('Y-m-d H:i:s'). "] " . $s . "\n";
	fwrite($fp, $ss);
	fclose($fp);
}

define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
?>