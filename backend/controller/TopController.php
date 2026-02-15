<?php

/**
 * CONTROLLER : TOP
 *
 *　@author kanemiya
 */

class TopController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	private $_login_account;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$account = $model_session->get('account');
		$is_login = $model_session->is_login();

		$model_session->close();

		// ログイン種別チェック
		if ($is_login)
			header('Location: ' . UtilCommon::get_base_url('mypage'));

		// $this->_view->assign('login_account', $account);
		// $this->_login_account = $account;

		
	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {

		// echo 'top';

	}


}