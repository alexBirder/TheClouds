<?php

//-- COOKIE ----------------------------------------------------------------

function socials_class(){
    global $CONF;
    $result = '';
    if(policy_socials() == 'y') $result = file_get_contents(ROOT . $CONF['template_mir'] . $CONF['socials_tpl']);
    return $result;
}

function services(){
    global $DB;
    if(isset($_COOKIE['login'])){
        $modules_limit = $DB->sql2result("SELECT `services` FROM settings_users WHERE `login` = '$_COOKIE[login]'");
        return $modules_limit;
    }
}

//-- CORE ------------------------------------------------------------------

function get_or_post($name){
	if(isset($_POST[$name])) return $_POST[$name];
	if(isset($_GET[$name])) return $_GET[$name];
	return null;
}

function trace($data){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

function flog($str){
	$f = fopen("{$_SERVER['DOCUMENT_ROOT']}/uploadfiles/cache/logs.txt", 'a+');
	if($f == true){
		$str = sprintf('%s%s', var_export($str, true), "\r\n");
		flock($f, LOCK_EX);
		fwrite($f, $str);
		fclose($f);
	}
}

function template_file($dir, $file, $lang){
	global $CONF;

	if(change_template() == 'n' || $CONF['langs'][$lang]['main'] == 1){
		$path = sprintf('%s%s/%s', ROOT, $dir, $file);
	} else {
		$path = sprintf('%s%s/%s/%s', ROOT, $dir, $lang, $file);
		if(!file_exists($path) || !is_file($path)) $path = sprintf('%s%s/%s', ROOT, $dir, $file);
	}

	return $path;
}

function table_param($table, $field, $conditions, $val = ''){
	global $DB;

	if(is_array($conditions)){
		$w = array();
		foreach ($conditions as $k=>$v){
			$w[] = sprintf("`%s` LIKE '%s'", $k, $v);
		}
		$WHERE = "WHERE " . join(' AND ', $w);
	} else {
		$WHERE = "WHERE `$conditions` LIKE '$val'";
	}

	$result = $DB->query("SELECT $field FROM $table $WHERE");
	return mysqli_num_rows($result) ? $DB->result($result) : null;
}

function error_page(){
    header('Location: /404.php');
}

function redirect($url, $r301 = true){
	if(($url = trim($url)) && mb_strlen($url)){
		if($urldata = parse_url($url)){
			if(!array_key_exists('scheme', $urldata)){
				$url = sprintf('http://%s%s', $_SERVER["SERVER_NAME"], $url);
			}
		}
		if($r301) header('HTTP/1.1 301 Moved Permanently');
		header("location: {$url}");
		if(session_id() == true) session_write_close();
		exit;
	}
	new Exception('Ошибка программы - неправильный редирект.', 500);
}

function back_link($link = null){
	if(isset($_SERVER['HTTP_REFERER']) && mb_strlen($_SERVER['HTTP_REFERER'])){
		$url_data = parse_url($_SERVER['HTTP_REFERER']);
		if(strcasecmp($url_data['host'], $_SERVER['HTTP_HOST']) == 0){
			return $_SERVER['HTTP_REFERER'];
		}
	}
	return $link;
}

function is_external_link($url){
	if($urldata = parse_url(trim($url))){
		if(array_key_exists('scheme', $urldata) && strcasecmp($urldata['host'], $_SERVER["SERVER_NAME"]) != 0){
			return true;
		}
	}
	return false;
}

function translit_url($string){
	static $cyr = array(
		"Щ", "Ш", "Ч","Ц", "Ю", "Я", "Ж","А","Б","В", "Г","Д","Е","Ё","З","И","Й","К","Л","М","Н",
		"О","П","Р","С","Т","У","Ф","Х","Ь","Ы","Ъ", "Э","Є", "Ї","І",
		"щ", "ш", "ч","ц", "ю", "я", "ж","а","б","в", "г","д","е","ё","з","и","й","к","л","м","н",
		"о","п","р","с","т","у","ф","х","ь","ы","ъ", "э","є", "ї","і"
	);
	static $lat = array(
		"Shch","Sh","Ch","C","Yu","Ya","J","A","B","V", "G","D","e","e","Z","I","y","K","L","M","N",
		"O","P","R","S","T","U","F","H","", "Y","" ,"E","E","Yi","I",
		"shch","sh","ch","c","Yu","Ya","j","a","b","v", "g","d","e","e","z","i","y","k","l","m","n",
		"o","p","r","s","t","u","f","h", "", "y","" ,"e","e","yi","i"
	);
	$string = trim($string);
	for($i = 0, $max = sizeof($cyr); $i < $max; $i++){
		$c_cyr = $cyr[$i];
		$c_lat = $lat[$i];
		$string = mb_str_replace($c_cyr, $c_lat, $string);
	}
	$string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]e/u", "\${1}e", $string);
	$string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]/u", "\${1}'", $string);
	$string = preg_replace("/([eyuioaEYUIOA]+)[Kk]h/u", "\${1}h", $string);
	$string = preg_replace("/^kh/u", "h", $string);
	$string = preg_replace("/^Kh/u", "H", $string);
	$string = preg_replace("/\W/u", " ", $string);
	$string = preg_replace("/[[:space:]]+/u", "-", trim($string));
	return mb_strtolower($string);
}

