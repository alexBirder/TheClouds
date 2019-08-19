<?php

class TAdwords extends TCore {
	protected $module_path = '/engine/services/adwords';
	protected $module_id = 'adwords';
	protected $module_name = 'Рекламные баннеры';

	public function __construct($MID, $CONF, $DB, $TPL){
		parent::__construct($MID, $CONF, $DB, $TPL);
        $this->load_plugins($this, 'adwords');
	}

	//-- PUBLIC ----------------------------------------------------------------

	public function process(){}

	public function execute(){
		$this->adwords_top();
	}

	public function printversion(){}

	//-- PRIVATE ---------------------------------------------------------------

    private function adwords_top(){
        $template = $this->template_file("/templates/html/adwords", "adwords_top.tpl", $this->lang);
        $this->TPL->assign_file('MAIN_SLIDER_TOP', $template);
        $items = $this->DB->sql2array("SELECT * FROM adwords_items WHERE `enabled` = 'y' ORDER BY `sort` DESC");
        foreach ($items as $item){
            $data = array('id' => $item['id'], 'title' => $item['title'], 'intro' => $item['intro'], 'url' => $item['url'], 'banner' => $this->CONF['upload_dir'] . '/banners/' . $item['image']);
            $this->transform_item_data($data, $item['id']);
            $this->TPL->insert_loop($this->CONF['base_tpl'] . '.main_slider_top.item', array('ITEM' => $data));
        }
        $this->TPL->parse($this->CONF['base_tpl'] . '.main_slider_top');
    }

	//-- CORE ------------------------------------------------------------------

    public function transform_item_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('title', 'intro', 'url');
        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM adwords_items_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
    }

	public function get_doctitle(){}

	public function get_pagetitle(){}

	public function get_description(){}

	public function get_keywords(){}

	public function get_navigationstring(){}

	public function get_template(){
		return 'global_default.tpl';
	}

	public function is_main(){
		return false;
	}

	public function is_active(){
		return true;
	}
}
