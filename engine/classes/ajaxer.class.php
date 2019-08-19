<?php

class TAjaxer{
	static $format = null;
	static $content = null;

	static function set_format($format = 'JSON'){
		self::$format = $format;
	}

	static function set($str){
		self::$content = $str;
	}

	static function get(){
		$return = '';
		if(is_array(self::$content) || strlen(self::$content) > 0){
			switch(self::$format){
				case 'JSON': $return = self::getJSON(); break;
				case 'TEXT': $return = self::$content; break;
				default: $return = self::getJSON();
			}
		}
		return $return;
	}

	static function getJSON(){
		return json_encode(self::$content);
	}
}

?>