<?php

class TNews extends TCore {
	protected $module_path = '/includes/services/news';
	protected $module_id = 'news';
	protected $module_name = 'Новости';

	private $issue;
	private $item;
	private $ppage;

	public function __construct($MID, $CONF, $DB, $TPL){
		global $mod_rewrite, $media;
		parent::__construct($MID, $CONF, $DB, $TPL);
		if($this->is_main()){
			if(count($mod_rewrite) > 0 && $media == 'normal'){
				$issue_url = trim($mod_rewrite[0]);
				$this->issue = (int)$this->DB->sql2result("SELECT `id` FROM `news_issues` WHERE `url` = '{$issue_url}'");
				if(!$this->issue) error_page();
				if(array_key_exists(1, $mod_rewrite)){
					$item_url = trim($mod_rewrite[1]);
					if(preg_match('/^([\w0-9\-]+)\.html$/i', $item_url, $args)){
						$this->item = (int)$this->DB->sql2result("SELECT `id` FROM `news_items` WHERE `url` = '{$args[1]}'");
						if(!$this->item) error_page();
					} else {
                        error_page();
					}
				}
			} else {
				$this->item = (int)get_or_post('item');
			}
			$this->ppage = 10;
		}

		$this->load_plugins($this, 'news');
	}

	//-- PUBLIC ----------------------------------------------------------------

	public function process(){
		$this->item ? $this->news_item() : $this->news_issue();
	}

	public function execute(){
		global $module;

		switch($module){
			case $this->module_id:
				$this->sub_menu();
				break;
			default:
                $this->news_main();
				break;
		}

	}

	public function printversion(){}

	//-- PRIVATE ---------------------------------------------------------------

	private function sub_menu(){
		$template = $this->template_file("/templates/html/modules", "module_categories.tpl", $this->lang);
		$this->TPL->assign_file('SUB_MENU', $template);

		$children = $this->DB->sql2array("SELECT `id`, `title` FROM `news_issues` ORDER BY `sort`");
		foreach($children as $child){
			$data = array();
			$data['link'] = $this->path($child['id']);
			$data['title'] = $child['title'];
			$data['class'] = $child['id'] == $this->issue ? 'active' : '';
            $this->transform_issue_data($data, $child['id']);
			$this->TPL->insert_loop($this->CONF['base_tpl'] . '.sub_menu.row', array('SUBMENU' => $data));
		}

		$this->TPL->parse($this->CONF['base_tpl'] . '.sub_menu');
	}

    private function news_main(){
        $template = $this->template_file("/templates/html/news", "news_main.tpl", $this->lang);
        $this->TPL->assign_file('NEWS_MAIN', $template);
        $image = new TThumbnail();

        $items = $this->DB->sql2array("SELECT * FROM news_items WHERE `enabled` = 'y' AND `main_list` = 'y' ORDER BY `date` DESC");
        foreach ($items as $item){
            $data = array(
                'title' => word_limiter($item['title'], 80),
                'intro' => word_limiter($item['intro'], 80),
                'image' => $image->src($this->CONF['upload_dir'] . '/news/' . $item['prev'], 310, 310),
                'link' => $this->path($item['issue'], $item['id']),
                'date' => date2str($item['date'])
            );
            $this->transform_item_data($data, $item['id']);
            $this->TPL->assign(array('ITEM' => $data));
            $this->TPL->parse($this->CONF['base_tpl'] . '.news_main.item');
        }

        $this->TPL->parse($this->CONF['base_tpl'] . '.news_main');
    }

    private function news_issue(){
        $template = $this->template_file("/templates/html/news", "news_issue.tpl", $this->lang);
        $this->TPL->assign_file('WORK_PART', $template);

        $ppage = $this->ppage;
        $conditions = array("`enabled` = 'y'");
        if($this->issue) $conditions = array("`issue` = {$this->issue}");
        $current_page = $this->call_module('paginator', 'public_get_page');
        $where = '(' . join($conditions, ') AND (') . ')';
        $limit = sprintf('%d, %d', ($current_page - 1) * $ppage, $ppage);

        $items = $this->DB->sql2array("SELECT *, IF(`images` IN (1,3), null, `prev`) AS image FROM news_items WHERE  {$where} ORDER BY `date` DESC LIMIT {$limit}");
        $total = $this->DB->sql2result("SELECT COUNT(*) FROM news_items WHERE {$where}");
        $image = new TThumbnail();

        foreach ($items as $item){
            $data = array(
                'title' => word_limiter($item['title'], 80),
                'intro' => word_limiter($item['intro'], 180),
                'link' => $this->path($item['issue'], $item['id']),
                'date' => date2str($item['date']),
                'images' => $item['images']
            );
            if($data['images'] == 1 || $data['images'] == 3) $data['image'] = $item['image'] ? $image->src($this->CONF['upload_dir'] . '/news/' . $item['image'], 260, 260) : '';
            $this->transform_item_data($data, $item['id']);
            $this->TPL->assign(array('ITEM' => $data));
            if(strlen($data['image'])) $this->TPL->parse($this->CONF['base_tpl'] . '.work_part.item.image');
            $this->TPL->parse($this->CONF['base_tpl'] . '.work_part.item');
        }

        $this->call_module('paginator', 'public_print_pages', $total, $this->ppage);
        $this->TPL->parse($this->CONF['base_tpl'] . '.work_part');
    }

