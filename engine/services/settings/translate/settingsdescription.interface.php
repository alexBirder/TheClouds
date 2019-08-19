<?php

interface ISettingsdescription{
	public function admin_item_form($edit_id);

	public function admin_item_add($insert_id);

	public function admin_item_update($edit_id);

	public function admin_item_delete($delete_id);

	public function transform_data($data, $id, $tolang = null);
}

?>