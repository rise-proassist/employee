<?php

/**
 * CONTROLLER : ETC
 *
 *　@author kanemiya
 */

class EtcController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	private $_login_account;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {
		
	}

	/**
	 * インデックスアクション
	 *
	 */
	public function Action() {

		// echo 'top';

	}

	/**
	 * 同意書アクション
	 *
	 */
	public function consentAction() {

	}

	/**
	 * 個人情報取扱アクション
	 *
	 */
	public function privacyAction() {

	}


}