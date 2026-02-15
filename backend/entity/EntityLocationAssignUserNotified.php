<?php

/**
 * ENTITY 現場アサイン従事者通知
 *
 * @author kanemiya
 *
 */

class EntityLocationAssignUserNotified {
	
	public $id;

	public $location_id;

	public $user_id;

	public $is_notified;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->location_id = $data['location_id'];
		$entity->user_id = $data['user_id'];
		$entity->is_notified = $data['is_notified'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}