<?php

class TAdwords extends TAdmin {
	protected $module_path = '/engine/services/adwords';
	protected $module_id = 'adwords';
	protected $module_name = 'Рекламные баннеры';

	protected $types;

	function __construct($CONF, $DB){
		parent::__construct($CONF, $DB);

        $this->actions		= array(1 => 'Все баннеры', 2 => 'Добавить баннер');
        $this->action		= ($action = get_or_post('action')) ? intval($action) : 1;
		$this->page_max 	= 20;

		$this->add_plugin('adwordsdescription');
        $this->load_plugins($this, 'adwords');
	}

	public function __destruct(){
		parent::__destruct();
	}

	public function execute(){
		switch($this->action){
			case 1:
				$this->files_show(); break;
			case 2:
				$this->files_add(); break;
		}
	}

	// FILES BLOCK -------------------------------------------------------------

	private function files_show(){
		$move_id = isset($_GET['move']) || isset($_POST['move']) ? get_or_post('move') : 0;
		$edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
		$delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

		if($edit_id > 0){
			$return = true;

            $item_to_edit = $this->DB->sql2row("SELECT * FROM adwords_items WHERE `id` = '$edit_id'");

			if(isset($_POST['save']) && $_POST['save'] == 'yes'){
				$data['title'] = htmlspecialchars($_POST['title']);
				$data['intro'] = $_POST['intro'];
				$data['url'] = $_POST['url'];
				$data['position'] = strval($_POST['position']);
				$data['enabled'] = strval($_POST['enabled']);

                if(isset($_FILES['image']) && is_uploaded_file($_FILES['image']["tmp_name"])){
                    $save_path = $this->CONF['upload_dir'] . '/banners';
                    $image_big = TImager::load('image', $save_path);
                    $data['image'] = $image_big;
                }

				if ($this->DB->update('adwords_items', $edit_id, $data)) {
					$this->call_plugins('adwordsdescription', 'admin_item_update', $edit_id);
					$this->AddMessage('Баннер "' . stripslashes($data['title']) . '" успешно обновлен.');
                    THistory::send('Баннер <b>' . stripslashes($data['title']) . '</b> успешно обновлен.');
					unset($_GET['edit'], $_POST['edit'], $_REQUEST['edit']);
					$return = false;
				} else {
					$this->AddError($this->DB->errors());
				}
			}

			if($return == true){
				require_once(ROOT . "$this->module_path/js/generic.js");
				require_once(ROOT . "$this->module_path/html/admin/items_save.tpl");
				return false;
			}
		}
		
		if($delete_id > 0){
			$title = $this->DB->sql2result("SELECT `title` FROM `adwords_items` WHERE `id` = {$delete_id}");
			$this->deleteFile($delete_id);
			$this->AddMessage("Файл &laquo;<b>$title</b>&raquo; успешно удален.");
            THistory::send('Баннер <b>' . $title . '</b> успешно удален.');
		}

		if($move_id > 0){
			$neighbours = $this->DB->sql2array("SELECT `id`, `sort` FROM `adwords_items` ORDER BY `sort` DESC");
			if(strtolower(get_or_post('to')) == 'up') $neighbours = array_reverse($neighbours);

			foreach($neighbours as $key => &$neighbour){
				if($neighbour['id'] == $move_id && isset($neighbours[$key + 1])){
					$owner = $this->DB->sql2row("SELECT `sort` FROM `adwords_items` WHERE `id` = '{$move_id}'");
					$change = $neighbours[$key + 1];

					if(max($owner['sort'], $change['sort']) > 0){
						$new_owner_sort = intval($change['sort']);
						$new_change_sort = intval($owner['sort']);

						$query1 = "UPDATE `adwords_items` SET `sort` = {$new_owner_sort} WHERE `id` = '{$move_id}'";
						$query2 = "UPDATE `adwords_items` SET `sort` = {$new_change_sort} WHERE `id` = '{$change['id']}'";

						if($this->DB->query($query1) && $this->DB->query($query2)){
							$title = $this->DB->sql2result("SELECT `title` FROM `adwords_items` WHERE `id` = {$move_id}");
							$this->AddMessage("Файл &laquo;<b>$title</b>&raquo; успешно перенесен.");
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

		$sql = "SELECT i.* FROM `adwords_items` AS i ORDER BY i.`sort` DESC";
		$items = $this->DB->sql2array($sql);

		require_once(ROOT . "$this->module_path/js/generic.js");
		require_once(ROOT . "$this->module_path/html/admin/items_show.tpl");
	}

	private function deleteFile($delete_id){
		$this->removeFile($delete_id);
		$this->DB->query("DELETE FROM `adwords_items` WHERE `id` = {$delete_id}");
		$this->call_plugins('adwordsdescription', 'admin_item_delete', $delete_id);
	}

	private function removeFile($delete_id){
		$file = $this->DB->sql2result("SELECT `image` FROM `adwords_items` WHERE `id` = {$delete_id}");
		$path = ROOT . $this->CONF['upload_dir'] . '/banners/' . $file;
		if(is_file($path)) unlink($path);
		$this->DB->query("UPDATE `adwords_items` SET `image` = null WHERE `id` = {$delete_id}");
	}

	private function files_add(){
		if(isset($_POST['save']) && $_POST['save'] == 'yes'){
			$reload	= true;

			$data['title']		= htmlspecialchars($_POST['title']);
			$data['intro']		= $_POST['intro'];
			$data['url']        = $_POST['url'];
			$data['position']	= strval($_POST['position']);

            if(isset($_FILES['image']) && is_uploaded_file($_FILES['image']["tmp_name"])){
                $save_path = $this->CONF['upload_dir'] . '/banners';
                $image_big = TImager::load('image', $save_path);
                $data['image'] = $image_big;
            }

			if($this->DB->insert('adwords_items', $data)){
				$insert_id = $this->DB->insert_id();
				$this->DB->query("UPDATE adwords_items SET `sort` = {$insert_id} WHERE `id` = {$insert_id}");
				$this->call_plugins('adwordsdescription', 'admin_item_add', $insert_id);
				$this->AddMessage('Баннер "' . $data['title'] . '" успешно добавлен в базу.');
                THistory::send('Баннер <b>' . $data['title'] . '</b> успешно добавлен в базу.');
				$reload	= false;
			}
			else{
				$this->AddError($this->DB->errors());
			}

			$item_to_edit = array();
			if($reload == true){
				$item_to_edit['title'] = htmlspecialchars($_POST['title']);
				$item_to_edit['intro'] = $_POST['intro'];
				$item_to_edit['url'] = $_POST['url'];
			}
			$item_to_edit = array_map('stripslashes', $item_to_edit);
		}		

		require_once(ROOT . "$this->module_path/js/generic.js");
		require_once(ROOT . "$this->module_path/html/admin/items_save.tpl");
	}
}

?>