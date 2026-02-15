<?php

/**
 * DTO リクエスト基底クラス
 *
 * @author kanemiya
 *
 */

class DtoResponseBase {
	
	const RESULT_CODE_SUCCESS = 0;
	
	public $code = self::RESULT_CODE_SUCCESS;

	public $message = null;
	
}