function word4num($number, $titles){
    $cases = array(2, 0, 1, 1, 1, 2);
    return $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function vars2url($array, $parent = ''){
	$params = array();
	foreach($array as $k => $v){
		if(is_array($v))
			$params[] = vars2url($v, (empty($parent) ? urlencode($k) : $parent . '[' . urlencode($k) . ']'));
		else
			$params[] = (!empty($parent) ? $parent . '[' . urlencode($k) . ']' : urlencode($k)) . '=' . urlencode($v);
	}
	$sessid = session_id();
	if(!empty($parent) || empty($sessid)) return implode('&', $params);
	$sessname = session_name();
	if(ini_get('session.use_cookies')){
		if (!ini_get('session.use_only_cookies') && (!isset($_COOKIE[$sessname]) || ($_COOKIE[$sessname] != $sessid)))
			$params[] = $sessname . '=' . urlencode($sessid);
		} elseif(!ini_get('session.use_only_cookies')){
			$params[] = $sessname . '=' . urlencode($sessid);
		}
	return implode('&', $params);
}

function date2str($date){
	global $lang;
	$lang = empty($lang) ? 'ru' : $lang;
	$months = array(
		'ru' => array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'),
		'ua' => array('січня', 'лютого', 'березня', 'квітня', 'травня', 'червня', 'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'),
		'en' => array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')
	);
	if(preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', $date, $parts) == false){
		$time = strtotime($date);
		$parts = array(1 => date('d', $time), 2 => date('m', $time), 3 => date('Y', $time));
	}
	$date_str = sprintf('%2d %s %4d', $parts[1], $months[$lang][$parts[2] - 1], $parts[3]);
	return $date_str;
}

function cp2utf($str, $from = 'windows-1251', $to = 'utf-8'){
	return iconv($from, $to, $str);
}

function utf2cp($str, $from = 'utf-8', $to = 'windows-1251'){
	return iconv($from, $to, $str);
}

function short_words($var, $len = 3){
	return mb_strlen($var) > $len;
}

function word_limiter($text, $limit = 30){
	if(mb_strlen($text) > $limit){
		$words = preg_split('/ +/u', $text, -1, PREG_SPLIT_NO_EMPTY);
		$text = '';
		while(count($words) > 0 && mb_strlen($text . $words[0]) < $limit){
			$text .= array_shift($words) . " ";
		}
		$text = rtrim($text) . '&hellip;';
	}
	return $text;
}

function youtube($link, $width = 320, $height = 240) {
    return preg_replace(
        "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
        "<iframe src=\"//www.youtube.com/embed/$2\" width='".$width."' height='".$height."' frameborder='0'></iframe>",
        $link
    );
}

function ip(){
	if(!function_exists('ip_first')){
		function ip_first($ips){
			if (($pos = strpos($ips, ',')) != false){
				return substr($ips, 0, $pos);
			} else {
				return $ips;
			}
		}
	}
	if(!function_exists('ip_valid')){	
		function ip_valid($ips){
			if(isset($ips)){
				$ip = ip_first($ips);
				$ipnum = ip2long($ip);
				if($ipnum !== -1 && $ipnum !== false && (long2ip($ipnum) === $ip)){ // PHP 4 and PHP 5
					if (($ipnum < 167772160   || $ipnum >   184549375) && // Not in 10.0.0.0/8
						($ipnum < -1408237568 || $ipnum > -1407188993) && // Not in 172.16.0.0/12
						($ipnum < -1062731776 || $ipnum > -1062666241))   // Not in 192.168.0.0/16
						return true;
				}
			}
			return false;
		}
	}
	$check = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM', 'HTTP_CLIENT_IP');
	foreach ($check as $c){
		if (ip_valid($_SERVER[$c])){
			return ip_first($_SERVER[$c]);
		}
	}
	return $_SERVER['REMOTE_ADDR'];
}

function array2matrix($source, $columns = 4){
	$result = null;
	$size = sizeof($source);

	if($size > $columns && $columns > 0){
		$rows = ceil($size / $columns);
		$currentIndex = 0;

		$result = array();
		for($i = 0; $i < $rows; ++$i)
			$result[$i] = array();
		do {
			$page = 0;
			while( ($size - ($page + 1) * $rows) >= ($columns - ($page + 1)))
				++$page;

			for($i = 1; $i <= $page; ++$i) {
				for($j = 0; $j < $rows; ++$j) {
					$result[$j][] = $source[$currentIndex++];
					--$size;
				}
			}
			$columns = $columns - $page;
			--$rows;

			if($rows == 1){
				for($i = 0; $i < $columns; ++$i)
					$result[$rows - 1][] = $source[$currentIndex++];
				break;
			}
			if($columns == 1) {
				for($i = 0; $i < $size; ++$i)
					$result[$i][] = $source[$currentIndex++];
				break;
			}
		} while($size);
	} else {
	    if(!is_array($source)) return $source;
		$matrix = array();
		$array = array_chunk($source, ceil($size / $columns));
	    foreach($array as $row => $cval){
			if(is_array($cval)) foreach ($cval as $col => $val) $matrix[$col][$row] = $val;
			else $matrix[0][$row] = $cval;
		}
	    return $matrix;
	}
	return $result;
}

if(!function_exists('mb_str_replace')){
	function mb_str_replace($needle, $replacement, $haystack){
		$needle_len = mb_strlen($needle);
		$replacement_len = mb_strlen($replacement);
		$pos = mb_strpos($haystack, $needle);
		while ($pos !== false){
			$haystack = mb_substr($haystack, 0, $pos) . $replacement . mb_substr($haystack, $pos + $needle_len);
		    $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
		}
		return $haystack;
	}
}

if(!function_exists('mb_strcasecmp')){
	function mb_strcasecmp($str1, $str2, $encoding = null) {
	    if (null === $encoding) { $encoding = mb_internal_encoding(); }
	    return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
	}
}

if(!function_exists('mb_str_pad')){
	function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT){
		$diff = mb_strlen($input) - mb_strlen($input, 'UTF-8');
		return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
	}
}

