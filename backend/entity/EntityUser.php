<?php

/**
 * ENTITY ユーザー
 *
 * @author kanemiya
 *
 */

class EntityUser {
	
	public $id;

	public $code;

	public $status_type;

	public $approve_type;

	public $name;

	public $name_kana;

	public $tel;

	public $email;

	public $password;

	public $post_code;

	public $address;

	public $gender_type;

	public $birth_day;

	public $country_id;

	public $employ_type;

	public $company_id;

	public $etc_company_name;

	public $lang_type;

	public $last_login_date;

	public $note;

	public $create_date;

	public $update_date;

	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->code = $data['code'];
		$entity->status_type = $data['status_type'];
		$entity->approve_type = $data['approve_type'];
		$entity->name = $data['name'];
		$entity->name_kana = $data['name_kana'];
		$entity->tel = $data['tel'];
		$entity->email = $data['email'];
		$entity->password = $data['password'];
		$entity->post_code = $data['post_code'];
		$entity->address = $data['address'];
		$entity->gender_type = $data['gender_type'];
		$entity->birth_day = $data['birth_day'];
		$entity->country_id = $data['country_id'];
		$entity->employ_type = $data['employ_type'];
		$entity->company_id = $data['company_id'];
		$entity->etc_company_name = $data['etc_company_name'];
		$entity->lang_type = $data['lang_type'];
		$entity->last_login_date = $data['last_login_date'];
		$entity->note = $data['note'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}