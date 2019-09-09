<?php

class TPage extends TCore {
    protected $module_path = '/engine/services/page';
    protected $module_id = 'page';
    protected $module_name = 'Статические страницы';

    private $item;
    private $id;

    public function __construct($MID, $CONF, $DB, $TPL){
        global $mod_rewrite;

        parent::__construct($MID, $CONF, $DB, $TPL);

        if($this->is_main()){
            $this->item = (int)get_or_post('item');
            if(count($mod_rewrite) > 0){
                $last_param = trim($mod_rewrite[count($mod_rewrite) - 1]);
                if(preg_match('/^([\w0-9\-]+)\.html$/i', $last_param, $args)){
                    $this->item = (int)$this->DB->sql2result("SELECT `id` FROM `page_items` WHERE `url` = '{$args[1]}'");
                    if(!$this->item) error_page();
                }
            }
            if($this->item == false) $this->item = 2;
        }

        $this->load_plugins($this, 'page');
    }

    //-- PUBLIC ----------------------------------------------------------------

    public function process(){
        if($this->item == '11'){
            $template = $this->template_file("/templates/html/modules", "module_page_ord.tpl", $this->lang);
        } else {
            $template = $this->template_file("/templates/html/modules", "module_page.tpl", $this->lang);
        }

        $this->TPL->assign_file('WORK_PART', $template);

        if($this->item > 0){
            $data = $this->DB->sql2row("SELECT `title`, `text` FROM `page_items` WHERE `id` = {$this->item} AND `enabled` = 'y'");
            if($this->item == '11') $this->call_module('settings', 'get_orders_inside', '.work_part');
            $this->transform_data($data, $this->item);
            $this->TPL->assign('CMS_DATA', $data['text']);
        } else {
            error_page();
        }

        $this->TPL->parse($this->CONF['base_tpl'] . '.work_part');
    }

    public function execute(){
        global $module;

        if($this->item){
            $background = $this->DB->sql2result("SELECT `bg` FROM `page_items` WHERE `id` = '{$this->item}' AND `enabled` = 'y'");
        } else {
            switch($module){
                case 'news': $background = '/templates/img/simple.jpg'; break;
                default: $background = null;
            }
        }
        $this->TPL->assign('BACKGROUND', $background ?: '/templates/img/simple.jpg');

        switch($module){
            case $this->module_id:
                $this->sub_menu();
                break;
            default:
                $this->seo_block();
                break;
        }
    }

    public function printversion(){}

    //-- PRIVATE ---------------------------------------------------------------

    private function seo_block(){
        $text = $this->DB->sql2row("SELECT `id`, `title`, `text` FROM `page_items` WHERE `url` LIKE 'seo'");
        if($text){
            $data = array('id' => $text['id'], 'title' => $text['title'], 'text' => $text['text']);
            $this->transform_data($data, $data['id']);
            $this->TPL->assign('SEO', $data);
            if($data['text']) $this->TPL->parse($this->CONF['base_tpl'] . '.seo_block');
        }
    }

    private function sub_menu(){
        if(is_numeric($this->item) && $this->item > 0){
            $parents = TTree::path($this->DB, 'page_items', $this->item);
            $top_id = count($parents) ? array_shift($parents) : $this->item;

            if(true){
                $path = array_merge(TTree::path($this->DB, 'page_items', $this->item), (array)$this->item);
                $children = TTree::children_all($this->DB, 'page_items', $top_id, "enabled = 'y' AND `menu` = 'y'", array('`sort` ASC'), null, $path);
                $template = $this->template_file("/templates/html/modules", "module_categories.tpl", $this->lang);
                $this->TPL->assign_file('SUB_MENU', $template);

                foreach($children as $child){
                    $data = $class = array();
                    if($this->item == $child['id']) $class[] = 'active';
                    if($child['level'] == 2) $class[] = 'sub';
                    if($child['level'] == 3) $class[] = 'sub2';
                    if($child['level'] == 4) $class[] = 'sub3';
                    $data['offset'] = intval(($child['level'] - 1) * 10);
                    $data['link'] = $this->path($child['id']);
                    $data['title'] = $child['title'];
                    $data['class'] = implode(' ', $class);
                    $this->transform_data($data, $child['id']);
                    $this->TPL->insert_loop($this->CONF['base_tpl'] . ".sub_menu.row", array('SUBMENU' => $data));
                }

                $this->TPL->parse($this->CONF['base_tpl'] . ".sub_menu");
            }
        }
    }