if(!function_exists('array_replace')){
	function array_replace(array $array, array $array1){
		$args = func_get_args();
		$count = func_num_args();
		for($i = 0; $i < $count; ++$i){
			if(is_array($args[$i])){
				foreach($args[$i] as $key => $val){
					$array[$key] = $val;
				}
			} else {
				trigger_error(__FUNCTION__ . '(): Argument #' . ($i + 1) . ' is not an array', E_USER_WARNING);
				return null;
			}
		}
		return $array;
	}
}

//-- BASE ------------------------------------------------------------------

function project_reply(){
    global $DB; return $DB->sql2result("SELECT `project_email_reply` FROM settings_project");
}

function project_email(){
    global $DB; return $DB->sql2result("SELECT `project_email` FROM settings_project");
}

function project_name(){
    global $DB; return $DB->sql2result("SELECT `project_name` FROM settings_project");
}

function project_bread(){
    global $DB, $lang; $bread = $DB->sql2result("SELECT `project_bread` FROM settings_project");
    return TTranslate::get($bread, $lang);
}

function project_title($lang){
    global $DB; return $DB->sql2result("SELECT `title` FROM settings_titles WHERE `lang` = '$lang'");
}

function project_scripts(){
    global $DB; return $DB->sql2result("SELECT `project_scripts` FROM settings_project");
}

function project_favicon(){
    global $DB, $CONF; return $CONF['upload_dir'] . '/favicons/' . $DB->sql2result("SELECT `project_favicon` FROM settings_project");
}

function project_description($lang){
    global $DB; return $DB->sql2result("SELECT `description` FROM settings_titles WHERE `lang` = '$lang'");
}

function project_keywords($lang){
    global $DB; return $DB->sql2result("SELECT `keywords` FROM settings_titles WHERE `lang` = '$lang'");
}

function project_status(){
    global $DB; return $DB->sql2result("SELECT `project_status` FROM settings_project");
}

function change_menu(){
    global $DB; return $DB->sql2result("SELECT `change_menu` FROM settings_project");
}

function change_template(){
    global $DB; return $DB->sql2result("SELECT `change_template` FROM settings_project");
}

function change_gzip(){
    global $DB; return $DB->sql2result("SELECT `change_gzip` FROM settings_project");;
}

function change_minify(){
    global $DB; return $DB->sql2result("SELECT `change_minify` FROM settings_project");
}

function change_adblock(){
    global $DB; return $DB->sql2result("SELECT `change_adblock` FROM settings_project");
}

function policy_socials(){
    global $DB; return $DB->sql2result("SELECT `policy_socials` FROM settings_project");
}

function policy_cookie(){
    global $DB; return $DB->sql2result("SELECT `policy_cookie` FROM settings_project");
}

function policy_confidence(){
    global $DB; return $DB->sql2result("SELECT `policy_confidence` FROM settings_project");
}

function module_nova(){
    global $DB; return $DB->sql2result("SELECT `c_nova` FROM catalogue_settings WHERE `id` = '1'");
}

function module_click(){
    global $DB; return $DB->sql2result("SELECT `c_click` FROM catalogue_settings WHERE `id` = '1'");
}

function module_ajax(){
    global $DB; return $DB->sql2result("SELECT `c_ajax` FROM catalogue_settings WHERE `id` = '1'");
}

function module_online(){
    global $DB; return $DB->sql2result("SELECT `c_online` FROM catalogue_settings WHERE `id` = '1'");
}
