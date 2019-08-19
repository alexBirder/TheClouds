<?php

class TCore {
	protected $module_path = '';
	protected $module_id = '';
	protected $module_name = '';

	protected $MID;
	protected $CONF;
	protected $DB;
	protected $TPL;

	protected $observers;

	public $lang;
	public $plugins;

	public function __get($name){
		if(preg_match('/module_path|module_id|module_name/', $name))
			return $this->$name;
	}

	protected function __construct($MID, $CONF, $DB, $TPL){
		global $lang, $mod_rewrite;
		$this->MID	= $MID;
		$this->CONF	= $CONF;
		$this->DB	= $DB;
		$this->TPL	= $TPL;

		$this->observers = array();
		$this->lang = strtolower(trim($lang));
		$this->plugins = array();
		set_exception_handler(array($this, 'exception_handler'));
	}

	protected function __desctruct(){}

	// PUBLIC ------------------------------------------------------------------

	public function __initialize(){
		return true;
	}

	public function exception_handler(Throwable $e){
		header("HTTP/1.0 500 Internal server error");
		print('Uncaught '.get_class($e).', code: ' . $e->getCode() . "<br />Message: " . htmlentities($e->getMessage(), null, 'utf-8')."\n");
	}

	public function observe_module($module_name, $event, $callback){
		global $ALL_MODS;

		if(isset($ALL_MODS[$module_name]) && is_object($ALL_MODS[$module_name])){
			if(method_exists($this, $callback)){
				$event = strtolower($event);
				return call_user_func_array(array($ALL_MODS[$module_name], 'observe'), array($this, $event, $callback));
			}
		}

		return null;
	}

	public function observe($observer, $event, $callback){
		if(!array_key_exists($event, $this->observers)) $this->observers[$event] = array();
		$this->observers[$event][] = array($observer, $callback);
	}

	public function fire($event){
		$event = strtolower($event);
		if(array_key_exists($event, $this->observers) && is_array($this->observers[$event])){
			$stack = debug_backtrace(); $args = array();
			if(is_array($stack[0]["args"]) && count($stack[0]["args"]) > 1){
				for($i = 1, $size = count($stack[0]["args"]); $i < $size; $i++){
					$args[] = $stack[0]["args"][$i];
				}
			}
			foreach($this->observers[$event] as $i => &$observer){
				call_user_func_array(array($observer[0], $observer[1]), $args);
			}
		}
	}

	protected function settings($key, $value = null){
		if($this->DB->num_rows($this->DB->query("SHOW TABLES LIKE 'settings'")) > 0){
			$key = trim($key);
			$_value = trim($this->DB->sql2result("SELECT `value` FROM `settings` WHERE `key` = '{$key}'"));
			if($_value) $value = $_value;
		}
		return $value;
	}

	protected function add_plugin($name){
		$name = strtolower(trim($name));
		$this->plugins[$name] = array();
	}

	protected function load_plugins($parent, $name){
		foreach($this->plugins as $interface => $objects){
			$ipath = realpath(sprintf('%s/%s/%s.interface.php', ROOT, $this->CONF['services_dir'] . '/' . $name . '/translate/', strtolower($interface)));
			$ifile = preg_replace('/^I(\w+)$/i', '\\1', $interface) . '.interface.php';
			if(file_exists($ipath) && is_file($ipath)){
				require_once($ipath);
				$dir_name = substr($ifile, 0, ($len = strpos($ifile, '.')) > 0 ? $len : strlen($ifile));
				$dir = ROOT . $this->CONF['services_dir'] . '/' . $name  . '/translate/' . strtolower($dir_name);
				if(($handle = opendir($dir)) == false) $this->AddError("Can not open path: $ipath.");
				while(($node = readdir($handle)) !== false){
					if($node != "." && $node != ".."){
						if(is_dir($dir . '/' . $node)){
							$plugin_file = sprintf('%s/%s/%s.%s.plugin.php', $dir, $node, $node, $dir_name);
							if(file_exists($plugin_file) && is_file($plugin_file)){
								require_once($plugin_file);
							}
						}
					}
				}
				closedir($handle);
			}
		}

		$classes = get_declared_classes();
		foreach($this->plugins as $interface => &$objects){
			$interface = 'I' . ucfirst(strtolower($interface));
			foreach($classes as $class){
				$reflection = new ReflectionClass($class);
				if($reflection->implementsInterface($interface))
					$objects[] = new $class($parent, $this->CONF, $this->DB, $this->MID, $this->TPL);
			}
		}
	}

	public function canonical($url = null){
		if($url !== null){
			if(strcasecmp($_SERVER['REQUEST_URI'], $url) != 0){
				$canonical = sprintf('%s%s', $this->CONF['url'], $url);
			}	
		}
		if(isset($canonical) && strlen($canonical) > 0){
			$this->TPL->assign(array('CANONICAL' => $canonical));
			$this->TPL->parse($this->CONF['base_tpl'] . '.canonical');
		}
	}

	public function get_issue_id(){
		return isset($this->issue) ? (int)$this->issue : 0;
	}

	public function get_item_id(){
		return isset($this->item) ? (int)$this->item : 0;
	}

	public function call_module($module_name, $module_method){
		global $ALL_MODS;
		if(isset($ALL_MODS[$module_name]) && is_object($ALL_MODS[$module_name])){
			if(method_exists($ALL_MODS[$module_name], $module_method)){
				$args = array_slice(func_get_args(), 2);
				return call_user_func_array(array($ALL_MODS[$module_name], $module_method), $args);
			}
		}
		return null;
	}

	public function call_plugins(){
		$stack = debug_backtrace();
		if(isset($stack[0]["args"]) && is_array($stack[0]["args"]) && count($stack[0]["args"]) >= 2){
			$plugins_group = array_shift($stack[0]["args"]);
			$method = array_shift($stack[0]["args"]);
			$args = array();
			for($i = 0, $size = count($stack[0]["args"]); $i < $size; $i++){
				$args[] = $stack[0]["args"][$i];
			}
			if(isset($this->plugins[$plugins_group])){
				foreach($this->plugins[$plugins_group] as $plugin_id => $plugin_obj){
					if(method_exists($plugin_obj, $method) && $plugin_obj->executable()){
						$return = call_user_func_array(array($plugin_obj, $method), $args);
						if($return) return $return;
					}
				}
			}
		}
		return false;
	}

	public function template_file($dir, $file, $lang){
		if(change_template() == 'n' || $this->CONF['langs'][$lang]['main'] == 1){
			$path = sprintf('%s%s/%s', ROOT, $dir, $file);
		} else {
			$path = sprintf('%s%s/%s/%s', ROOT, $dir, $lang, $file);
			if(!file_exists($path) || !is_file($path)) $path = sprintf('%s%s/%s', ROOT, $dir, $file);
		}
		return $path;
	}

	public function get_navigationstring_core(){
		$navy = $this->get_navigationstring();
		if(mb_strlen($navy) > 0){
			$links = explode($this->CONF['nav_separator'], $navy);
			$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
			$template = '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="%s"><span itemprop="title">%s</span></a>%s</span>';
			$navy_arr = array(); $max = sizeof($links) - 1;
			foreach($links as $i => $link){
				if(preg_match("/$regexp/siU", $link, $match)){
					$separator = ($i != $max) ? $this->CONF['nav_separator'] : '';
					$navy_arr[] = sprintf($template, $match[2], $match[3], $separator);
				}
			}
			$navy = join('', $navy_arr);
		}
		return $navy;
	}
}

?>