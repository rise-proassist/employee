<?php

class UrlParameter extends RequestVariables {

	protected function setValues() {

		// パラメーター取得（末尾の / は削除）
		$param = preg_replace('/\/$/', '', $_SERVER['REQUEST_URI']);

		$params = array();
		if ('' != $param)
			$params = explode('/', $param); // パラメーターを / で分割

		if (2 < count($params)) {
			foreach ($params as $param) { 
				// "_"で分割
				$splited = explode('_', $param);
				if (2 == count($splited)) {
					$key = $splited[0];
					$val = $splited[1];
					$this->_values[$key] = $val;
				}
			}
		}
	}
}