<?php

/**
 * パラメータユーティリティ
 *
 * @author kanemiya
 *
 */

class UtilParam {

	/**
	 * 指定パラメータの名称を取得する
	 *
	 * @param int $id キーID
	 * @return string 名称
	 */
	public static function get_param_name($id, $params) {

		foreach ($params as $param_id => $param_value) {

			if ($param_id == $id)
				return $param_value;
		}

		return false;
	}
}