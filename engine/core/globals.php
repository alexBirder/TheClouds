<?php

$__GET = array();
if(key_exists('REQUEST_URI', $_SERVER) && preg_match('/\?(.+)/', $_SERVER['REQUEST_URI'], $get_params)){
	parse_str($get_params[1], $__GET);
	$_GET = array_merge($_GET, $__GET);
	$_REQUEST = array_merge($_REQUEST, $__GET);
}
unset($__GET);

?>