    //-- ADDITIONAL ------------------------------------------------------------

    public function path($item_id = 0, $lang = null){
        if($lang === null){ $lang = $this->CONF['langs'][$this->lang]['main'] == 1 ? "" : "/$this->lang"; }
        else { $lang = "/$lang"; }

        if($item_id > 0){
            $element = $this->DB->sql2row("SELECT `url`, `islink` FROM `page_items` WHERE `id` = {$item_id}");
            if($element['islink'] == 'n'){
                $path = $lang . "/$this->module_id";
                $parents = TTree::path($this->DB, 'page_items', $item_id);
                foreach($parents as $parent_id){
                    $selement = $this->DB->sql2row("SELECT `url`, `islink` FROM `page_items` WHERE `id` = '{$parent_id}'");
                    $path .= $selement['islink'] == 'n' ? sprintf('/%s', urlencode($selement['url'])) : $selement['url'];
                }
                $path .= $element['islink'] == 'n' ? sprintf('/%s.html', urlencode($element['url'])) : $element['url'];
            } else {
                $path = $element['url'];
            }
        } else {
            $path = $lang;
        }

        return $path;
    }

    //-- CORE ------------------------------------------------------------------

    public function transform_data(&$data, $id, $tolang = null){
        $lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
        $pattern = array('title', 'text', 'meta_title', 'meta_description', 'meta_keywords');
        if($this->CONF['langs'][$lang]['main'] == 0){
            $keys = join("`, `", array_intersect($pattern, array_keys($data)));
            $sql = sprintf("SELECT `%s` FROM page_items_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
            $data = array_replace($data, $this->DB->sql2row($sql));
        }
        return $data;
    }

    public function get_doctitle(){
        $title = array(project_title($this->lang));
        if($this->item){
            $data = array();
            $data = $this->DB->sql2row("SELECT `title`, `meta_title` FROM `page_items` WHERE `id` = {$this->item}");
            $this->transform_data($data, $this->item);
            $title[] = $data['meta_title'] ? $data['meta_title'] : $data['title'];
        }
        $this->transform_data($data, $this->item);
        return join(array_reverse($title), ' - ');
    }

    public function get_pagetitle(){
        if($this->item){
            $data = array();
            $data = $this->DB->sql2row("SELECT `title`, `meta_title` FROM `page_items` WHERE `id` = {$this->item}");
            $this->transform_data($data, $this->item);
            $title = $data['title'];
        } else {
            $title = $this->module_name;
        }
        return $title;
    }

    public function get_description(){
        if($this->item) {
            $data = array();
            $data = $this->DB->sql2row("SELECT `meta_description` FROM `page_items` WHERE `id` = {$this->item}");
            $this->transform_data($data, $this->item);
            return $data['meta_description'];
        } else {
            return project_description($this->lang);
        }
    }

    public function get_keywords(){
        if($this->item) {
            $data = array();
            $data = $this->DB->sql2row("SELECT `meta_keywords` FROM `page_items` WHERE `id` = {$this->item}");
            $this->transform_data($data, $this->item);
            return $data['meta_keywords'];
        } else {
            return project_keywords($this->lang);
        }
    }

    public function get_navigationstring(){
        $ns = array();

        $path = TTree::path($this->DB, 'page_items', $this->item);
        $path = count($path) > 0 ? array_merge($path, (array)$this->item) : (array)$this->item;
        foreach($path as $parent_id){
            $data = array();
            $data['title'] = $this->DB->sql2result("SELECT `title` FROM `page_items` WHERE `id` = {$parent_id}");
            $this->transform_data($data, $parent_id);
            $ns[] = sprintf('<a href="%s">%s</a>', $this->path($parent_id), $data['title']);
        }

        return join($ns, $this->CONF['nav_separator']);
    }

    public function get_template(){
        if($this->item && ($template = $this->DB->sql2result("SELECT `template` = 'wide' FROM `page_items` WHERE `id` = {$this->item}")))
            return 'global_wide.tpl';
        else
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