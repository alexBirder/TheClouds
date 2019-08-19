<?php

class TPhotos extends TAdmin{
	protected $module_path = '/engine/services/photos';
	protected $module_id = 'photos';
	protected $module_name = 'Фотографии и альбомы';

	function __construct($CONF, $DB){
		parent::__construct($CONF, $DB);

        $this->actions		= array(1 => 'Все фотографии', 2 => 'Альбомы', 3 => 'Добавить альбом');
        $this->action		= ($action = get_or_post('action')) ? intval($action) : 1;
		$this->page_max 	= 25;

		$this->add_plugin('photosdescription');
        $this->load_plugins($this, 'photos');
	}

	public function __destruct(){
		parent::__destruct();
	}

	public function execute(){
		switch($this->action){
			case 1:
				$this->items_show(); break;
			case 2:
				$this->issues_show(); break;
			case 3:
				$this->issues_add(); break;
			case 4:
				$this->items_load(); break;
			case 10:
				$this->uploadfiles(); break;
			case 11:
				$this->sortfiles(); break;
			case 12:
				$this->deletefiles(); break;
			case 13:
				$this->savetitle(); break;
		}
	}

	// AJAX BLOCK --------------------------------------------------------------

	private function items_load(){
		$issue = (int)get_or_post('issue');
		$items = $this->items_list($issue);
		TAjaxer::set_format('TEXT');
		TAjaxer::set($items);
	}

	private function uploadfiles(){
		$save_path = $this->CONF['upload_dir'] . '/photos';
		$files = TImager::load_multiple('photos', $save_path);
		$errors = array();

		foreach($files as $file){
			$image_prev = $image_full = null;

			$original_path = sprintf('%s/%s', $save_path, $file);
			$original_path_full = sprintf('%s%s/%s', ROOT, $save_path, $file);

			if(file_exists($original_path_full) && is_file($original_path_full)){
				$duplicate = TImager::duplicate($original_path, $save_path);
				if($duplicate){
					$duplicate_path = sprintf('%s/%s', $save_path, $duplicate);
					$duplicate_path_full = sprintf('%s/%s', $save_path, $duplicate);
					if(TImager::crop_resize($duplicate_path, array('width' => 270, 'height' => 270))){
						$image_prev = $duplicate;
						if(TImager::resize($original_path, array('width' => 800, 'height' => 800))){
							$image_full = $file;
						}
						else{
							unlink($duplicate_path_full);
							unlink($original_path_full);
							$errors[] = join("\n", TImager::$errors);
						}
					}
					else{
						unlink($duplicate_path_full);
						$errors[] = join("\n", TImager::$errors);
					}
				}
				else{
					$errors[] = join("\n", TImager::$errors);
				}
			}

			if($image_prev && $image_full){
				$data = array(
					'issue' => (int)get_or_post('issue'),
					'title' => '',
					'prev' => $image_prev,
					'full' => $image_full
				);
				$id = $this->DB->insert('photos_items', $data);
				if($id) $this->DB->update('photos_items', $id, array('sort' => $id));
			}
			else{
				$errors[] = join("\n", 'Внутренняя ошибка программы.');
			}
		}

		if(sizeof($errors) > 0){
			TAjaxer::set_format('TEXT');
			TAjaxer::set(join("\n", $errors));
		}
	}

	private function sortfiles(){
        $issue	= (int)get_or_post('issue');
        $order	= (array)get_or_post('galleryList');

		$items = $this->DB->sql2array("SELECT `id`, `sort` FROM `photos_items` WHERE `issue` = {$issue} ORDER BY `sort` ASC");
		if(sizeof($items) == sizeof($order)){
			for($i = 0; $i < sizeof($order); $i++){
				$sorts[$i] = array($order[$i], $items[$i]['sort']);
			}
			foreach($sorts as $pair){
				$this->DB->update('photos_items', $pair[0], array('sort' => $pair[1]));
			}
		}
	}

