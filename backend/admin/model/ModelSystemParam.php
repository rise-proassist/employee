<?php

/**
 * MODEL システムパラメータ
 *
 * @author kanemiya
 *
 */

class ModelSystemParam {

	private $_dao = null;

	private $_datas = null;

	function __construct() {

		$this->_dao = new DaoSystemParam();
		$this->_datas = $this->_dao->select_all();

	}

	/**
	 * ゲッタ
	 *
	 * @param int $key キー(id)
	 * @return string $value 値
	 */
	public function get_value($key) {

		foreach ($this->_datas as $data) {
			if ($key == $data->id)
				return $data->value;
		}

		return null;
	}

	/**
	 * セッタ
	 *
	 * @param int $key キー(id)
	 * @param string $value 値
	 * @return boolean 処理結果
	 */
	public function set_value($key, $value) {

		if (!$key)
			return false;

		$sets['value'] = $value;
		$where['id'] = $key;
		if (!$this->_dao->update($sets, $where))
			return false;

		return true;
	}

}