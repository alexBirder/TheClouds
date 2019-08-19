<?php

class TGzip{
	static function out(&$contents){
		if(extension_loaded('zlib')){
			ob_start('ob_gzhandler');
		}
		echo $contents;
	}
}

?>