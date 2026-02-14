<?php

/**
 * ENTITY 現場アサイン従事者
 *
 * @author kanemiya
 *
 */

class EntityLocationAssignUser {
	
	public $id;

	public $location_shift_id;

	public $location_shift_row;

	public $user_id;

	public $allocate_cert_ids;

	public $work_date_from;

	public $is_modify_from;

	public $work_date_to;

	public $is_modify_to;

	public $comment;

	public $receipt_user_name;

	public $receipt_user_post_code;

	public $receipt_user_address;

	public $receipt_user_tel;

	public $receipt_user_birth_day;

	public $daily_wage;

	public $withhold_tax;

	public $total_wage;

	public $is_confirmed;

	public $is_paid;

	public $paid_date;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->location_shift_id = $data['location_shift_id'];
		$entity->location_shift_row = $data['location_shift_row'];
		$entity->user_id = $data['user_id'];
		$entity->allocate_cert_ids = $data['allocate_cert_ids'];
		$entity->work_date_from = $data['work_date_from'];
		$entity->is_modify_from = $data['is_modify_from'];
		$entity->work_date_to = $data['work_date_to'];
		$entity->is_modify_to = $data['is_modify_to'];
		$entity->comment = $data['comment'];
		$entity->receipt_user_name = $data['receipt_user_name'];
		$entity->receipt_user_post_code = $data['receipt_user_post_code'];
		$entity->receipt_user_address = $data['receipt_user_address'];
		$entity->receipt_user_tel = $data['receipt_user_tel'];
		$entity->receipt_user_birth_day = $data['receipt_user_birth_day'];
		$entity->daily_wage = $data['daily_wage'];
		$entity->withhold_tax = $data['withhold_tax'];
		$entity->total_wage = $data['total_wage'];
		$entity->is_confirmed = $data['is_confirmed'];
		$entity->is_paid = $data['is_paid'];
		$entity->paid_date = $data['paid_date'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}