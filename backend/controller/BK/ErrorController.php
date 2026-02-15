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

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$user = $model_session->get('user');
		$is_login = $model_session->is_login();
		
		$model_session->close();

		$this->_login_user = $user;	
		$this->_view->assign('login_user', $user);

	}

	public function indexAction() {

	}

}