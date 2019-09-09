<?php

class TSettings extends TCore {
    protected $module_path = '/engine/services/settings';
    protected $module_id = 'settings';
    protected $module_name = 'Настройки';

    public function __construct($MID, $CONF, $DB, $TPL){
        parent::__construct($MID, $CONF, $DB, $TPL);
        $this->load_plugins($this, 'settings');
    }

    //-- PUBLIC ----------------------------------------------------------------

    public function process(){
        $this->word_translate();
        $this->menu_main();
        $this->get_orders();
        $this->get_orders_inside();
        $this->show_cookies();
    }

    public function execute(){
        $this->word_translate();
        $this->menu_main();
        $this->get_orders();
        $this->get_orders_inside();
        $this->show_cookies();
    }

    //-- PRIVATE ---------------------------------------------------------------

    public function get_orders(){
        $template = $this->template_file("/templates/html/modules", "module_order.tpl", $this->lang);
        $this->TPL->assign_file('ORDERS', $template);

        $json_link = file_get_contents("https://panel.theclouds.pro/modules/addons/fs/tariff-list.php");
        $items = json_decode($json_link, true);

        foreach ($items as $item){
            $data = array(
                'type' => $item['hostingaccount'],
                'name' => $item['name'],
                'price' => array(
                    'value' => $item['price']['price'],
                    'prefix' => $item['price']['prefix'],
                    'period' => $item['price']['period'],
                ),
                'url' => base64_decode($item['url']),
                'cpu' => $item['cpu'],
                'ram' => $item['ram'],
                'hdd' => $item['hdd'],
                'system' => $item['system'],
                'dedicated_ips' => $item['dedicated_ips'],
            );
            $this->TPL->assign(array('ORD' => $data));
            $this->TPL->parse($this->CONF['base_tpl'] . '.orders.item');
        }

        $this->TPL->parse($this->CONF['base_tpl'] . '.orders');
    }

    public function get_orders_inside(){
        $template = $this->template_file("/templates/html/modules", "module_order.tpl", $this->lang);
        $this->TPL->assign_file('ORDERS_INSIDE', $template);

        $json_link = file_get_contents("https://panel.theclouds.pro/modules/addons/fs/tariff-list.php");
        $items = json_decode($json_link, true);

        foreach ($items as $item){
            $data = array(
                'type' => $item['hostingaccount'],
                'name' => $item['name'],
                'price' => array(
                    'value' => $item['price']['price'],
                    'prefix' => $item['price']['prefix'],
                    'period' => $item['price']['period'],
                ),
                'url' => base64_decode($item['url']),
                'cpu' => $item['cpu'],
                'ram' => $item['ram'],
                'hdd' => $item['hdd'],
                'system' => $item['system'],
                'dedicated_ips' => $item['dedicated_ips'],
            );
            $this->TPL->assign(array('ORD' => $data));
            $this->TPL->parse($this->CONF['base_tpl'] . '.work_part.orders_inside.item');
        }

        $this->TPL->parse($this->CONF['base_tpl'] . '.work_part.orders_inside');
    }

    public function show_cookies(){
        if(policy_cookie() == 'y') {
            $template = $this->template_file("/templates/html/modules", "module_cookies.tpl", $this->lang);
            $this->TPL->assign_file('COOKIES', $template);
        }
    }

    public function word_translate(){
        $words = $this->DB->sql2array("SELECT `id`, `word` FROM settings_words");
        foreach($words as $word){
            $data = array('id' => $word['id'], 'word' => $word['word']);
            $this->transform_data($data, $data['id']);
            $this->TPL->assign('WORD_'.$data['id'].'', $data['word']);
        }
    }

    public function get_word($id){
        $data = $this->DB->sql2row("SELECT `id`, `word` FROM settings_words WHERE `id` = {$id}");
        $this->transform_data($data, $data['id']);
        return $data['word'];
    }

    public function menu_main(){
        $template = $this->template_file("/templates/html/modules", "module_menu.tpl", $this->lang);
        $this->TPL->assign_file('MAIN_MENU', $template);

        $top_menu = TTree::children($this->DB, 'settings_menu', 0, "`enabled` = 'y'", array('`sort` ASC', '`id` ASC'));
        foreach ($top_menu as $menu){
            $top_data = array('link' => $this->lang . $menu['url'], 'title' => $menu['title']);

            $sub_menu = TTree::children($this->DB, 'settings_menu', $menu['id'], "`enabled` = 'y'", array('`sort` ASC', '`id` ASC'));
            foreach ($sub_menu as $sub){
                $sub_data = array('link' => $this->lang . $sub['url'], 'title' => $sub['title']);
                $this->transform_menu_data($sub_data, $sub['id']);
                $this->TPL->assign(array('MENU_SUB' => $sub_data));
                $this->TPL->parse($this->CONF['base_tpl'] . '.main_menu.top_menu.sub_menu');
            }

            $this->transform_menu_data($top_data, $menu['id']);
            $this->TPL->assign(array('MENU_TOP' => $top_data));
            $this->TPL->parse($this->CONF['base_tpl'] . '.main_menu.top_menu');
        }

        $this->TPL->parse($this->CONF['base_tpl'] . '.main_menu');
    }

    //-- CORE ------------------------------------------------------------------

    public function transform_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('word');
        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM settings_words_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
        return $data;
    }

    public function transform_menu_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('title');
        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM settings_menu_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
        return $data;
    }

    public function get_doctitle(){
        return $this->module_name;
    }

    public function get_pagetitle(){
        return $this->module_name;
    }

    public function get_description(){
        return project_description($this->lang);
    }

    public function get_keywords(){
        return project_keywords($this->lang);
    }

    public function get_navigationstring(){
        return null;
    }

    public function get_template(){
        return 'global_default.tpl';
    }

    public function is_main(){
        global $module;
        return strcmp($module, $this->module_id) == 0;
    }

    public function is_active(){
        return true;
    }

}