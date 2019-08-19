<?php

class TSession{
	static function set($key, $val){
		if(!empty($_SESSION[$key]))	TSession::del($key);
		$_SESSION[$key] = $val;
		return $val;
	}

	static function get($key){
		return !empty($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	static function del($key){
		if(isset($_SESSION[$key])) unset($_SESSION[$key]);;
	}
}

?>