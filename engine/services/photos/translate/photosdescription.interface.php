<?php

interface IPhotosdescription{
	public function admin_issue_form($edit_id);

	public function admin_issue_add($insert_id);

	public function admin_issue_update($edit_id);

	public function admin_issue_delete($delete_id);


	public function admin_item_form($edit_id);

	public function admin_item_add($insert_id);

	public function admin_item_update($edit_id);

	public function admin_item_delete($delete_id);


	public function transform_issue_data($data, $id, $tolang = null);

	public function transform_item_data($data, $id, $tolang = null);
}

?>