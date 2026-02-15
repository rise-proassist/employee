<?php

/**
 * MODEL 現場
 * 
 * @author kanemiya
 *
 */

class ModelLocation {

	private $_location = null;

	private $_location_shift_ids = null;

	private $_location_date_from = null;

	private $_location_date_to = null;
	
	/**
	 * コンストラクタ
	 *
	 */
	function __construct($location_id = null) {

		if (!$location_id)
			return false;

		// 現場情報
		$dao_location = new DaoLocation();
		$location = $dao_location->select_by_key($location_id);

		// 現場シフト
		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where(array('location_id' => $location_id));
		foreach ((array)$location_shifts as $location_shift) {
			$this->_location_shift_ids[] = $location_shift->id;
			if (!$this->_location_date_from || strtotime($this->_location_date_from) > strtotime($location_shift->shift_date_from)) {
				$this->_location_date_from = $location_shift->shift_date_from;
			}
			if (!$this->_location_date_to || strtotime($this->_location_date_to) < strtotime($location_shift->shift_date_to)) {
				$this->_location_date_to = $location_shift->shift_date_to;
			}
		}

		// 現場アサイン従事者
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_users = $dao_location_assign_user->select_where_with_user(array('lau.location_shift_id' => $this->_location_shift_ids));

		$this->_location = $this->factory($location, $location_shifts, $location_assign_users);

	}

	/**
	 * getter
	 *
	 */
	public function get() {
		return $this->_location;
	}

	/**
	 * getter
	 *
	 */
	public function get_shift_date_from() {
		return $this->_location_date_from;
	}

	/**
	 * getter
	 *
	 */
	public function get_shift_date_to() {
		return $this->_location_date_to;
	}

	/**
	 * setter
	 *
	 */
	public function set($location) {
		return $this->_location = $location;
	}

	/**
	 * データ整形
	 * 
	 */
	public function factory($location = null, $location_shifts = null, $location_assign_users = null) {

		if (!$location)
			$location = $this->_location;

		if (!$location)
			return false;

		$location_class = new stdClass();
		$location_class->id = $location->id;
		$location_class->name = $location->name;
		$location_class->location_date = $location->location_date;
		$location_class->address = $location->address;
		$location_class->detail = $location->detail;
		$location_class->is_assigned = $location->is_assigned;
		$location_class->is_closed = $location->is_closed;
		$location_class->create_date = $location->create_date;
		$location_class->update_date = $location->update_date;
		$location_class->location_shifts = null;
		if (is_array($location_shifts)) {

			$location_shift_classes = null;
			$location_date_from = null;
			$location_date_to = null;
			foreach ($location_shifts as $location_shift) {

				if ($location->id != $location_shift->location_id)
					continue;

				if (!$location_date_from || $location_date_from > $location_shift->shift_date_from)
					$location_date_from = $location_shift->shift_date_from;

				if (!$location_date_to || $location_date_to < $location_shift->shift_date_to)
					$location_date_to = $location_shift->shift_date_to;			

				$location_shift_class = new stdClass();
				$location_shift_class->id = $location_shift->id;
				$location_shift_class->location_id = $location_shift->location_id;
				$location_shift_class->name = $location_shift->name;
				$location_shift_class->shift_date_from = $location_shift->shift_date_from;
				$location_shift_class->shift_date_to = $location_shift->shift_date_to;
				$location_shift_class->request_num = $location_shift->request_num;
				$location_shift_class->note = $location_shift->note;
				$location_shift_class->create_date = $location_shift->create_date;
				$location_shift_class->update_date = $location_shift->update_date;
				$location_shift_class->location_assign_users = null;
				if (is_array($location_assign_users)) {

					$location_assign_user_classes = null;
					foreach ($location_assign_users as $location_assign_user) {

						if ($location_shift->id != $location_assign_user->location_shift_id)
							continue;

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
						$location_assign_user_class->user_id = $location_assign_user->user_id;
						$location_assign_user_class->name = $location_assign_user->name;
						$location_assign_user_class->name_kana = $location_assign_user->name_kana;
						$location_assign_user_class->tel = $location_assign_user->tel;
						$location_assign_user_class->email = $location_assign_user->email;
						$location_assign_user_class->post_code = $location_assign_user->post_code;
						$location_assign_user_class->address = $location_assign_user->address;
						$location_assign_user_class->gender_type = $location_assign_user->gender_type;
						$location_assign_user_class->gender_type_name = PARAM_CONST_GENDER_TYPES[$location_assign_user->gender_type];
						$location_assign_user_class->birth_day = $location_assign_user->birth_day;
						$location_assign_user_class->country_id = $location_assign_user->country_id;
						$location_assign_user_class->country_name = $location_assign_user->country_name;
						$location_assign_user_class->employ_type = $location_assign_user->employ_type;
						$location_assign_user_class->employ_type_name = PARAM_CONST_EMPLOY_TYPES[$location_assign_user->employ_type];
						$location_assign_user_class->company_id = $location_assign_user->company_id;
						$location_assign_user_class->company_name = $location_assign_user->company_name;
						$location_assign_user_class->etc_company_name = $location_assign_user->etc_company_name;
						$location_assign_user_class->last_login_date = $location_assign_user->last_login_date;
						$location_assign_user_classes[] = $location_assign_user_class;

					}
					$location_shift_class->location_assign_users = $location_assign_user_classes;
				}
				$location_shift_classes[] = $location_shift_class;
			}

			$location_class->location_shifts = $location_shift_classes;
			$location_class->location_date_from = $location_date_from;
			$location_class->location_date_to = $location_date_to;
		}

		return $location_class;

	}

}