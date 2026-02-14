<?php

/**
 * ENTITY 現場
 *
 * @author kanemiya
 *
 */

class EntityLocation {
	
	public $id;

	public $name;

	public $location_date;

	public $address;

	public $detail;

	public $is_assigned;

	public $is_closed;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->name = $data['name'];
		$entity->location_date = $data['location_date'];
		$entity->address = $data['address'];
		$entity->detail = $data['detail'];
		$entity->is_assigned = $data['is_assigned'];
		$entity->is_closed = $data['is_closed'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}