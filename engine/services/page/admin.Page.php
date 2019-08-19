<?php

class TPage extends TAdmin{
    protected $module_path = '/engine/services/page';
    protected $module_id = 'page';
    protected $module_name = 'Статические страницы';

    function __construct($CONF, $DB){
        parent::__construct($CONF, $DB);

        $this->actions		= array(1 => 'Просмотр всех', 2 => 'Добавить страницу');
        $this->action		= ($action = get_or_post('action')) ? intval($action) : 1;

        $this->add_plugin('pagedescription');
        $this->load_plugins($this, 'page');
    }

    public function __destruct(){
        parent::__destruct();
    }

    public function execute(){
        switch($this->action){
            case 1:
                $this->items_show(); break;
            case 2:
                $this->items_add(); break;
        }
    }

    // ITEMS BLOCK -------------------------------------------------------------

    public function settings_main(){
        $bases = $this->DB->sql2array("SELECT * FROM settings_history ORDER by `date` DESC LIMIT 14");
        $page_count = $this->DB->sql2result("SELECT COUNT(*) FROM page_items");
        $news_count = $this->DB->sql2result("SELECT COUNT(*) FROM news_items");
        $banners_count = $this->DB->sql2result("SELECT COUNT(*) FROM adwords_items");
        require_once(ROOT . "/engine/services/settings/html/admin/settings_main.tpl");
    }

    private function items_show(){
        $move_id = isset($_GET['move']) || isset($_POST['move']) ? get_or_post('move') : 0;
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;

            $item_to_edit = $this->DB->sql2row("SELECT * FROM page_items WHERE `id` = '$edit_id'");

            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $parent = intval($_POST['parent']);
                $url = trim($_POST['url']);
                $data = array();

                if(isset($_POST['islink']) && $_POST['islink'] == 'n' && preg_match('/[^A-Za-z0-9_]/i', $url)){
                    $this->AddError("URL страницы <b>'" . stripslashes($url) . "'</b> недопустимо.");
                }
                elseif($this->DB->sql2result("SELECT `id` FROM page_items WHERE `id` <> '$edit_id' AND `url` LIKE '$url'") > 0){
                    $this->AddError("Такой URL страницы уже существует '<b>" . stripslashes($url) . "</b>'.");
                }
                else{
                    $data['title']				= htmlspecialchars($_POST['title']);
                    $data['url']				= htmlspecialchars(trim($_POST['url']));
                    $data['text']				= addslashes($_POST['text']);
                    $data['meta_title']			= addslashes($_POST['meta_title']);
                    $data['meta_description']	= addslashes($_POST['meta_description']);
                    $data['meta_keywords']		= addslashes($_POST['meta_keywords']);
                    $data['bg']					= $_POST['bg'];
                    $data['islink']				= strval($_POST['islink']);
                    $data['menu']				= strval($_POST['menu']);
                    $data['enabled']			= strval($_POST['enabled']);
                    $data['template']			= strval($_POST['template']);

                    if(TTree::update($this->DB, 'page_items', $edit_id, $data)){
                        TTree::move($this->DB, 'page_items', $edit_id, $parent);
                        $this->call_plugins('pagedescription', 'admin_item_update', $edit_id);
                        if(change_menu() == 'y') $this->call_plugins('settingsdescription', 'admin_menu_update', $edit_id);
                        $this->AddMessage('Страница "' . stripslashes($data['title']) . '" успешно обновлена.');
                        THistory::send('Страница <b>' . stripslashes($data['title']) . '</b> успешно обновлена.');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    }
                    else{
                        $this->AddError($this->DB->errors());
                    }
                }
            }

            if($return == true){
                //$FCK = TEditor::instance();
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/items_save.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $title = $this->DB->sql2result("SELECT `title` FROM page_items WHERE `id` = {$delete_id}");
            if(TTree::remove_all($this->DB, 'page_items', $delete_id)){
                $this->AddMessage("Страница &laquo;<b>$title</b>&raquo; успешно удалена.");
                THistory::send('Страница <b>'.$title.'</b> успешно удалена.');
            }
            else{
                $this->AddError($this->DB->errors());
            }
        }

