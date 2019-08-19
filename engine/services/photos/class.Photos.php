<?php

class TPhotos extends TCore {
	protected $module_path = '/engine/services/photos';
	protected $module_id = 'photos';
	protected $module_name = 'Фотогалерея';

	private $issue;
	private $ppage;

	public function __construct($MID, $CONF, $DB, $TPL){
		global $mod_rewrite;

		parent::__construct($MID, $CONF, $DB, $TPL);

        if($this->is_main()){
			if(count($mod_rewrite) > 0){
				$last_param = trim($mod_rewrite[count($mod_rewrite) - 1]);
				if(preg_match('/^([\w0-9\-]+)\.html$/i', $last_param, $args)){
					$this->issue = (int)$this->DB->sql2result("SELECT `id` FROM `photos_issues` WHERE `url` = '{$args[1]}'");
					if(!$this->issue) error_page();
				}
			}
			$this->ppage = 150;
		}

        $this->load_plugins($this, 'photos');
	}

	//-- PUBLIC ----------------------------------------------------------------

	public function process(){
		if($this->issue == false){
			$this->issues_show();
		}
		else{
			$issues = TTree::sub_ids($this->DB, 'photos_issues', $this->issue);
			if(sizeof($issues) == 0){
				array_push($issues, $this->issue);
				$this->items_show($issues);
			}
			else{
				$this->issues_show();
			}
		}
	}

	public function execute(){
		global $module, $lang;

		switch($module){
			case $this->module_id:
				$this->sub_menu();
				break;
			default:
				break;
		}
	}

	public function printversion(){
	}

	//-- PRIVATE ---------------------------------------------------------------

	private function sub_menu(){
		$template = $this->template_file("/templates/html/modules", "module_categories.tpl", $this->lang);
		$this->TPL->assign_file('SUB_MENU', $template);

		$top_id = $this->issue;
		$children = TTree::children($this->DB, 'photos_issues', $top_id, null, array('`sort` DESC'));

		if(sizeof($children) == 0){
			$parents = TTree::path($this->DB, 'photos_issues', $this->issue);
			$top_id = count($parents) ? array_pop($parents) : 0;
			$children = TTree::children($this->DB, 'photos_issues', $top_id, null, array('`sort` DESC'));
		}

		foreach($children as $child){
			$data = array();
			$data['link'] = $this->path($child['id']);
			$data['title'] = $child['title'];
			$data['class'] = $child['id'] == $this->issue ? 'active' : '';
            $this->transform_issue_data($data, $child['id']);
			$this->TPL->insert_loop($this->CONF['base_tpl'] . '.sub_menu.row', array('SUBMENU' => $data));
		}

		if(count($children) > 0){
			$this->TPL->parse($this->CONF['base_tpl'] . '.sub_menu');
		}
	}

	private function issues_show(){
		$template = $this->template_file("/templates/html/photos", "photos_issue.tpl", $this->lang);
		$this->TPL->assign_file('WORK_PART', $template);

		$conditions = array("i.`enabled` = 'y'");
		$conditions[] = $this->issue ? "i.`parent` = {$this->issue}" : "i.`parent` = 0";
		$current_page = $this->call_module('paginator', 'public_get_page');
		$ppage = 9;

		$where = '(' . join($conditions, ') AND (') . ')';
		$limit = sprintf('%d, %d', ($current_page - 1) * $ppage, $ppage);
		$sql = "SELECT i.`id`, i.`title`, (SELECT `prev` FROM `photos_items` WHERE `issue` = i.`id` ORDER BY `sort` ASC LIMIT 1) AS `image`
				FROM `gallery_issues` AS i
				WHERE {$where}
				ORDER BY i.`sort` DESC
				LIMIT {$limit}";
		$issues = $this->DB->sql2array($sql);

		foreach($issues as $issue){
			$issue_data = array(
				'id' => $issue['id'],
				'title' => str_replace('_', ' ', $issue['title']),
				'image' => sprintf('%s/photos/%s', $this->CONF['upload_dir'], $issue['image']),
				'link' => $this->path($issue['id']),
			);
            $this->transform_issue_data($issue_data, $issue['id']);
			$this->TPL->assign(array('GALLERY_ISSUE' => $issue_data));
			$this->TPL->parse($this->CONF['base_tpl'] . '.work_part.issues_list');
		}

		$total = $this->DB->sql2result("SELECT COUNT(*) FROM `photos_issues` AS i WHERE {$where}");
		$this->call_module('paginator', 'public_print_pages', $total, $ppage);
		$this->TPL->parse($this->CONF['base_tpl'] . '.work_part');
	}

