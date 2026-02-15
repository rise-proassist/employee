<?php

/** 
 * FILES変数クラス
 *
 * @author kanemiya
 */
class Files extends RequestVariables {

	protected function setValues() {
	
		foreach ($_FILES as $key => $value) {

			$this->_values[$key] = $value;
	
		}

	}
}