        if($move_id > 0){
            $neighbours = TTree::neighbours($this->DB, 'page_items', $move_id, null, array('`sort` ASC', '`id` ASC'));
            if(strtolower(get_or_post('to')) == 'up') $neighbours = array_reverse($neighbours);

            foreach($neighbours as $key => &$neighbour){
                if($neighbour['id'] == $move_id && isset($neighbours[$key + 1])){
                    $owner = $this->DB->sql2row("SELECT * FROM page_items WHERE `id` = '$move_id'");
                    $change = $neighbours[$key + 1];

                    if(max($owner['sort'], $change['sort']) > 0){
                        $new_owner_sort = intval($change['sort']);
                        $new_change_sort = intval($owner['sort']);

                        $query1 = "UPDATE page_items SET `sort` = $new_owner_sort WHERE `id` = '$move_id'";
                        $query2 = "UPDATE page_items SET `sort` = $new_change_sort WHERE `id` = '$change[id]'";

                        if($this->DB->query($query1) && $this->DB->query($query2)){
                            $title =  $this->DB->sql2result("SELECT `title` FROM page_items WHERE `id` = {$move_id}");
                            $this->AddMessage("Страница &laquo;<b>$title</b>&raquo; успешно перенесена.");
                            THistory::send("Страница <b>".$title."</b> успешно перенесена.");
                        }
                        else{
                            $this->AddError($this->DB->errors());
                        }
                    }
                    else{
                        $this->AddError("Неправильные индексы сортировки");
                    }
                    break;
                }
            }
        }

        $items = TTree::children_all($this->DB, 'page_items', 0, null, array('`sort` ASC', '`id` ASC'));

        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/items_show.tpl");
    }

    private function items_add(){
        if(isset($_POST['save']) && $_POST['save'] == 'yes'){
            $root	= intval($_POST['parent']);
            $url	= trim($_POST['url']);

            $reload	= true;
            $data	= array();

            if(isset($_POST['islink']) && $_POST['islink'] == 'n' && preg_match('/[^A-Za-z0-9_]/i', $url)){
                $this->AddError("URL страницы <b>'" . stripslashes($url) . "'</b> недопустимо.");
            } elseif($this->DB->num_rows($this->DB->query("SELECT `id` FROM page_items WHERE `url` LIKE '$url'")) > 0){
                $this->AddError("Такой URL страницы уже существует '<b>" . stripslashes($url) . "</b>'.");
            } else {
                $data['title']				= htmlspecialchars($_POST['title']);
                $data['url']				= htmlspecialchars(trim($_POST['url']));
                $data['text']				= addslashes($_POST['text']);
                $data['meta_title']			= addslashes($_POST['meta_title']);
                $data['meta_description']	= addslashes($_POST['meta_description']);
                $data['meta_keywords']		= addslashes($_POST['meta_keywords']);
                $data['bg']					= $_POST['bg'];
                $data['islink']				= strval($_POST['islink']);
                $data['menu']				= strval($_POST['menu']);
                $data['enabled']			= strval($_POST['enabled']);
                $data['template']			= strval($_POST['template']);

                if(TTree::insert($this->DB, 'page_items', $root, $data)){
                    $insert_id = $this->DB->insert_id();
                    $this->DB->query("UPDATE page_items SET `sort` = $insert_id WHERE `id` = $insert_id LIMIT 1");
                    $this->call_plugins('pagedescription', 'admin_item_add', $insert_id);
                    if(change_menu() == 'y') $this->call_module('settings', 'settings_menu_add', $root, $insert_id);
                    $this->AddMessage('Страница "' . $data['title'] . '" успешно добавлена в базу.');
                    THistory::send('Страница <b>' . $data['title'] . '</b> успешно добавлена в базу.');
                    $reload	= false;
                } else {
                    $this->AddError($this->DB->errors());
                }

            }

            $item_to_edit = array();
            if($reload == true){
                $item_to_edit['parent'] = intval($_POST['parent']);
                $item_to_edit['url'] = strtolower($_POST['url']);
                $item_to_edit['title'] = addslashes(htmlspecialchars($_POST['title']));
                $item_to_edit['text'] = addslashes($_POST['text']);
                $item_to_edit['meta_title'] = addslashes(htmlspecialchars($_POST['meta_title']));
                $item_to_edit['meta_description'] = addslashes(htmlspecialchars($_POST['meta_description']));
                $item_to_edit['meta_keywords'] = addslashes(htmlspecialchars($_POST['meta_keywords']));
                $item_to_edit['bg'] = $_POST['bg'];
                $item_to_edit['template'] = strval($_POST['template']);
            }
            else{
                $item_to_edit['parent'] = intval($_POST['parent']);
            }
            $item_to_edit = array_map('stripslashes', $item_to_edit);
        }

        //$FCK = TEditor::instance();
        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/items_save.tpl");
    }

    private function items_list($default = null){
        echo '<select name="parent" class="areasTable--input">';
        echo '<option value="0" class="listTitle">-- в корень --</option>';
        $items = TTree::children_all($this->DB, 'page_items', 0, null, array('`sort` ASC', '`id` ASC'));
        foreach($items as $item){
            printf('<option value="%d" %s>%s%s</option>', $item['id'], $item['id'] == $default ? 'selected' : '', str_repeat('&nbsp;', $item['level'] * 3), $item['title']);
        }
        echo '</select>';
    }

}