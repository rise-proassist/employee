<?php

/**
 * MODEL ユーザー検索
 * @author kanemiya
 *
 */

class ModelUserSearch {

	private $_users = null;

	private $_code = null;

	private $_name = null;

	private $_name_kana = null;

	private $_email = null;

	private $_tel = null;

	private $_status_type = null;

	private $_approve_type = null;

	private $_sort_key = null;

	private $_sort_type = null;

	private $_limit = null;

	private $_offset = null;

	private $_num = 0;

	private $_is_all = false;

	/**
	 * コンストラクタ
	 *
	 */
	function __construct() {}

	/**
	 * 検索
	 * 
	 */
	public function search() {

		// 検索条件
		$wheres = array();

		// 検索条件 : コード
		if ($this->_code)
			$wheres['u.code'] = $this->_code;

		// 検索条件 : 氏名
		if ($this->_name)
			$wheres['u.name'] = $this->_name;

		// 検索条件 : 氏名（ふりがな）
		if ($this->_name)
			$wheres['u.name_kana'] = $this->_name_kana;

		// 検索条件 : メールアドレス
		if ($this->_email)
			$wheres['u.email'] = $this->_email;

		// 検索条件 : 電話番号
		if ($this->_tel)
			$wheres['u.tel'] = $this->_tel;

		// 検索条件 : ステータス種別
		if ($this->_status_type)
			$wheres['u.status_type'] = $this->_status_type;

		// 検索条件 : 承認種別
		if ($this->_approve_type)
			$wheres['u.approve_type'] = $this->_approve_type;

		// 検索条件 : ???
		// if ($this->_gender_id)
		// 	$wheres['gender_id'] = $this->_gender_id;	

		// ソート種別
		$sorts = null;
		if ($this->_sort_key && $this->_sort_type) {

			switch ($this->_sort_type) {

				case PARAM_CONST_LIST_SORT_DESC:
					$sorts[$this->_sort_key] = 'DESC';
					break;

				case PARAM_CONST_LIST_SORT_ASC:
				default:
					$sorts[$this->_sort_key] = 'ASC';
					break;

			}

		}

		// $sorts = array();
		// switch ($this->_sort_type) {

		// 	// 登録順（降順）
		// 	case PARAM_CONST_USER_SEARCH_SORT_TYPE_NEW_ENTRY:
		// 		$sorts['id'] = 'DESC';
		// 		break;

		// 	// 最終ログイン順（降順）
		// 	case PARAM_CONST_USER_SEARCH_SORT_TYPE_LAST_LOGIN_DATE:
		// 		$sorts['last_login_date'] = 'DESC';
		// 		break;
			
		// 	default:
		// 		// ソートなし
		// 		break;
		// }

		// 検索する
		$user_ids = null;
		$dao_user = new DaoUser();
		$users = $dao_user->select_for_search($wheres, $sorts, $this->_limit, $this->_offset, $this->_is_all);
		if (is_array($users) && count($users)) {

			foreach ($users as $user) {
				$user_ids[] = $user->id;
			}
			
		}
		if (!$user_ids)
			return true;

		$model_users = new ModelUsers($user_ids, $sorts);
		$this->_users = $model_users->gets();

		// 件数を抽出（ページャ用）
		$user_count = $dao_user->select_count_for_search($wheres, true);
		$this->_num = $user_count->num;

	}

	/**
	 * getter
	 *
	 */
	public function get() {

		return $this->_users;
	
	}

	/**
	 * getter
	 *
	 */
	public function get_num() {

		return $this->_num;
	
	}

	/**
	 * setter(会員コード）
	 *
	 */
	public function set_code($code) {

		return $this->_code = $code;

	}

	/**
	 * setter(氏名)
	 *
	 */
	public function set_name($name) {

		return $this->_name = $name;

	}

	/**
	 * setter(氏名（ふりがな）)
	 *
	 */
	public function set_name_kana($name_kana) {

		return $this->_name_kana = $name_kana;

	}

	/**
	 * setter(メールアドレス)
	 *
	 */
	public function set_email($email) {

		return $this->_email = $email;

	}

	/**
	 * setter(電話番号）
	 *
	 */
	public function set_tel($tel) {

		return $this->_tel = $tel;

	}

	/**
	 * setter(ステータス種別）
	 *
	 */
	public function set_status_type($status_type) {

		return $this->_status_type = $status_type;

	}

	/**
	 * setter(承認種別）
	 *
	 */
	public function set_approve_type($approve_type) {

		return $this->_approve_type = $approve_type;

	}

	/**
	 * setter(ソートキー）
	 *
	 */
	public function set_sort_key($sort_key) {

		return $this->_sort_key = $sort_key;

	}

	/**
	 * setter(ソート種別）
	 *
	 */
	public function set_sort_type($sort_type) {

		return $this->_sort_type = $sort_type;

	}

	/**
	 * setter(件数)
	 *
	 */
	public function set_limit($limit) {

		return $this->_limit = $limit;

	}

	/**
	 * setter(表示位置)
	 *
	 */
	public function set_offset($offset) {

		return $this->_offset = $offset;

	}

	/**
	 * setter(全件フラグ)
	 *
	 */
	public function set_is_all($_is_all) {

		return $this->_is_all = $_is_all;

	}

}