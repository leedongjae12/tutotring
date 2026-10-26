<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text; charset=utf-8">
<body style="margin:80;">
<?
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	switch ($lang){
	    case "en":
			?>
			<p align="center"><img src="/images/error-e.jpg" border="0" alt="Page Not Found. We have encountered a system error while processing your request. We apologize for the inconvenience. The requested page does not exist or system error. Please check URL and try again." title="Page Not Found. We have encountered a system error while processing your request. We apologize for the inconvenience. The requested page does not exist or system error. Please check URL and try again.">
			<?
	        break;        
	    default:
			?>
			<p align="center"><img src="/images/error.jpg" border="0" alt="Page Not Found. 서비스 이용에 불편을 드려 죄송합니다. 요청하신 페이지가 존재하지 않거나, 일시적인 시스템 장애입니다. 해당 서비스의 주소가 이동, 삭제되었거나 접속 장애입니다. 입력하신 주소가 정확한지 확인 후 다시 시도해 주시기 바랍니다. " title="Page Not Found. 서비스 이용에 불편을 드려 죄송합니다. 요청하신 페이지가 존재하지 않거나, 일시적인 시스템 장애입니다. 해당 서비스의 주소가 이동, 삭제되었거나 접속 장애입니다. 입력하신 주소가 정확한지 확인 후 다시 시도해 주시기 바랍니다. ">
			<?
	        break;
	}
?>
</body>
</html>