<?php

class TSettings extends TAdmin {
    protected $module_path = '/engine/services/settings';
    protected $module_id = 'settings';
    protected $module_name = 'Настройки';

    private $USER_ID;

    function __construct($CONF, $DB){
        parent::__construct($CONF, $DB);

        $this->actions		= array(9 => 'Основное меню', 3 => 'Настройки', 8 => 'Переводы и данные', 6 => 'МЕТА данные', 1 => 'Модераторы', 2 => 'История');
        $this->action		= ($action = get_or_post('action')) ? intval($action) : 1;
        $this->page_max 	= 25;

        $this->add_plugin('settingsdescription');
        $this->load_plugins($this, 'settings');
    }

    public function __destruct(){
        parent::__destruct();
    }

    public function execute(){
        switch($this->action){
            case 1:
                $this->settings_users(); break;
            case 2:
                $this->settings_history(); break;
            case 3:
                $this->settings_config(); break;
            case 4:
                $this->settings_insert(); break;
            case 6:
                $this->settings_titles_show(); break;
            case 7:
                $this->settings_titles_add(); break;
            case 8:
                $this->settings_words_show(); break;
            case 9:
                $this->settings_menu(); break;
        }
    }

    public function public_uid(){
        return (int)$this->USER_ID;
    }

    // ITEMS BLOCK -------------------------------------------------------------

