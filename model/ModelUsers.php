<?php

/**
 * MODEL ユーザ（複数用）
 * 
 * @author kanemiya
 *
 */

class ModelUsers extends ModelUser {

	private $_users = null;

	private $_user_ids = null;
	
	/**
	 * コンストラクタ
	 *
	 */
	function __construct($user_ids = null, $sorts = null) {

		if (!$user_ids)
			return false;

		// 従事者情報
		$dao_user = new DaoUser();
		$users = $dao_user->select_where_with_master(array('u.id' => $user_ids), $sorts);

		// 従事者資格証明書情報
		$dao_user_cert = new DaoUserCert();
		$user_certs = $dao_user_cert->select_where_with_cert(array('uc.user_id' => $user_ids));

		$user_datas = array();
		foreach ((array)$users as $user) {

			$user_datas[$user->id]['user'] = $user;
			$user_datas[$user->id]['user_certs'] = null;

			foreach ((array)$user_certs as $user_cert) {
				$user_datas[$user->id]['user_certs'][] = $user_cert;
			}

		}

		foreach ($user_datas as $user_id => $user_data) {
			$this->_users[$user_id] = $this->factory($user_data['user'], $user_data['user_certs']);
			$this->_user_ids[] = $user_id;
		}

	}

	/**
	 * getter
	 *
	 */
	public function gets() {
		return $this->_users;
	}

	/**
	 * getter by id
	 *
	 */
	public function get($id = null) {

		if (!$id)
			return null;

		if (!isset($this->_users[$id]))
			return false;

		return $this->_users[$id];
	}

	/**
	 * getter
	 *
	 */
	public function get_ids() {
		return $this->_user_ids;
	}
	
}