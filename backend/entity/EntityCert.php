<?php

/**
 * ENTITY 資格証明書マスタ
 *
 * @author kanemiya
 *
 */

class EntityCert {
	
	public $id;

	public $name;

	public $short_name;

	public $allowance;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->name = $data['name'];
		$entity->short_name = $data['short_name'];
		$entity->allowance = $data['allowance'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}