    private function news_item(){
        $SQL = "SELECT *, IF(`images` IN (2,3), null, `full`) AS image FROM news_items WHERE `id` = {$this->item} AND `enabled` = 'y'";
        $item = $this->DB->sql2row($SQL);
        if(empty($item) || count($item) == 0) error_page();
        $template = $this->template_file("/templates/html/news", "news_item.tpl", $this->lang);
        $this->TPL->assign_file('WORK_PART', $template);
        $image = new TThumbnail();

        $item_data = array();
        $item_data['image'] = $item['image'] ? $image->src($this->CONF['upload_dir'] . '/news/' . $item['image'], 1090, 1090) : '';
        $item_data['title'] = $item['title'];
        $item_data['text'] = $item['text'];
        $item_data['author'] = $item['author'];
        $item_data['source'] = strlen($item['link']) ? sprintf('<a href="%s" target="_blank">%s</a>', $item['link'], ($item['source'] ? $item['source'] : $item['link'])) : $item['source'];
        $item_data['date'] = date2str($item['date']);

        $this->transform_item_data($item_data, $item['id']);
        $this->TPL->assign(array('ITEM' => $item_data));
        if(strlen(trim($item_data['image']))) $this->TPL->parse($this->CONF['base_tpl'] . '.work_part.image');
        if(strlen(trim($item_data['author']))) $this->TPL->parse($this->CONF['base_tpl'] . '.work_part.author');
        if(strlen(trim($item_data['source']))) $this->TPL->parse($this->CONF['base_tpl'] . '.work_part.source');
        $this->TPL->parse($this->CONF['base_tpl'] . '.work_part');
    }

	//-- ADDITIONAL ------------------------------------------------------------

	public function path($issue_id = 0, $item_id = 0, $lang = null){
		if($lang === null){ $lang = $this->CONF['langs'][$this->lang]['main'] == 1 ? "" : "/$this->lang"; } else { $lang = "/$lang"; }
		$path = "$lang/$this->module_id";
		if($issue_id > 0){
			$url = $this->DB->sql2result("SELECT `url` FROM `news_issues` WHERE `id` = {$issue_id}");
			$path .= sprintf('/%s', urlencode($url));
		}
		if($item_id > 0){
			$url = $this->DB->sql2result("SELECT `url` FROM `news_items` WHERE `id` = {$item_id}");
			$path .= sprintf('/%s.html', urlencode($url));
		}
		return $path;
	}

	//-- CORE ------------------------------------------------------------------

    public function transform_issue_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('title', 'meta_title', 'meta_keywords', 'meta_description');

        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM news_issues_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
    }

    public function transform_item_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('title', 'intro', 'text', 'meta_title', 'meta_keywords', 'meta_description');

        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM news_items_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
    }

	public function get_doctitle(){
		$title = array(project_title($this->lang), TTranslate::get($this->module_name, $this->lang));
		if($this->issue && empty($this->item)){
			$data = $this->DB->sql2row("SELECT `title` FROM `news_issues` WHERE `id` = {$this->issue}");
            $this->transform_issue_data($data, $this->issue);
			$title[] = $data['title'];
		}
		if($this->item){
			$data = $this->DB->sql2row("SELECT `title`, `meta_title` FROM `news_items` WHERE `id` = {$this->item}");
            $this->transform_item_data($data, $this->item);
			$title[] = $data['meta_title'] ?: $data['title'];
		}
		return join(array_reverse($title), ' - ');
	}

	public function get_pagetitle(){
        if($this->item){
            $data = $this->DB->sql2row("SELECT `title` FROM `news_items` WHERE `id` = {$this->item}");
            $this->transform_item_data($data, $this->item);
            return $data['title'];
        }
		if($this->issue){
			$data = $this->DB->sql2row("SELECT `title` FROM `news_issues` WHERE `id` = {$this->issue}");
            $this->transform_issue_data($data, $this->issue);
			return $data['title'];
		}
		return TTranslate::get($this->module_name, $this->lang);
	}

	public function get_description(){
		if($this->item && ($description = $this->DB->sql2result("SELECT `meta_description` FROM `news_items` WHERE `id` = {$this->item}")))
			return $description;
		else
			return project_description($this->lang);
	}

	public function get_keywords(){
		if($this->item && ($keywords = $this->DB->sql2result("SELECT `meta_keywords` FROM `news_items` WHERE `id` = {$this->item}")))
			return $keywords;
		else
			return project_keywords($this->lang);
	}

	public function get_navigationstring(){
		$ns = array();
		$ns[] = sprintf('<a href="%s">%s</a>', $this->path(), TTranslate::get($this->module_name, $this->lang));
		return join($ns, $this->CONF['nav_separator']);
	}

	public function get_template(){
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