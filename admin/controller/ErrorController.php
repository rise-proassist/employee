<?php

/**
 * CONTROLLER : エラー
 *
 *　@author kanemiya
 */

class ErrorController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	function __construct() {
	
		parent::__construct();

	}

	/**
	 * 共通事前処理
	 *
	 */
	protected function preAction() {
		
	}

	public function indexAction() {

	}

}