<?
session_cache_limiter('no-cache, must-revalidate');
session_cache_expire(600);
session_start();

ini_set("memory_limit" , -1);

header("Pragma: no-cache");
//header("Cache-Control: no-cache,must-revalidate");  
?>