	private function items_show($issues){
		$template = $this->template_file("/templates/html/photos", "photos_items.tpl", $this->lang);
		$this->TPL->assign_file('WORK_PART', $template);

		$conditions = array("i.`enabled` = 'y'");
		$current_page = $this->call_module('paginator', 'public_get_page');
		$ppage = $this->ppage;

		$conditions[] = "i.`issue` IN (" . join(', ', $issues) . ")";

		$where = '(' . join($conditions, ') AND (') . ')';
		$limit = sprintf('%d, %d', ($current_page - 1) * $ppage, $ppage);
		$sql = "SELECT SQL_CALC_FOUND_ROWS i.*
				FROM `photos_items` AS i
				WHERE {$where}
				ORDER BY i.`sort` ASC
				LIMIT {$limit}";

		$resource = $this->DB->query($sql);
		$total = $this->DB->sql2result("SELECT FOUND_ROWS()");

		while($item = $this->DB->fetch_array($resource)){
			$item_data = array(
				'id' => $item['id'],
				'title' => htmlspecialchars($item['title']),
				'prev' => sprintf('%s/photos/%s', $this->CONF['upload_dir'], $item['prev']),
				'full' => sprintf('%s/photos/%s', $this->CONF['upload_dir'], $item['full']),
			);
            $this->transform_item_data($item_data, $item['id']);
			$item_data['intro'] = word_limiter($item_data['title'], 55);
			$this->TPL->assign(array('ITEM' => $item_data));
			$this->TPL->parse($this->CONF['base_tpl'] . '.work_part.items_list');
		}

		$this->canonical($this->path($this->issue));
		$this->call_module('paginator', 'public_print_pages', $total, $this->ppage);
		$this->TPL->parse($this->CONF['base_tpl'] . '.work_part');
	}

	//-- ADDITIONAL ------------------------------------------------------------

	public function path($issue_id = 0, $lang = null){
		if($lang === null){ $lang = $this->CONF['langs'][$this->lang]['main'] == 1 ? "" : "/$this->lang"; }
		else { $lang = "/{$lang}"; }

		if($issue_id > 0){
			$url = $this->DB->sql2result("SELECT `url` FROM `photos_issues` WHERE `id` = {$issue_id}");
			$path = $lang . "/{$this->module_id}";
			$parents = TTree::path($this->DB, 'photos_issues', $issue_id);
			foreach($parents as $parent_id){
				$_url = $this->DB->sql2result("SELECT `url` FROM `photos_issues` WHERE `id` = '{$parent_id}'");
				$path .= sprintf('/%s', urlencode($_url));
			}
			$path .= sprintf('/%s.html', urlencode($url));
		}
		else{
			$path = $lang;
		}

		return $path;
	}

	//-- CORE ------------------------------------------------------------------

    public function transform_issue_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('title');

        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM photos_issues_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
    }

    public function transform_item_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('title');

        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM photos_items_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
    }

	public function get_doctitle(){
		$title = array(TTranslate::get($this->module_name, $this->lang));
		if($this->issue) $title[] = $this->DB->sql2result("SELECT `title` FROM `photos_issues` WHERE `id` = {$this->issue}");
		return join(array_reverse($title), ' - ');
	}

	public function get_pagetitle(){
		if($this->issue){
			$data = $this->DB->sql2row("SELECT `title` FROM `photos_issues` WHERE `id` = {$this->issue}");
            $this->transform_issue_data($data, $this->issue);
			return $data['title'];
		}
		return TTranslate::get($this->module_name, $this->lang);
	}

	public function get_description(){
		return $this->CONF['metadesc'][$this->lang];
	}

	public function get_keywords(){
		return $this->CONF['metakeys'][$this->lang];
	}

	public function get_navigationstring(){
		$ns = array();
		$ns[] = sprintf('<a href="%s">%s</a>', $this->path(), $this->module_name);
		return join($ns, $this->CONF['nav_separator']);
	}

	public function get_template(){
		//return !$this->issue || sizeof(TTree::sub_ids($this->DB, 'gallery_issues', $this->issue)) ? 'wide.html' : 'default.html';
		return 'global_default.tpl';
	}

	public function is_main(){
		global $module;
		return isset($module) && strcmp($module, $this->module_id) == 0;
	}

	public function is_active(){
		return true;
	}
}

?>