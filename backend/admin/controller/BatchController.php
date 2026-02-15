<?php

/**
 * CONTROLLER : サンプル
 *
 *　@author kanemiya
 */

class BatchController extends BaseController {

	// protected $_is_batch = false;

	protected $_is_view = false;

	protected $_logger;

	private $_auth_actions = array();

	function __construct() {

		parent::__construct(true);

	}

	protected function preAction() {

		// ログインチェック
		if (!UtilLogin::is_login())
			parent::setView('login', 'form');
		
	}

	public function indexAction() {

	}

	public function sampleAction() {
		
		$argv = $this->getArgv();

		$id = $argv[3];

		$dao_admin_user = new DaoAdminUser();
		$user = $dao_admin_user->select_by_key($id);

		var_dump($user);

		echo 'This Action is sample_action!';

	}


}