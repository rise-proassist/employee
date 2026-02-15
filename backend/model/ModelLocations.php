<?php

/**
 * MODEL 現場（複数用）
 * 
 * @author kanemiya
 *
 */

class ModelLocations extends ModelLocation {

	private $_locations = null;

	private $_location_shift_ids = null;

	private $_location_date_from = null;

	private $_location_date_to = null;

	/**
	 * コンストラクタ
	 *
	 */
	function __construct($location_ids = null, $sorts = null) {

		if (!$location_ids)
			return false;

		// 現場情報
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_where(array('id' => $location_ids), $sorts);

		// 現場シフト
		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where(array('location_id' => $location_ids));
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

		foreach ($locations as $location) {

			$this->_locations[$location->id] = $this->factory(
				$location,
				$location_shifts,
				$location_assign_users
			);

		}

	}

	/**
	 * getter
	 *
	 */
	public function gets() {
		return $this->_locations;
	}

	/**
	 * getter by id
	 *
	 */
	public function get($id = null) {

		if (!$id)
			return null;

		if (!isset($this->_locations[$id]))
			return false;

		return $this->_locations[$id];
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

}