<?php

/**
 * ENTITY 国籍マスタ
 *
 * @author kanemiya
 *
 */

class EntityCountry {
	
	public $id;

	public $name;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->name = $data['name'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}