<?php

/**
 * バリデーションエラー時の例外 
 * 
 */
class ValidatiteException extends Exception {

	public function __construct() {
		
		parent::__construct('Validation error.', 800);
	
	}

}