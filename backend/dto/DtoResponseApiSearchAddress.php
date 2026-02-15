<?php

/**
 * DTO API 郵便番号住所検索レスポンス
 */
class DtoResponseApiSearchAddress extends DtoResponseBase {

	public $success = false;

	public $address = null;

	public $addresses = array();

}
