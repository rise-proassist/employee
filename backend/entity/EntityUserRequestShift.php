<?php

/**
 * ENTITY 従事者希望シフト
 *
 * @author kanemiya
 *
 */

class EntityUserRequestShift {
	
	public $id;

	public $user_id;

	public $shift_date_from;

	public $shift_date_to;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->user_id = $data['user_id'];
		$entity->shift_date_from = $data['shift_date_from'];
		$entity->shift_date_to = $data['shift_date_to'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}