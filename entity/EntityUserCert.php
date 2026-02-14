<?php

/**
 * ENTITY 従事者資格証明書マスタ
 *
 * @author kanemiya
 *
 */

class EntityUserCert {
	
	public $id;

	public $user_id;

	public $cert_id;

	public $cert_type;

	public $file_name;

	public $is_approved;

	public $comment;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->user_id = $data['user_id'];
		$entity->cert_id = $data['cert_id'];
		$entity->cert_type = $data['cert_type'];
		$entity->file_name = $data['file_name'];
		$entity->is_approved = $data['is_approved'];
		$entity->comment = $data['comment'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}