    private function settings_users(){
        $add_id = isset($_GET['add']) || isset($_POST['edit']) ? get_or_post('add') : 0;
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;
            $item_to_edit = $this->DB->sql2row("SELECT * FROM settings_users WHERE `id` = {$edit_id}");
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('name' => $_POST['name'], 'login' => $_POST['login_admin'], 'password' => $_POST['password_admin'], 'services' => implode('|', $_POST['services']), 'enabled' => strval($_POST['enabled']));
                if ($this->NoErrors()){
                    if ($this->DB->update('settings_users', $edit_id, $data)){
                        $this->AddMessage('Пользователь "'.stripslashes($data['name']).'" успешно обновлена');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/settings_users_add.tpl");
                return false;
            }
        }

        if($add_id > 0){
            $return	= true;
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('name' => $_POST['name'], 'login' => $_POST['login_admin'], 'password' => $_POST['password_admin'], 'services' => $_POST['services'], 'enabled' => strval($_POST['enabled']));
                if ($this->NoErrors()){
                    if($this->DB->insert('settings_users', $data)){
                        $this->AddMessage('Пользователь успешно добавлен в базу');
                    } else{
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/settings_users_add.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $title = $this->DB->sql2result("SELECT `name` FROM settings_users WHERE `id` = {$delete_id}");
            if(TTree::remove_all($this->DB, 'settings_users', $delete_id)){
                $this->AddMessage("Пользователь &laquo;<b>$title</b>&raquo; успешно удален.");
            } else {
                $this->AddError($this->DB->errors());
            }
        }

        $users = $this->DB->sql2array("SELECT * FROM settings_users WHERE `enabled` = 'y'");
        require_once(ROOT . "$this->module_path/html/admin/settings_users.tpl");
    }

    private function modules_list($default = null){
        global $MODS;
        foreach($MODS as $value) {
            printf('<label><input type="checkbox" name="services[]" value="%s" %s><div class="panelBoxes--box"></div><div class="panelBoxes--title">%s</div></label>', $value['path'], $current == $value['path'] ? 'checked' : '', $value['name']);
        }
    }

    private function settings_history(){
        $items = $this->DB->sql2array("SELECT * FROM settings_history ORDER by `date` DESC");
        require_once(ROOT . "$this->module_path/html/admin/settings_history.tpl");

        $total = $this->DB->sql2result("SELECT COUNT(*) FROM `settings_history`");
        $this->PrintPages($total, $this->page_max);
    }

    public function settings_menu_add($parent, $id){
        $menu = array('id' => $id, 'title' => $_POST['title'], 'url' => '/page/' . $_POST['url'] . '.html', 'enabled' => strval($_POST['enabled']));
        if(TTree::insert($this->DB, 'settings_menu', $parent, $menu)){
            $this->DB->query("UPDATE settings_menu SET `sort` = {$id} WHERE `id` = {$id} LIMIT 1");
            $this->call_plugins('settingsdescription', 'admin_menu_add', $id);
        } else{
            $this->AddError($this->DB->errors());
        }
    }

    private function settings_menu(){
        $add_id = isset($_GET['add']) || isset($_POST['edit']) ? get_or_post('add') : 0;
        $move_id = isset($_GET['move']) || isset($_POST['move']) ? get_or_post('move') : 0;
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;
            $item_to_edit = $this->DB->sql2row("SELECT * FROM settings_menu WHERE `id` = {$edit_id}");
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $parent = intval($_POST['parent']);
                $data = array('title' => $_POST['title'], 'url' => $_POST['url'], 'enabled' => strval($_POST['enabled']));
                if ($this->NoErrors()){
                    if ($this->DB->update('settings_menu', $edit_id, $data)){
                        TTree::move($this->DB, 'settings_menu', $edit_id, $parent);
                        $this->AddMessage('Ссылка "'.stripslashes($data['title']).'" успешно обновлена.');
                        $this->call_plugins('settingsdescription', 'admin_menu_update', $edit_id);
                        THistory::send('Ссылка в основном меню успешно обновлена.');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/settings_menu_add.tpl");
                return false;
            }
        }

        if($add_id > 0){
            $root	= intval($_POST['parent']);
            $return	= true;
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('title' => $_POST['title'], 'url' => $_POST['url'], 'enabled' => strval($_POST['enabled']));
                if ($this->NoErrors()){
                    if(TTree::insert($this->DB, 'settings_menu', $root, $data)){
                        $insert_id = $this->DB->insert_id();
                        $this->DB->query("UPDATE settings_menu SET `sort` = $insert_id WHERE `id` = $insert_id LIMIT 1");
                        $this->AddMessage('Ссылка успешно добавлено в базу.');
                        $this->call_plugins('settingsdescription', 'admin_menu_add', $insert_id);
                        THistory::send('Добавлена ссылка в основное меню');
                    } else{
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/settings_menu_add.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $title = $this->DB->sql2result("SELECT `title` FROM settings_menu WHERE `id` = {$delete_id}");
            if(TTree::remove_all($this->DB, 'settings_menu', $delete_id)){
                $this->AddMessage("Ссылка &laquo;<b>$title</b>&raquo; успешно удалена.");
            } else {
                $this->AddError($this->DB->errors());
            }
        }

        if($move_id > 0){
            $neighbours = TTree::neighbours($this->DB, 'settings_menu', $move_id, null, array('`sort` ASC', '`id` ASC'));
            if(strtolower(get_or_post('to')) == 'up') $neighbours = array_reverse($neighbours);
            foreach($neighbours as $key => &$neighbour){
                if($neighbour['id'] == $move_id && isset($neighbours[$key + 1])){
                    $owner = $this->DB->sql2row("SELECT * FROM settings_menu WHERE `id` = '$move_id'");
                    $change = $neighbours[$key + 1];
                    if(max($owner['sort'], $change['sort']) > 0){
                        $new_owner_sort = intval($change['sort']);
                        $new_change_sort = intval($owner['sort']);
                        $query1 = "UPDATE settings_menu SET `sort` = $new_owner_sort WHERE `id` = '$move_id'";
                        $query2 = "UPDATE settings_menu SET `sort` = $new_change_sort WHERE `id` = '$change[id]'";
                        if($this->DB->query($query1) && $this->DB->query($query2)){
                            $this->AddMessage("Ссылка успешно перенесена.");
                            THistory::send("Страница <b>".$title."</b> успешно перенесена.");
                        } else {
                            $this->AddError($this->DB->errors());
                        }
                    } else {
                        $this->AddError("Неправильные индексы сортировки");
                    }
                    break;
                }
            }
        }

        $items = TTree::children_all($this->DB, 'settings_menu', 0, null, array('`sort` ASC', '`id` ASC'));
        require_once(ROOT . "$this->module_path/html/admin/settings_menu.tpl");
    }

    private function settings_menu_list($default = null){
        echo '<select name="parent" class="areasTable--input">';
        echo '<option value="0" class="listTitle">-- в корень --</option>';
        $items = TTree::children_all($this->DB, 'settings_menu', 0, null, array('`sort` ASC', '`id` ASC'));
        foreach($items as $item){
            printf('<option value="%d" %s>%s%s</option>', $item['id'], $item['id'] == $default ? 'selected' : '', str_repeat('&nbsp;', $item['level'] * 3), $item['title']);
        }
        echo '</select>';
    }

    private function settings_words_show(){
        $add_id = isset($_GET['add']) || isset($_POST['edit']) ? get_or_post('add') : 0;
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;
            $item_to_edit = $this->DB->sql2row("SELECT * FROM settings_words WHERE `id` = {$edit_id}");
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('word' => addslashes($_POST['word']));
                if ($this->NoErrors()){
                    if ($this->DB->update('settings_words', $edit_id, $data)){
                        $this->AddMessage('Слово "'.stripslashes($data['word']).'" успешно обновлено.');
                        $this->call_plugins('settingsdescription', 'admin_item_update', $edit_id);
                        THistory::send('Слово успешно обновлено.');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/settings_translate_add.tpl");
                return false;
            }
        }

        if($add_id > 0){
            $return	= true;
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('word' => addslashes($_POST['word']));
                if ($this->NoErrors()){
                    if ($this->DB->insert('settings_words', $data) == true){
                        $insert_id = $this->DB->insert_id();
                        $this->AddMessage('Слово успешно добавлено в базу.');
                        $this->call_plugins('settingsdescription', 'admin_item_add', $insert_id);
                        THistory::send('Успешно добавлено новое слово в переводы');
                        $return = false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/settings_translate_add.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $this->DB->query("DELETE FROM `settings_words` WHERE `id` = '{$delete_id}'");
            $title = table_param('settings_words', 'title', 'id', $delete_id);
            $this->call_plugins('settingsdescription', 'admin_item_delete', $delete_id);
            $this->AddMessage("Слово успешно удалено.");
            THistory::send('Новость <b>' . $title . '</b> успешно удалена.');
        }

        $titles = $this->DB->sql2array("SELECT * FROM settings_words ORDER by `id` ASC");
        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/settings_translate.tpl");
    }

    private function settings_config(){

        if((isset($_POST['save']) && $_POST['save'] == 'yes' && ADMIN_MODE == true)){
            $data = array(
                "project_scripts" => addslashes($_POST['project_scripts']),
                "project_name" => $_POST['project_name'],
                "project_url" => $_POST['project_url'],
                "project_bread" => $_POST['project_bread'],
                "project_email" => $_POST['project_email'],
                "project_email_reply" => $_POST['project_email_reply'],
                "project_status" => $_POST['project_status'],
                "project_recaptcha" => $_POST['project_recaptcha'],
                "policy_socials" => $_POST['policy_socials'],
                "policy_cookie" => $_POST['policy_cookie'],
                "policy_confidence" => $_POST['policy_confidence'],
                "change_menu" => $_POST['change_menu'],
                "change_adblock" => $_POST['change_adblock'],
                "change_template" => $_POST['change_template'],
                "change_gzip" => $_POST['change_gzip'],
                "change_minify" => $_POST['change_minify']
            );

            if(isset($_FILES['project_favicon']) && is_uploaded_file($_FILES['project_favicon']["tmp_name"])){
                $save_path = $this->CONF['upload_dir'] . '/favicons';
                $favicon = TImager::load('project_favicon', $save_path);
                $data['project_favicon'] = $favicon;
            }

            if ($this->DB->update('settings_project', '1', $data)){
                $this->AddMessage('Настройки успешно обновлены');
                THistory::send('Были обновлены настройки сайта');
            } else {
                $this->AddError($this->DB->errors());
            }
        }

        $result = $this->DB->sql2row("SELECT * FROM settings_project WHERE `id` = '1'");
        $project = array(
            "project_scripts" => stripslashes($result['project_scripts']),
            "project_name" => $result['project_name'],
            "project_url" => $result['project_url'],
            "project_bread" => $result['project_bread'],
            "project_favicon" => $result['project_favicon'],
            "project_email" => $result['project_email'],
            "project_email_reply" => $result['project_email_reply'],
            "project_status" => $result['project_status'],
            "project_recaptcha" => $result['project_recaptcha'],
            "policy_socials" => $result['policy_socials'],
            "policy_cookie" => $result['policy_cookie'],
            "policy_confidence" => $result['policy_confidence'],
            "change_menu" => $result['change_menu'],
            "change_adblock" => $result['change_adblock'],
            "change_template" => $result['change_template'],
            "change_gzip" => $result['change_gzip'],
            "change_minify" => $result['change_minify']
        );

        require_once(ROOT . "$this->module_path/html/admin/settings_config.tpl");
    }

    private function settings_titles_show(){
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;
            $item_to_edit = $this->DB->sql2row("SELECT * FROM settings_titles WHERE `id` = '$edit_id'");
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('title' => $_POST['title'], 'description' => $_POST['description'], 'keywords' => $_POST['keywords'], 'lang' => $_POST['lang']);
                if ($this->NoErrors()){
                    if ($this->DB->update('settings_titles', $edit_id, $data)){
                        $this->AddMessage('Данные "'.stripslashes($data['title']).'" успешно обновлены.');
                        THistory::send('МЕТА данные успешно обновлены.');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/js/generic.js");
                require_once(ROOT . "$this->module_path/html/admin/settings_titles_add.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $this->DB->query("DELETE FROM `settings_titles` WHERE `id` = '{$delete_id}'");
            $title = table_param('settings_titles', 'title', 'id', $delete_id);
            $this->AddMessage("МЕТА успешно удалены.");
            THistory::send('Новость <b>' . $title . '</b> успешно удалена.');
        }

        $titles = $this->DB->sql2array("SELECT * FROM settings_titles ORDER by `id` DESC");
        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/settings_titles.tpl");
    }

    private function settings_titles_add(){
        if(isset($_POST['save']) && $_POST['save'] == 'yes'){
            $reload	= true;
            $data = array('title' => $_POST['title'], 'description' => $_POST['description'], 'keywords' => $_POST['keywords'], 'lang' => $_POST['lang']);
            if ($this->NoErrors()){
                if ($this->DB->insert('settings_titles', $data) == true){
                    $this->DB->insert_id();
                    $this->AddMessage('МЕТА заголовки успешно добавлены в базу.');
                    THistory::send('Были добавлены МЕТА заголовки');
                    $reload	= false;
                } else {
                    $this->AddError($this->DB->errors());
                }
            }
            $item_to_edit = array();
            if($reload == true){
                $item_to_edit = array('title' => $_POST['title'], 'description' => $_POST['description'], 'keywords' => $_POST['keywords'], 'lang' => $_POST['lang']);
            }
            $item_to_edit = array_map('stripslashes', $item_to_edit);
        }

        require_once(ROOT . "$this->module_path/js/generic.js");
        require_once(ROOT . "$this->module_path/html/admin/settings_titles_add.tpl");
    }

    private function lang_list($default = null){
        echo '<select name="lang" class="areasTable--input">';
        echo '<option value="0" class="listTitle">-- выберите язык --</option>';
        $langs = $this->CONF['langs'];
        foreach($langs as $lang => $key){
            printf('<option value="%s" %s>%s</option>', $lang, $lang == $default ? 'selected' : '', $key['name']);
        }
        echo '</select>';
    }

    private function settings_insert(){
        global $MODS;

        foreach ($MODS as $service){
            $path = ROOT . $this->CONF['services_dir'] . '/' . strtolower($service['path']) . '/mysql/tables.sql';
            if(file_exists($path) && is_file($path)){
                $query = file_get_contents($path);
                $q_arr = explode(';', $query);
                foreach ($q_arr as $query){
                    $q = preg_replace('/[[:space:]]+/', ' ', trim($query));
                    if (empty($q)) continue;
                    $q = trim($q);
                    printf('<table width="100%%"><tr><td style="background: #f1f1f1; border-radius: 4px;"><pre style="margin: 0; padding: 15px; line-height: 18px;">%s</pre></td></tr><tr>', trim($query));
                    if ($this->DB->query($q))
                        print('<td style="padding: 15px; background: #45c7a4; box-shadow: 0 3px 5px rgba(51,182,146,0.2); border-radius: 4px; color: #fff;">Успешно выполненно.</td>');
                    else
                        print('<td style="padding: 15px; background: #d54949; box-shadow: 0 3px 5px rgba(51,182,146,0.2); border-radius: 4px; color: #fff;">Ошибка: '.$this->DB->error().'.</td>');
                    print('	</tr></table><br />');
                }
            }
        }

        require_once(ROOT . "$this->module_path/html/admin/settings_insert.tpl");
    }

}

?>