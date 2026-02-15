<?php

/**
 * MODEL サンプル
 * 
 * @author kanemiya
 *
 */

class ModelUser {

	private $_user = null;
	
	/**
	 * コンストラクタ
	 *
	 */
	function __construct($user_id = null) {

		if (!$user_id)
			return false;

		// 従事者情報
		$dao_user = new DaoUser();
		$users = $dao_user->select_where_with_master(array('u.id' => $user_id));

		// 従事者資格証明書情報
		$dao_user_cert = new DaoUserCert();
		$user_certs = $dao_user_cert->select_where_with_cert(array('uc.user_id' => $user_id));

		$this->_user = $this->factory($users[0], $user_certs);

	}

	/**
	 * getter
	 *
	 */
	public function get() {
		return $this->_user;
	}

	/**
	 * setter
	 *
	 */
	public function set($user) {
		return $this->_user = $user;
	}

	/**
	 * データ整形
	 * 
	 */
	public function factory($user = null, $user_certs = null) {

		if (!$user)
			$user = $this->_user;

		if (!$user)
			return false;

		$user_class = new stdClass();
		$user_class->id = $user->id;
		$user_class->code = $user->code;
		$user_class->status_type = $user->status_type;
		$user_class->status_type_name = PARAM_CONST_USER_STATUS_TYPES[$user->status_type];
		$user_class->approve_type = $user->approve_type;
		$user_class->approve_type_name = PARAM_CONST_USER_APPROVE_TYPES[$user->approve_type];
		$user_class->name = $user->name;
		$user_class->name_kana = $user->name_kana;
		$user_class->tel = $user->tel;
		$user_class->email = $user->email;
		$user_class->post_code = $user->post_code;
		$user_class->address = $user->address;
		$user_class->gender_type = $user->gender_type;
		$user_class->gender_type_name = UtilCommon::get_gender_name($user->gender_type, $user->lang_type);
		$user_class->birth_day = $user->birth_day;
		$user_class->country_id = $user->country_id;
		$user_class->country_name = $user->country_name;
		$user_class->age = UtilCommon::get_age(
			substr($user->birth_day, 0, 4),
			substr($user->birth_day, 5, 2),
			substr($user->birth_day, 8, 2)
		);
		$user_class->lang_type = $user->lang_type;
		$user_class->lang_type_name = UtilCommon::get_lang_name($user->lang_type, $user->lang_type);
		$user_class->employ_type = $user->employ_type;
		$user_class->employ_type_name = UtilCommon::get_employ_name($user->employ_type, $user->lang_type);
		$user_class->company_id = $user->company_id;
		$user_class->company_name = $user->company_name;
		$user_class->etc_company_name = $user->etc_company_name;
		$user_class->last_login_date = $user->last_login_date;
		$user_class->note = $user->note;
		$user_class->is_temporary = 'T' == substr($user->code, 1, 1) ? true : false;
		$user_class->create_date = $user->create_date;
		$user_class->update_date = $user->update_date;
		$user_class->user_certs = null;
		if (is_array($user_certs)) {

			$user_cert_classes = null;
			$cert_short_names = array();
			foreach ($user_certs as $user_cert) {

				if ($user_cert->user_id != $user->id)
					continue;

				if (!isset($user_cert_class->cert_files[$user_cert->cert_id])) {
					$user_cert_class = new stdClass();
					$user_cert_class->cert_id = $user_cert->cert_id;
					$user_cert_class->cert_name = $user_cert->cert_name;
					$user_cert_class->cert_short_name = $user_cert->cert_short_name;
					$user_cert_class->cert_files = null;
					$cert_short_names[] = $user_cert->cert_short_name;
				}

				$cert_file_class = new stdClass();
				$cert_file_class->id = $user_cert->id;
				$cert_file_class->user_id = $user_cert->user_id;
				$cert_file_class->cert_type = $user_cert->cert_type;
				$cert_file_class->cert_type_name = PARAM_CONST_CERT_TYPES[$user_cert->cert_type];
				$cert_file_class->file_name = $user_cert->file_name;
				$cert_file_class->file_dir = CERT_UPLOAD_DIR . '/' . $user->code . '/' . $user_cert->file_name;
				$cert_file_class->file_src = CERT_SRC . '/' . $user->code . '/' . $user_cert->file_name;
				$cert_file_class->is_approved = $user_cert->is_approved;
				$cert_file_class->comment = $user_cert->comment;
				$cert_file_class->create_date = $user_cert->create_date;
				$cert_file_class->update_date = $user_cert->update_date;
				$user_cert_class->cert_files[$user_cert->cert_id][] = $cert_file_class;

				$user_class->user_certs[$user_cert->cert_id] = $user_cert_class;

			}
			$user_class->user_cert_short_names = implode(' / ', $cert_short_names);
			$gender_mark = $user->gender_type == PARAM_CONST_GENDER_TYPE_MALE ? '♂️' : '♀️';
			$user_class->assign_label = $gender_mark . '/' . $user_class->user_cert_short_names;
		}

		return $user_class;

	}

}