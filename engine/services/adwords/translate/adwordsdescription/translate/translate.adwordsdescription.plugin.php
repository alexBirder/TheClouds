<?php

class PAdwordstranslate implements IGeneric, IAdwordsdescription{
	private $parent;
	private $path;

	private $CONF;
	private $DB;
	private $MID;
	private $TPL;

	public function __construct($obj, $CONF, $DB, $MID = null, $TPL = null){
		$this->parent = $obj;
		
		$this->CONF = $CONF;
		$this->DB = $DB;
		$this->MID = $MID;
		$this->TPL = $TPL;

		$this->path = "/engine/services/adwords/translate/adwordsdescription/translate";
	}

	public function __destruct(){
	}

	public function process(){
	}

	public function execute(){
	}

	public function executable(){
		return true;
	}

    public function admin_item_buttons($edit_id){
        require_once(ROOT . "$this->path/html/admin_files_buttons.html");
    }

	public function admin_item_form($edit_id){
		require_once(ROOT . "$this->path/html/admin_files_save.html");
	}

	public function admin_item_add($insert_id){
		if($_POST['plugin_translate'])foreach($_POST['plugin_translate'] as $lang => $POST){
			if(isset($_POST['plugin_translate'][$lang]['intro']) && !empty($_POST['plugin_translate'][$lang]['intro'])){
		       	$data = array();

				$data['id']		= intval($insert_id);
				$data['lang']	= strval($lang);
				$data['intro']	= $POST['intro'];
				$data['url']	= $POST['url'];

				$keys	= array_keys($data);
				$vals	= array_values($data);
	           	$query	= sprintf("INSERT INTO adwords_items_translate (`%s`) VALUES ('%s')", join($keys, "`,`"), join($vals, "','"));

				if ($this->DB->query($query) == false){
					$this->parent->AddError($this->DB->last_error());
				}
			}
		}
	}

	public function admin_item_update($edit_id){
		if($_POST['plugin_translate'])foreach($_POST['plugin_translate'] as $lang => $POST){
			if(isset($_POST['plugin_translate'][$lang]['intro']) && !empty($_POST['plugin_translate'][$lang]['intro'])){
		       	$data = array();

				$data['id']		= intval($edit_id);
				$data['lang']	= strval($lang);
				$data['intro']	= $POST['intro'];
				$data['url']	= $POST['url'];

				if($this->DB->sql2result("SELECT COUNT(*) FROM adwords_items_translate WHERE `id` = '$edit_id' AND `lang` LIKE '$lang'") > 0){
					unset($data['id'], $data['lang']);
					$set = array();
					foreach($data as $key=>$val)
						$set[] = sprintf("`%s` = '%s'", $key, $val);
					$set = join(', ', $set);
					$query	= sprintf("UPDATE adwords_items_translate SET %s WHERE `id` = '%d' AND `lang` LIKE '%s'", $set, $edit_id, $lang);
				} else {
					$keys	= array_keys($data);
					$vals	= array_values($data);
		           	$query	= sprintf("INSERT INTO adwords_items_translate (`%s`) VALUES ('%s')", join($keys, "`,`"), join($vals, "','"));
				}

				if ($this->DB->query($query) == false){
					$this->parent->AddError($this->DB->last_error());
				}
			} elseif($this->DB->sql2result("SELECT COUNT(*) FROM adwords_items_translate WHERE `id` = '$edit_id' AND `lang` LIKE '$lang'") > 0){
				$query	= sprintf("DELETE FROM adwords_items_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $edit_id, $lang);
				if ($this->DB->query($query) == false){
					$this->parent->AddError($this->DB->last_error());
				}			
			}
		}
	}

	public function admin_item_delete($delete_id){
		foreach($this->CONF['langs'] as $lang => $language){
			if($this->DB->sql2result("SELECT COUNT(*) FROM adwords_items_translate WHERE `id` = '$delete_id' AND `lang` LIKE '$lang'") > 0){
				$file = $this->DB->sql2result("SELECT `file` FROM `adwords_items_translate` WHERE `id` = '$delete_id' AND `lang` LIKE '$lang'");
				$path = ROOT . $this->CONF['upload_dir'] . '/adwords/' . $file;
				if(is_file($path)) unlink($path);
				$this->DB->query("UPDATE `adwords_items_translate` SET `file` = null WHERE `id` = '$delete_id' AND `lang` LIKE '$lang'");

				$query	= sprintf("DELETE FROM adwords_items_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $delete_id, $lang);
				if ($this->DB->query($query) == false){
					$this->parent->AddError($this->DB->last_error());
				}							
			}
		}
	}

	public function transform_item_data($data, $id, $tolang = null){
		$lang = $tolang === null ? $GLOBALS['lang'] : trim($tolang);
		$pattern = array('url', 'intro');
		
		if($this->CONF['langs'][$lang]['main'] == 0){
			$keys = join("`, `", array_intersect($pattern, array_keys($data)));
			$sql = sprintf("SELECT `%s` FROM adwords_items_translate WHERE `id` = '%d' AND `lang` LIKE '%s'", $keys, $id, $lang);
			$data = array_replace($data, $this->DB->sql2row($sql));
		}
	}
}

?>