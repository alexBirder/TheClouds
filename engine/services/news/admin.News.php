<?php

class TNews extends TAdmin{
    protected $module_path = '/engine/services/news';
    protected $module_id = 'news';
    protected $module_name = 'Новости и публикации';

    function __construct($CONF, $DB){
        parent::__construct($CONF, $DB);

        $this->actions		= array(1 => 'Просмотр всех', 2 => 'Добавить новость', 3 => 'Рубрикатор', 4 => 'Добавить рубрику');
        $this->action		= ($action = get_or_post('action')) ? intval($action) : 1;
        $this->page_max 	= 25;

        $this->add_plugin('newsdescription');
        $this->load_plugins($this, 'news');
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
            case 3:
                $this->issues_show(); break;
            case 4:
                $this->issues_add(); break;
        }
    }

    // ITEMS BLOCK -------------------------------------------------------------

    private function items_show(){
        $move_id = isset($_GET['move']) || isset($_POST['move']) ? get_or_post('move') : 0;
        $filter = isset($_GET['filter']) || isset($_POST['filter']) ? get_or_post('filter') : 0;
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;

            if(isset($_GET['image']) && strtolower(trim($_GET['image'])) == 'delete'){
                $this->items_remove_photos($edit_id);
                $save_path = $this->CONF['upload_dir'] . '/news';
                if($this->DB->query("UPDATE news_items SET `prev` = NULL, `full` = NULL WHERE `id` = '$edit_id'"))
                    $this->AddMessage('Картинка успешно удалена.');
                else
                    $this->AddError($this->DB->errors());
            }

            $item_to_edit = $this->DB->sql2row("SELECT * FROM news_items WHERE `id` = '$edit_id'");

            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                if($this->validate_url($_POST['url'], 'news_items', 'url', $edit_id) == false){
                    $this->AddError("URL страницы <b>'" . stripslashes($_POST['url']) . "'</b> недопустимо или уже существует.");
                }
                else{
                    $data = array();

                    $data['issue']	 			= intval($_POST['parent']);
                    $data['url']				= htmlspecialchars($_POST['url']);
                    $data['title']				= addslashes(htmlspecialchars($_POST['title']));
                    $data['intro']				= addslashes($_POST['intro']);
                    $data['text']				= addslashes($_POST['text']);
                    $data['author']				= htmlspecialchars($_POST['author']);
                    $data['source']				= htmlspecialchars($_POST['source']);
                    $data['link']				= htmlspecialchars($_POST['link']);
                    $data['date']				= date('Y-m-d', strtotime($_POST['date']));
                    $data['meta_title']			= $_POST['meta_title'];
                    $data['meta_description']	= $_POST['meta_description'];
                    $data['meta_keywords']		= $_POST['meta_keywords'];
                    $data['images']				= intval($_POST['images']);
                    $data['enabled']			= strval($_POST['enabled']);
                    $data['main_list']			= strval($_POST['main_list']);
                    $data['main_top']			= strval($_POST['main_top']);

                    if(isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']["tmp_name"])){
                        $this->items_remove_photos($edit_id);

                        $save_path = $this->CONF['upload_dir'] . '/news';
                        if($image_big = TImager::load_resize('photo', $save_path, array('width' => 1000, 'height' => 1000))){
                            $image_small = TImager::copy_resize($save_path . '/' . $image_big, $save_path, array('width' => 380, 'height' => 380));
                        }

                        $data['prev'] = $image_small ? $image_small : null;
                        $data['full'] = $image_big ? $image_big : null;
                    }

                    if ($this->NoErrors()){
                        if ($this->DB->update('news_items', $edit_id, $data)){
                            $this->call_plugins('newsdescription', 'admin_item_update', $edit_id);
                            $this->AddMessage('Новость "'.stripslashes($data['title']).'" успешно обновлена.');
                            THistory::send('Новость <b>' . stripslashes($data['title']) . '</b> успешно обновлена.');
                            unset($_GET['edit'], $_POST['edit']);
                            $return = false;
                        }
                        else{
                            $this->AddError($this->DB->errors());
                        }
                    }
                }
            }

            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/items_save.tpl");
                return false;
            }
        }

        if($move_id > 0){
            $rows = TTree::neighbours($this->DB, 'news_items', $move_id, null, array('`sort` DESC', '`id` DESC'));
            if(strtolower(get_or_post('to')) == 'up') $rows = array_reverse($rows);

            foreach($rows as $key => $neighbour){
                if($neighbour['id'] == $move_id && isset($rows[$key + 1])){
                    $owner = $this->DB->sql2row("SELECT * FROM news_items WHERE `id` = {$move_id}");
                    $change = $rows[$key + 1];

                    if(max($owner['sort'], $change['sort']) > 0){
                        $new_owner_sort = intval($change['sort']);
                        $new_change_sort = intval($owner['sort']);

                        $query1 = "UPDATE news_items SET `sort` = {$new_owner_sort} WHERE `id` = {$move_id}";
                        $query2 = "UPDATE news_items SET `sort` = {$new_change_sort} WHERE `id` = {$change[id]}";

                        if($this->DB->query($query1) && $this->DB->query($query2)){
                            $title = $this->DB->sql2result("SELECT `title` FROM news_items WHERE `id` = {$move_id}");
                            $this->AddMessage("Новость &laquo;<b>$title</b>&raquo; успешно перенесена.");
                            THistory::send('Новость <b>' . $title . '</b> успешно перенесена.');
                        } else {
                            $this->AddError($this->DB->errors());
                        }
                    } else {
                        $this->AddError("Неправильные индексы сортировки.");
                    }
                    break;
                }
            }
        }

        if($delete_id > 0){
            if($this->items_delete($delete_id)){
                $title = table_param('news_items', 'title', 'id', $delete_id);
                $this->AddMessage("Новость успешно удалена.");
                THistory::send('Новость <b>' . $title . '</b> успешно удалена.');
            }
            else{
                $this->AddError($this->DB->errors());
            }
        }

        $conditions = array("1");

        if($filter > 0){
            $sub_ids = TTree::sub_ids($this->DB, 'news_issues', $filter);
            $sub_ids[] = (int)$filter;
            $conditions[] = "i.`issue` IN (" . join(', ', $sub_ids) . ")";
        }

        $where = join(' AND ', $conditions);
        $limit = sprintf('%d, %d', ($this->CurrentPage() - 1) * $this->page_max, $this->page_max);
        $sql = "SELECT i.* FROM `news_items` AS i WHERE {$where} ORDER BY i.`sort` DESC LIMIT {$limit}";

        $items = $this->DB->sql2array($sql);

        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/items_filter.tpl");
        require_once(ROOT . "$this->module_path/html/admin/items_show.tpl");

        $total = $this->DB->sql2result("SELECT COUNT(*) FROM `news_items` AS i WHERE {$where}");
        $this->PrintPages($total, $this->page_max);
    }

    private function items_remove_photos($element){
        if(is_array($element) && sizeof($element) > 0){
            foreach($element as $id) $this->items_remove_photos($id);
        } else{
            $fields = array('prev', 'full');
            $files = $this->DB->sql2row("SELECT `" . join('`,`', $fields) . "` FROM `news_items` WHERE `id` = {$element}");
            foreach($files as $file){
                $path = sprintf('%s%s/news/%s', ROOT, $this->CONF['upload_dir'], $file);
                if(file_exists($path) && is_file($path)) unlink($path);
            }
            for($i = 0, $keys = array(); $i < sizeof($fields); $i++) $keys[$fields[$i]] = null;
            return $this->DB->update('news_items', $element, $keys);
        }
        return true;
    }

    private function items_delete($delete_id){
        $mid = Id2Mid($this->module_id);

        $this->call_plugins('newsdescription', 'admin_item_delete', $delete_id);
        $this->items_remove_photos($delete_id);

        return $this->DB->query("DELETE FROM `news_items` WHERE `id` = '{$delete_id}'");
    }

    private function items_add(){
        if(isset($_POST['save']) && $_POST['save'] == 'yes'){
            $reload	= true;

            if($this->validate_url($_POST['url'], 'news_items') == false){
                $this->AddError("URL страницы <b>'" . stripslashes($_POST['url']) . "'</b> недопустимо или уже существует.");
            } else {
                $data	= array();

                $data['issue']	 			= intval($_POST['parent']);
                $data['url']				= htmlspecialchars($_POST['url']);
                $data['title']				= addslashes(htmlspecialchars($_POST['title']));
                $data['intro']				= addslashes($_POST['intro']);
                $data['text']				= addslashes($_POST['text']);
                $data['author']				= htmlspecialchars($_POST['author']);
                $data['source']				= htmlspecialchars($_POST['source']);
                $data['link']				= htmlspecialchars($_POST['link']);
                $data['date']				= date('Y-m-d', strtotime($_POST['date']));
                $data['meta_title']			= $_POST['meta_title'];
                $data['meta_description']	= $_POST['meta_description'];
                $data['meta_keywords']		= $_POST['meta_keywords'];
                $data['images']				= intval($_POST['images']);
                $data['enabled']			= strval($_POST['enabled']);
                $data['main_list']			= strval($_POST['main_list']);
                $data['main_top']			= strval($_POST['main_top']);

                if(isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']["tmp_name"])){
                    $save_path = $this->CONF['upload_dir'] . '/news';
                    if($image_big = TImager::load_resize('photo', $save_path, array('width' => 1000, 'height' => 1000))){
                        $image_small = TImager::copy_resize($save_path . '/' . $image_big, $save_path, array('width' => 380, 'height' => 380));
                    }
                    $data['prev'] = $image_small ? $image_small : null;
                    $data['full'] = $image_big ? $image_big : null;
                }

                if ($this->NoErrors()){
                    if ($this->DB->insert('news_items', $data) == true){
                        $insert_id = $this->DB->insert_id();
                        $this->DB->query("UPDATE news_items SET `sort` = {$insert_id} WHERE `id` = {$insert_id} LIMIT 1");
                        $this->call_plugins('newsdescription', 'admin_item_add', $insert_id);
                        $this->AddMessage('Новость "'.stripslashes($data['title']).'" успешно добавлена в базу.');
                        THistory::send('Новость <b>' . stripslashes($data['title']) . '</b> успешно добавлена в базу.');
                        $reload	= false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }

            $item_to_edit = array();
            if($reload == true){
                $item_to_edit['issue'] = intval($_POST['parent']);
                $item_to_edit['url'] = htmlspecialchars($_POST['url']);
                $item_to_edit['title'] = addslashes(htmlspecialchars($_POST['title']));
                $item_to_edit['intro'] = addslashes($_POST['intro']);
                $item_to_edit['text'] = addslashes($_POST['text']);
                $item_to_edit['author'] = htmlspecialchars($_POST['author']);
                $item_to_edit['source'] = htmlspecialchars($_POST['source']);
                $item_to_edit['link'] = htmlspecialchars($_POST['link']);
                $item_to_edit['date'] = strval($_POST['date']);
                $item_to_edit['meta_title'] = $_POST['meta_title'];
                $item_to_edit['meta_description'] = $_POST['meta_description'];
                $item_to_edit['meta_keywords'] = $_POST['meta_keywords'];
                $item_to_edit['images'] = intval($_POST['images']);
                $item_to_edit['enabled'] = strval($_POST['enabled']);
                $item_to_edit['main_list'] = strval($_POST['main_list']);
                $item_to_edit['main_top'] = strval($_POST['main_top']);
            } else {
                $item_to_edit['issue'] = intval($_POST['parent']);
                $item_to_edit['date'] = date('Y-m-d');
            }
            $item_to_edit = array_map('stripslashes', $item_to_edit);
        }

        $item_to_edit['date'] = empty($item_to_edit['date']) ? date('Y-m-d') : $item_to_edit['date'];
        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/items_save.tpl");
    }

    // ISSUES BLOCK ------------------------------------------------------------

    private function issues_show(){
        $move_id = isset($_GET['move']) || isset($_POST['move']) ? get_or_post('move') : 0;
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;

            $item_to_edit = $this->DB->sql2row("SELECT * FROM news_issues WHERE `id` = '$edit_id'");

            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $parent = intval($_POST['parent']);
                $url = trim($_POST['url']);
                $data = array();

                if(preg_match('/[^A-Za-z0-9_\-]/i', $url)){
                    $this->AddError("URL рубрики <b>'" . stripslashes($url) . "'</b> недопустимо.");
                }
                elseif($this->DB->sql2result("SELECT `id` FROM news_issues WHERE `id` <> '$edit_id' AND `url` LIKE '$url'") > 0){
                    $this->AddError("Такой URL рубрики уже существует '<b>" . stripslashes($url) . "</b>'.");
                }
                else{
                    $data['title']				= addslashes(htmlspecialchars($_POST['title']));
                    $data['url']				= htmlspecialchars(trim($_POST['url']));
                    $data['enabled']			= strval($_POST['enabled']);

                    if(TTree::update($this->DB, 'news_issues', $edit_id, $data)){
                        TTree::move($this->DB, 'news_issues', $edit_id, $parent);
                        $this->call_plugins('newsdescription', 'admin_issue_update', $edit_id);
                        $this->AddMessage('Рубрика "' . stripslashes($data['title']) . '" успешно обновлена.');
                        THistory::send('Рубрика в новостях <b>' . stripslashes($data['title']) . '</b> успешно обновлена.');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    }
                    else{
                        $this->AddError($this->DB->errors());
                    }
                }
            }

            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/issues_save.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $title = table_param('news_issues', 'title', 'id', $delete_id);

            $children = TTree::sub_ids($this->DB, 'news_issues', $delete_id);
            $children[] = $delete_id;
            $items_to_delete = $this->DB->sql2array("SELECT `id` FROM news_items WHERE `issue` IN (" . join(',', $children) . ")");
            foreach($items_to_delete as $item_delete) $this->items_delete($item_delete['id']);

            if(TTree::remove_all($this->DB, 'news_issues', $delete_id)){
                $this->call_plugins('newsdescription', 'admin_issue_delete', $delete_id);
                $this->AddMessage("Рубрика &laquo;<b>$title</b>&raquo; успешно удалена.");
                THistory::send('Рубрика в новостях <b>' . $title . '</b> успешно удалена.');
            }
            else{
                $this->AddError($this->DB->errors());
            }
        }

        if($move_id > 0){
            $neighbours = TTree::neighbours($this->DB, 'news_issues', $move_id, null, array('`sort` DESC', '`id` DESC'));
            if(strtolower(get_or_post('to')) == 'up') $neighbours = array_reverse($neighbours);

            foreach($neighbours as $key => &$neighbour){
                if($neighbour['id'] == $move_id && isset($neighbours[$key + 1])){
                    $owner = $this->DB->sql2row("SELECT * FROM news_issues WHERE `id` = '$move_id'");
                    $change = $neighbours[$key + 1];

                    if(max($owner['sort'], $change['sort']) > 0){
                        $new_owner_sort = intval($change['sort']);
                        $new_change_sort = intval($owner['sort']);

                        $query1 = "UPDATE news_issues SET `sort` = $new_owner_sort WHERE `id` = '$move_id'";
                        $query2 = "UPDATE news_issues SET `sort` = $new_change_sort WHERE `id` = '$change[id]'";

                        if($this->DB->query($query1) && $this->DB->query($query2)){
                            $title = table_param('news_issues', 'title', 'id', $move_id);
                            $this->AddMessage("Рубрика &laquo;<b>$title</b>&raquo; успешно перенесена.");
                            THistory::send('Рубрика в новостях <b>' . $title . '</b> успешно перенесена.');
                        }
                        else{
                            $this->AddError($this->DB->errors());
                        }
                    }
                    else{
                        $this->AddError("Неправильные индексы сортировки.");
                    }
                    break;
                }
            }
        }

        $items = TTree::children_all($this->DB, 'news_issues', 0, null, array('`sort` DESC', '`id` DESC'));

        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/issues_show.tpl");
    }

    private function issues_add(){
        if(isset($_POST['save']) && $_POST['save'] == 'yes'){
            $root	= intval($_POST['parent']);
            $url	= trim($_POST['url']);

            $reload	= true;
            $data	= array();

            if(preg_match('/[^A-Za-z0-9_\-]/i', $url)){
                $this->AddError("URL рубрики <b>'" . stripslashes($url) . "'</b> недопустимо.");
            }
            elseif($this->DB->num_rows($this->DB->query("SELECT `id` FROM news_issues WHERE `url` LIKE '$url'")) > 0){
                $this->AddError("Такой URL рубрики уже существует '<b>" . stripslashes($url) . "</b>'.");
            }
            else{
                $data['title']				= addslashes(htmlspecialchars($_POST['title']));
                $data['url']				= htmlspecialchars(trim($_POST['url']));
                $data['enabled']			= strval($_POST['enabled']);

                if(TTree::insert($this->DB, 'news_issues', $root, $data)){
                    $insert_id = $this->DB->insert_id();
                    $this->DB->query("UPDATE news_issues SET `sort` = $insert_id WHERE `id` = $insert_id LIMIT 1");
                    $this->call_plugins('newsdescription', 'admin_issue_add', $insert_id);
                    $this->AddMessage('Рубрика "' . $data['title'] . '" успешно добавлена в базу.');
                    THistory::send('Рубрика в новостях <b>' . $data['title'] . '</b> успешно добавлена в базу.');
                    $reload	= false;
                }
                else{
                    $this->AddError($this->DB->errors());
                }
            }

            $item_to_edit = array();
            if($reload == true){
                $item_to_edit['parent'] = intval($_POST['parent']);
                $item_to_edit['url'] = strtolower($_POST['url']);
                $item_to_edit['title'] = htmlspecialchars($_POST['title']);
                $item_to_edit['enabled'] = strval($_POST['enabled']);
            }
            else{
                $item_to_edit['parent'] = intval($_POST['parent']);
            }
            $item_to_edit = array_map('stripslashes', $item_to_edit);
        }

        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/issues_save.tpl");
    }

    private function issues_list($default = null){
        echo '<select name="parent" class="areasTable--input">';
        echo '<option value="0" class="listTitle">-- выберите рубрику --</option>';
        $items = TTree::children_all($this->DB, 'news_issues', 0, null, array('`sort` DESC', '`id` DESC'));
        foreach($items as $item){
            printf('<option value="%d" %s>%s%s</option>', $item['id'], $item['id'] == $default ? 'selected' : '', str_repeat('&nbsp;', $item['level'] * 3), $item['title']);
        }
        echo '</select>';
    }
}