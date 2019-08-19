<?php

class TPaginator extends TCore {
	const delta = 4;

	static $class = 'page';
	static $classh = 'page_hover';
	static $classa = 'current';

	protected $module_path = '/engine/services/paginator';
	protected $module_id = 'paginator';
	protected $module_name = 'Paginator';

	private $page;

	public function __construct($MID, &$CONF, &$DB, &$TPL){
		global $mod_rewrite;
		parent::__construct($MID, $CONF, $DB, $TPL);

		$this->page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
	}

	public function __destruct(){
		parent::__desctruct();
	}

	//-- PUBLIC ----------------------------------------------------------------

	public function public_get_page(){
		return $this->page;
	}

	public function public_print_pages($count, $pp = 10, $anchor = '', $return = false){
        $max = ceil($count / max($pp, 1));
		if ($max > 1){
			if($return == false){
		    	$base_path = "{$this->CONF['base_tpl']}.pages_bar.pagination";

				$template = $this->template_file("/templates/html/modules", "module_paginator.tpl", $this->lang);
				$this->TPL->assign_file('PAGES_BAR', $template);
				$TPL = $this->TPL;
			} else {
		    	$base_path = "pagination";
				$template = $this->template_file("/templates/html/modules", "module_paginator.tpl", $this->lang);
				$TPL = new XTemplate($template);
			}

			if($this->page > 2 && $this->page > self::delta + 1){ // в начало
				$TPL->assign('PAGEBUTTON_FIRST', $this->path(1, $anchor));
				$TPL->parse("{$base_path}.page_first");
			}
			if($this->page - 1 > 0){ //предыдущая
				$TPL->assign('PAGEBUTTON_PREV', $this->path($this->page - 1, $anchor));
				$TPL->parse("{$base_path}.page_prev");
			}

			$start = max($this->page - self::delta, 1);
			$finish = min($this->page + self::delta, $max);

			$prev_group = max($start - self::delta, 1);
			$next_group = min($finish + self::delta, $max);

			if($start > 1){ // предыдущая группа страниц
				$data = array('link' => $this->path($prev_group, $anchor), 'class' => self::$class, 'num' => '...');
				$TPL->insert_loop("{$base_path}.page_items", array('PAGE' => $data));
			}

			for ($i = $start; $i <= $finish; $i++){
				$data = array('link' => $this->path($i, $anchor), 'class' => $i == $this->page ? self::$classa : self::$class, 'num' => $i);
				$TPL->insert_loop("{$base_path}.page_items", array('PAGE' => $data));
			}

			if($finish < $max){ // студующая группа страниц
				$data = array('link' => $this->path($next_group, $anchor), 'class' => self::$class, 'num' => '...');
				$TPL->insert_loop("{$base_path}.page_items", array('PAGE' => $data));
			}

			if($this->page < $max){ //следующая
				$TPL->assign('PAGEBUTTON_NEXT', $this->path($this->page + 1, $anchor));
				$TPL->parse("{$base_path}.page_next");
			}

			if($this->page < ($max - 1) && $this->page < ($max - self::delta)){ // в конец
				$TPL->assign('PAGEBUTTON_LAST', $this->path($max, $anchor));
				$TPL->assign('PAGEBUTTON_MAX', $max);
				$TPL->parse("{$base_path}.page_last");
			}

			if($return == false){
				$TPL->parse($base_path);
				$TPL->parse("{$this->CONF['base_tpl']}.pages_bar");
			} else{
				$TPL->parse($base_path);
				return $TPL->text($base_path);
			}
		}
	}

	public function process(){
	}

	public function execute(){
	}

	//-- PRIVATE ---------------------------------------------------------------

	//-- ADDITIONAL ------------------------------------------------------------

	private function path($p, $anchor){
		static $url;
		if(empty($url)){
			$url = parse_url($_SERVER['REQUEST_URI']);
			if(!key_exists('query', $url)) $url['query'] = '';
		}
		parse_str($url['query'], $get); unset($get['p']);
		if($p > 1) $get['p'] = $p;
		return sizeof($get) ? sprintf('%s/?%s%s', rtrim($url['path'], '/'), vars2url($get), $anchor) : sprintf('%s%s', $url['path'], $anchor);
	}

	//-- CORE ------------------------------------------------------------------

	public function is_main(){
		return false;
	}

	public function is_active(){
		return false;
	}
}

?>