	private function deletefiles(){
		$save_path = $this->CONF['upload_dir'] . '/photos';
		$issue = (int)get_or_post('issue');
		$id = (int)get_or_post('id');

		$files = $this->DB->sql2row("SELECT `prev`, `full` FROM `photos_items` WHERE `id` = {$id}");
		foreach($files as $file){
			$file_path = sprintf('%s%s/%s', ROOT, $save_path, $file);
			if(file_exists($file_path) && is_file($file_path)) unlink($file_path);
			$this->DB->query("DELETE FROM `photos_items` WHERE `id` = {$id}");
		}
		$this->call_plugins('photosdescription', 'admin_item_delete', $id);

		$items = $this->items_list($issue);
		TAjaxer::set_format('TEXT');
		TAjaxer::set($items);
	}

	private function items_delete($id){
		$save_path = $this->CONF['upload_dir'] . '/photos';

		$files = $this->DB->sql2row("SELECT `prev`, `full` FROM `photos_items` WHERE `id` = {$id}");
		foreach($files as $file){
			$file_path = sprintf('%s%s/%s', ROOT, $save_path, $file);
			if(file_exists($file_path) && is_file($file_path)) unlink($file_path);
			$this->DB->query("DELETE FROM `photos_items` WHERE `id` = {$id}");
		}
		$this->call_plugins('photosdescription', 'admin_item_delete', $id);

		$items = $this->items_list($issue);
		TAjaxer::set_format('TEXT');
		TAjaxer::set($items);
	}

	private function savetitle(){
		list($lang, $id) = explode('-', get_or_post('id'));

		$text = get_or_post('text');
		$text = trim(preg_replace('/\s+/', ' ', $text));

		if($this->CONF['langs'][$lang]['main'] == 1){
			$this->DB->update('photos_items', $id, array('title' => $text));
		}
		else{
			$exists = (int)$this->DB->sql2result("SELECT COUNT(*) FROM `photos_items_translate` WHERE `id` = {$id} AND `lang` = '{$lang}'");
			if(!$exists) $this->DB->insert('photos_items_translate', array('id' => $id, 'lang' => $lang, 'title' => $text));
			else $this->DB->query("UPDATE `photos_items_translate` SET `title` = '{$text}' WHERE `id` = {$id} AND `lang` = '{$lang}'");
		}
	}

	// ITEMS BLOCK -------------------------------------------------------------

	private function items_show(){
		$issue = (int)get_or_post('issue');
		
		require_once(ROOT . "$this->module_path/js/generic.js");
		require_once(ROOT . "$this->module_path/html/admin/items_show.tpl");
	}

	private function items_list($issue){
		$result = array();
		$save_path = $this->CONF['upload_dir'] . '/photos';

		$items = $this->DB->sql2array("SELECT `id`, `title`, `prev`, `enabled` FROM `photos_items` WHERE `issue` = {$issue} ORDER BY `sort` ASC");
		foreach($items as $item){
			$inputs = array();
			foreach($this->CONF['langs'] as $k => $v){
				$title = $this->CONF['langs'][$k]['main'] == 1 ? $item['title'] : $this->DB->sql2result("SELECT `title` FROM `photos_items_translate` WHERE `id` = {$item['id']} AND `lang` = '{$k}'");
				$inputs[] = sprintf(
					'<h3>%s</h3><textarea name="title[%d]" class="gallery-photo-title" rel="%s-%d">%s</textarea>',
					$v['name'], $item['id'], $k, $item['id'], $title
				);
			}

			$result[] = sprintf(
				'<li id="galleryList_%d">
					<div class="galleryAdmin-dcont">
						<img src="%s/%s">
						<button type="button" class="galleryList--delete" onclick="galleryDeletePhoto(%d)"><i class="flaticon-forbidden-mark"></i></button>
						%s
				  	</div>
				</li>',
				$item['id'], $save_path, $item['prev'], $item['id'], join("\n", $inputs)
			);
		}

		return sprintf('<ul id="galleryList">%s</ul>', join('', $result));
	}

