<?php

class TForms extends TAdmin {
    protected $module_path = '/engine/services/forms';
    protected $module_id = 'forms';
    protected $module_name = 'Динамические формы';

    function __construct($CONF, $DB){
        parent::__construct($CONF, $DB);

        $this->actions		= array(1 => 'Формы', 2 => 'Создать форму', 3 => 'Поля', 4 => 'Создать поле');
        $this->action		= ($action = get_or_post('action')) ? intval($action) : 1;
        $this->page_max 	= 25;

        $this->load_plugins($this, 'forms');
    }

    public function __destruct(){
        parent::__destruct();
    }

    public function execute(){
        switch($this->action){
            case 1:
                $this->forms_show(); break;
            case 2:
                $this->forms_show_add(); break;
            case 3:
                $this->areas_show(); break;
            case 4:
                $this->areas_show_add(); break;
        }
    }

    // ITEMS BLOCK -------------------------------------------------------------

    private function forms_show(){
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;
            $item_to_edit = $this->DB->sql2row("SELECT * FROM forms_items WHERE `id` = '$edit_id'");
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('title' => $_POST['title']);
                if ($this->NoErrors()){
                    if ($this->DB->update('forms_items', $edit_id, $data)){
                        $this->AddMessage('Форма "'.stripslashes($data['word']).'" успешно обновлена.');
                        $this->forms_attached($edit_id, $_POST['areas']);
                        THistory::send('Форма успешно обновлена.');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/html/admin/forms_show_add.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $this->DB->query("DELETE FROM `forms_items` WHERE `id` = '{$delete_id}'");
            $title = $this->DB->sql2result("SELECT `title` FROM forms_items WHERE `id` = {$delete_id}");
            $this->AddMessage("Форма успешно удалена.");
            THistory::send('Форма <b>' . $title . '</b> успешно удалена.');
        }

        $titles = $this->DB->sql2array("SELECT * FROM forms_items ORDER by `id` ASC");
        require_once(ROOT . "$this->module_path/html/admin/forms_show.tpl");
    }

    private function forms_attached($item, $areas){
        $this->DB->query("DELETE FROM `forms_attached` WHERE `form` = {$item}");
        foreach($areas as $area){
            $data = array('form' => $item, 'area' => $area);
            $this->DB->insert('forms_attached', $data);
        }
    }

    private function forms_show_add(){
        if(isset($_POST['save']) && $_POST['save'] == 'yes'){
            $reload	= true;
            $data = array('title' => $_POST['title']);
            if ($this->NoErrors()){
                if ($this->DB->insert('forms_items', $data) == true){
                    $id = $this->DB->insert_id();
                    $this->forms_attached($id, $_POST['areas']);
                    $this->AddMessage('Форма успешно добавлена в базу.');
                    THistory::send('Форма успешно добавлена в базу');
                    $reload	= false;
                } else {
                    $this->AddError($this->DB->errors());
                }
            }
            $item_to_edit = array();
            if($reload == true){
                $item_to_edit = array('title' => $_POST['title']);
            }
            $item_to_edit = array_map('stripslashes', $item_to_edit);
        }

        require_once(ROOT . "$this->module_path/html/admin/forms_show_add.tpl");
    }

    private function areas_show(){
        $move_id = isset($_GET['move']) || isset($_POST['move']) ? get_or_post('move') : 0;
        $edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
        $delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

        if($edit_id > 0){
            $return = true;
            $item_to_edit = $this->DB->sql2row("SELECT * FROM forms_areas WHERE `id` = '$edit_id'");
            if(isset($_POST['save']) && $_POST['save'] == 'yes'){
                $data = array('title' => $_POST['title'], 'name' => $_POST['name'], 'value' => $_POST['value'], 'type' => $_POST['type'], 'required' => $_POST['required'], 'enabled' => $_POST['enabled']);
                if ($this->NoErrors()){
                    if ($this->DB->update('forms_areas', $edit_id, $data)){
                        $this->AddMessage('Поле успешно обновлено.');
                        THistory::send('Поле успешно обновлено.');
                        unset($_GET['edit'], $_POST['edit']);
                        $return = false;
                    } else {
                        $this->AddError($this->DB->errors());
                    }
                }
            }
            if($return == true){
                require_once(ROOT . "$this->module_path/html/admin/areas_show_add.tpl");
                return false;
            }
        }

        if($delete_id > 0){
            $this->DB->query("DELETE FROM `forms_areas` WHERE `id` = '{$delete_id}'");
            $this->AddMessage("Поле успешно удалено.");
            THistory::send('Поле успешно удалено.');
        }

        if($move_id > 0){
            //$neighbours = $this->DB->sql2array("SELECT * FROM forms_areas WHERE `id` = {$move_id} ORDER BY `sort` DESC `id` DESC");
            $neighbours = TTree::neighbours($this->DB, 'forms_areas', $move_id, null, array('`sort` DESC', '`id` DESC'));
            if(strtolower(get_or_post('to')) == 'up') $neighbours = array_reverse($neighbours);

            foreach($neighbours as $key => $neighbour){
                if($neighbour['id'] == $move_id && isset($neighbours[$key + 1])){
                    $owner = $this->DB->sql2row("SELECT * FROM forms_areas WHERE `id` = '$move_id'");
                    $change = $neighbours[$key + 1];

                    if(max($owner['sort'], $change['sort']) > 0){
                        $new_owner_sort = intval($change['sort']);
                        $new_change_sort = intval($owner['sort']);

                        $query1 = "UPDATE forms_areas SET `sort` = $new_owner_sort WHERE `id` = '$move_id'";
                        $query2 = "UPDATE forms_areas SET `sort` = $new_change_sort WHERE `id` = '$change[id]'";

                        if($this->DB->query($query1) && $this->DB->query($query2)){
                            $title = $this->DB->sql2result("SELECT `title` FROM forms_areas WHERE `id` = {$move_id}");
                            $this->AddMessage("Поле &laquo;<b>$title</b>&raquo; успешно перенесено.");
                            THistory::send('Поле <b>' . $title . '</b> успешно перенесено.');
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

        $titles = $this->DB->sql2array("SELECT * FROM forms_areas ORDER by `sort` DESC");
        require_once(ROOT . "$this->module_path/html/admin/areas_show.tpl");
    }

    private function areas_show_add(){
        if(isset($_POST['save']) && $_POST['save'] == 'yes'){
            $reload	= true;
            $data = array('title' => $_POST['title'], 'name' => $_POST['name'], 'value' => $_POST['value'], 'type' => $_POST['type'], 'required' => $_POST['required'], 'enabled' => $_POST['enabled']);
            if ($this->NoErrors()){
                if ($this->DB->insert('forms_areas', $data) == true){
                    $insert_id = $this->DB->insert_id();
                    $this->DB->query("UPDATE forms_areas SET `sort` = $insert_id WHERE `id` = $insert_id LIMIT 1");
                    $this->AddMessage('Поле успешно добавлено в базу.');
                    THistory::send('Поле успешно добавлено в базу');
                    $reload	= false;
                } else {
                    $this->AddError($this->DB->errors());
                }
            }
            $item_to_edit = array();
            if($reload == true){
                $item_to_edit = array('title' => $_POST['title'], 'name' => $_POST['name'], 'value' => $_POST['value'], 'type' => $_POST['type'], 'required' => $_POST['required'], 'enabled' => $_POST['enabled']);
            }
            $item_to_edit = array_map('stripslashes', $item_to_edit);
        }

        require_once(ROOT . "$this->module_path/html/admin/areas_show_add.tpl");
    }

    private function areas_list($default = null){
        $items = $this->DB->sql2array("SELECT * FROM forms_areas ORDER by `id` DESC");
        foreach($items as $item){
            $title = $this->DB->sql2result("SELECT `word` FROM settings_words WHERE `id` = {$item['title']} ");
            $defaults = $this->DB->sql2result("SELECT `form` FROM `forms_attached` WHERE `area` = {$item['id']}");
            printf('<label class="forms--areas"><input type="checkbox" name="areas[]" value="%d" %s><div class="forms--areas--checkbox"></div><div class="forms--areas--title">%s</div></label>', $item['id'], $default == $defaults ? 'checked' : '', $title);
        }
    }

    private function titles_list($default = null){
        echo '<select name="title" class="areasTable--input">';
        echo '<option value="0" class="listTitle">-- выберите слово --</option>';
        $items = $this->DB->sql2array("SELECT * FROM settings_words ORDER by `id` DESC");
        foreach($items as $item){
            printf('<option value="%d" %s>%s</option>', $item['id'], $item['id'] == $default ? 'selected' : '', $item['word']);
        }
        echo '</select>';
    }

}

?>