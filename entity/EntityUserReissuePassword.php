<?php

/**
 * ENTITY ユーザパスワード再設定
 *
 * @author kanemiya
 *
 */

class EntityUserReissuePassword {
	
	public $id;

	public $user_id;

	public $access_code;

	public $expire_date;

	public $is_processed;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->user_id = $data['user_id'];
		$entity->access_code = $data['access_code'];
		$entity->expire_date = $data['expire_date'];
		$entity->is_processed = $data['is_processed'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}