	// ISSUES BLOCK ------------------------------------------------------------

	private function issues_show(){
		$move_id = isset($_GET['move']) || isset($_POST['move']) ? get_or_post('move') : 0;
		$add_id = isset($_GET['add']) || isset($_POST['add']) ? get_or_post('add') : 0;
		$edit_id = isset($_GET['edit']) || isset($_POST['edit']) ? get_or_post('edit') : 0;
		$delete_id = isset($_GET['delete']) || isset($_POST['delete']) ? get_or_post('delete') : 0;

		if($edit_id > 0){
			$return = true;
            $item_to_edit = $this->DB->sql2row("SELECT * FROM photos_issues WHERE `id` = '$edit_id'");
			if(isset($_POST['save']) && $_POST['save'] == 'yes'){
				$parent = intval($_POST['parent']);
				$url = trim($_POST['url']);
		       	$data = array();

				if(preg_match('/[^A-Za-z0-9_\-]/i', $url)){
					$this->AddError("URL рубрики <b>'" . stripslashes($url) . "'</b> недопустимо.");
				} elseif($this->DB->sql2result("SELECT `id` FROM photos_issues WHERE `id` <> '$edit_id' AND `url` LIKE '$url'") > 0){
					$this->AddError("Такой URL рубрики уже существует '<b>" . stripslashes($url) . "</b>'.");
				} else {
					$data['title']				= htmlspecialchars($_POST['title']);
                    $data['text']	            = $_POST['text'];
					$data['url']				= htmlspecialchars(trim($_POST['url']));
					$data['enabled']			= $_POST['enabled'];
					$data['main']			    = $_POST['main'];
                    $data['meta_title']			= $_POST['meta_title'];
                    $data['meta_description']	= $_POST['meta_description'];
                    $data['meta_keywords']		= $_POST['meta_keywords'];

					if(TTree::update($this->DB, 'photos_issues', $edit_id, $data)){
						TTree::move($this->DB, 'photos_issues', $edit_id, $parent);
						$this->call_plugins('photosdescription', 'admin_issue_update', $edit_id);
						$this->AddMessage('Рубрика "' . stripslashes($data['title']) . '" успешно обновлена.');
						unset($_GET['edit'], $_POST['edit']);
						$return = false;
					} else {
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
			$title = $this->DB->sql2result("SELECT `title` FROM photos_issues WHERE `id` = {$delete_id}");
			$children = TTree::sub_ids($this->DB, 'photos_issues', $delete_id);
			$children[] = $delete_id;
			$items_to_delete = $this->DB->sql2array("SELECT `id` FROM photos_items WHERE `issue` IN (" . join(',', $children) . ")");
			foreach($items_to_delete as $item_delete) $this->items_delete($item_delete['id']);
			if(TTree::remove_all($this->DB, 'photos_issues', $delete_id)){
				$this->call_plugins('photosdescription', 'admin_issue_delete', $delete_id);
				$this->AddMessage("Рубрика &laquo;<b>$title</b>&raquo; успешно удалена.");
			} else {
				$this->AddError($this->DB->errors());
			}
		}

		if($move_id > 0){
			$neighbours = TTree::neighbours($this->DB, 'photos_issues', $move_id, null, array('`sort` DESC', '`id` DESC'));
			if(strtolower(get_or_post('to')) == 'up') $neighbours = array_reverse($neighbours);
			foreach($neighbours as $key => &$neighbour){
				if($neighbour['id'] == $move_id && isset($neighbours[$key + 1])){
					$owner = $this->DB->sql2row("SELECT * FROM photos_issues WHERE `id` = '$move_id'");
					$change = $neighbours[$key + 1];
					if(max($owner['sort'], $change['sort']) > 0){
						$new_owner_sort = intval($change['sort']);
						$new_change_sort = intval($owner['sort']);
						$query1 = "UPDATE photos_issues SET `sort` = $new_owner_sort WHERE `id` = '$move_id'";
						$query2 = "UPDATE photos_issues SET `sort` = $new_change_sort WHERE `id` = '$change[id]'";
						if($this->DB->query($query1) && $this->DB->query($query2)){
							$title = $this->DB->sql2result("SELECT `title` FROM photos_issues WHERE `id` = {$move_id}");
							$this->AddMessage("Рубрика &laquo;<b>$title</b>&raquo; успешно перенесена.");
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

        $items = TTree::children_all($this->DB, 'photos_issues', 0, null, array('`sort` DESC', '`id` DESC'));

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
			} elseif($this->DB->num_rows($this->DB->query("SELECT `id` FROM photos_issues WHERE `url` LIKE '$url'")) > 0){
				$this->AddError("Такой URL рубрики уже существует '<b>" . stripslashes($url) . "</b>'.");
			} else {
				$data['title']				= htmlspecialchars($_POST['title']);
                $data['text']	            = $_POST['text'];
				$data['url']				= htmlspecialchars(trim($_POST['url']));
				$data['enabled']			= $_POST['enabled'];
				$data['main']			    = $_POST['main'];
                $data['meta_title']			= $_POST['meta_title'];
                $data['meta_description']	= $_POST['meta_description'];
                $data['meta_keywords']		= $_POST['meta_keywords'];

				if(TTree::insert($this->DB, 'photos_issues', $root, $data)){
					$insert_id = $this->DB->insert_id();
					$this->DB->query("UPDATE photos_issues SET `sort` = $insert_id WHERE `id` = $insert_id LIMIT 1");
					$this->call_plugins('photosdescription', 'admin_issue_add', $insert_id);
					$this->AddMessage('Рубрика "' . $data['title'] . '" успешно добавлена в базу.');
					$reload	= false;
				} else {
					$this->AddError($this->DB->errors());
				}
			}

			$item_to_edit = array();
			if($reload == true){
				$item_to_edit['parent'] = intval($_POST['parent']);
				$item_to_edit['url'] = strtolower($_POST['url']);
				$item_to_edit['title'] = htmlspecialchars($_POST['title']);
				$item_to_edit['text'] = $_POST['text'];
				$item_to_edit['enabled'] = $_POST['enabled'];
				$item_to_edit['main'] = $_POST['main'];
                $item_to_edit['meta_title']	= $_POST['meta_title'];
                $item_to_edit['meta_description'] = $_POST['meta_description'];
                $item_to_edit['meta_keywords'] = $_POST['meta_keywords'];
			} else {
				$item_to_edit['parent'] = intval($_POST['parent']);
			}
			$item_to_edit = array_map('stripslashes', $item_to_edit);
		}		

		require_once(ROOT . "$this->module_path/js/generic.js");
		require_once(ROOT . "$this->module_path/html/admin/issues_save.tpl");
	}

	private function issues_list_photo($default = 0){
		print('<select name="issue" class="selectItem" onchange="this.form.submit();">');
		print('<option value="0" class="listTitle">-- выберите из списка --</option>');

        $items = TTree::children_all($this->DB, 'photos_issues', 0, null, array('`sort` DESC', '`id` DESC'));
		foreach($items as $item){
			printf('<option value="%d" %s>%s%s</option>', $item['id'], $item['id'] == $default ? 'selected' : '', str_repeat('&nbsp;', $item['level'] * 3), $item['title']);
		}

		print('</select>');
	}

	private function issues_list($default = null){
		echo '<select name="parent" class="selectItem">';
		echo '<option value="0" class="listTitle">-- выберите альбом --</option>';
        $items = TTree::children_all($this->DB, 'photos_issues', 0, null, array('`sort` DESC', '`id` DESC'));
		foreach($items as $item){
			printf('<option value="%d" %s>%s%s</option>', $item['id'], $item['id'] == $default ? 'selected' : '', str_repeat('&nbsp;', $item['level'] * 3), $item['title']);
		}
		echo '</select>';
	}
}

?>