<?php

/**
 * ENTITY 現場シフト
 *
 * @author kanemiya
 *
 */

class EntityLocationShift {
	
	public $id;

	public $location_id;

	public $name;

	public $shift_date_from;

	public $shift_date_to;

	public $request_num;

	public $note;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->location_id = $data['location_id'];
		$entity->name = $data['name'];
		$entity->shift_date_from = $data['shift_date_from'];
		$entity->shift_date_to = $data['shift_date_to'];
		$entity->request_num = $data['request_num'];
		$entity->note = $data['note'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}