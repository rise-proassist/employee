<?php

/**
 * MODEL 現場アサイン従事者検索
 * @author kanemiya
 *
 */

class ModelLocationAssignUserSearch {

	private $_location_assign_users = null;

	private $_code = null;

	private $_name = null;

	private $_shift_date_from = null;

	private $_shift_date_to = null;

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

		// 検索条件 : シフト日時（開始）
		if ($this->_shift_date_from)
			$wheres['ls.shift_date_from'] = $this->_shift_date_from;

		// 検索条件 : シフト日時（終了）
		if ($this->_shift_date_to)
			$wheres['ls.shift_date_to'] = $this->_shift_date_to;

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
		// ソート種別
		// $sorts = array();
		// switch ($this->_sort_type) {

		// 	// 登録順（降順）
		// 	case PARAM_CONST_USER_SEARCH_SORT_TYPE_NEW_ENTRY:
		// 		$sorts['lau.id'] = 'DESC';
		// 		break;

		// 	// 最終ログイン順（降順）
		// 	case PARAM_CONST_USER_SEARCH_SORT_TYPE_LAST_LOGIN_DATE:
		// 		$sorts['u.last_login_date'] = 'DESC';
		// 		break;
			
		// 	default:
		// 		// ソートなし
		// 		break;
		// }

		// 検索する
		$user_ids = null;
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_users = $dao_location_assign_user->select_for_search($wheres, $sorts, $this->_limit, $this->_offset, $this->_is_all);

		if (is_array($location_assign_users) && count($location_assign_users)) {

			foreach ($location_assign_users as $location_assign_user) {
				$user_ids[] = $location_assign_user->user_id;
			}
			
		}
		if (!$user_ids)
			return true;

		$model_users = new ModelUsers($user_ids);
		$users = $model_users->gets();

		// 件数を抽出（ページャ用）
		$location_assign_user_count = $dao_location_assign_user->select_count_for_search($wheres);
		$this->_num = $location_assign_user_count->num;

		$this->_location_assign_users = $this->factory($location_assign_users, $users);

	}

	/**
	 * getter
	 *
	 */
	public function get() {

		return $this->_location_assign_users;
	
	}

	/**
	 * getter
	 *
	 */
	public function get_num() {

		return $this->_num;
	
	}

	/**
	 * setter(氏名)
	 *
	 */
	public function set_name($name) {

		return $this->_name = $name;

	}

	/**
	 * setter(会員コード）
	 *
	 */
	public function set_code($code) {

		return $this->_code = $code;

	}

	/**
	 * setter(シフト日時（開始））
	 *
	 */
	public function set_shift_date_from($shift_date_from) {

		return $this->_shift_date_from = $shift_date_from;

	}

	/**
	 * setter(シフト日時（終了））
	 *
	 */
	public function set_shift_date_to($shift_date_to) {

		return $this->_shift_date_to = $shift_date_to;

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
	 * データ整形
	 * 
	 */
	public function factory($location_assign_users = null, $users = null) {

		if (!$location_assign_users)
			$location_assign_users = $this->_location_assign_users;

		if (!$location_assign_users)
			return false;

		$location_assign_user_classes = null;
		foreach ($location_assign_users as $location_assign_user) {

			$location_assign_user_class = new stdClass();
			$location_assign_user_class->id = $location_assign_user->id;
			$location_assign_user_class->location_shift_id = $location_assign_user->location_shift_id;
			$location_assign_user_class->location_shift_row = $location_assign_user->location_shift_row;
			$location_assign_user_class->allocate_cert_ids = explode(',', $location_assign_user->allocate_cert_ids);
			$location_assign_user_class->work_date_from = $location_assign_user->work_date_from;
			$location_assign_user_class->is_modify_from = $location_assign_user->is_modify_from;
			$location_assign_user_class->work_date_to = $location_assign_user->work_date_to;
			$location_assign_user_class->is_modify_to = $location_assign_user->is_modify_to;
			$location_assign_user_class->comment = $location_assign_user->comment;
			$location_assign_user_class->receipt_user_name = $location_assign_user->receipt_user_name;
			$location_assign_user_class->daily_wage = $location_assign_user->daily_wage;
			$location_assign_user_class->withhold_tax = $location_assign_user->withhold_tax;
			$location_assign_user_class->total_wage = $location_assign_user->total_wage;
			$location_assign_user_class->is_confirmed = $location_assign_user->is_confirmed;
			$location_assign_user_class->is_paid = $location_assign_user->is_paid;
			$location_assign_user_class->paid_date = $location_assign_user->paid_date;
			$location_assign_user_class->create_date = $location_assign_user->create_date;
			$location_assign_user_class->update_date = $location_assign_user->update_date;
			$location_assign_user_class->location_shift_name = $location_assign_user->location_shift_name;
			$location_assign_user_class->shift_date_from = $location_assign_user->shift_date_from;
			$location_assign_user_class->shift_date_to = $location_assign_user->shift_date_to;
			$location_assign_user_class->note = $location_assign_user->note;
			$location_assign_user_class->location_id = $location_assign_user->location_id;
			$location_assign_user_class->location_name = $location_assign_user->location_name;
			$location_assign_user_class->location_address = $location_assign_user->location_address;
			$location_assign_user_class->user = null;
			foreach ((array)$users as $user) {

				if ($location_assign_user->user_id == $user->id) {
					$location_assign_user_class->user = $user;
					break;
				}

			}

			$location_assign_user_classes[] = $location_assign_user_class;

		}

		return $location_assign_user_classes;

	}

}