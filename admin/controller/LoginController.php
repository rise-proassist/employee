<?php

/**
 * CONTROLLER : ログイン
 *
 *　@author kanemiya
 */

class LoginController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {
		
	}

	public function indexAction() {

	}

	/**
	 * フォームアクション
	 *
	 */
	public function formAction() {

		if (UtilLogin::is_login())
			header('Location: ' . UtilCommon::get_base_url('top'));

		parent::setView('login', 'form');

	}

	public function execAction() {

		$email = $this->_request->getPost('email');
		$password = $this->_request->getPost('password');
		$token = $this->_request->getPost('token');

		try {

			$model_session = new ModelSession();
			$model_session->set_dir(ADMIN_SESSION_DIR);
			$model_session->open();

			// 未入力チェック
			if (!$email || !$password)
				throw new Exception(ERR_MSG_LOGIN, 1);

			if (ADMIN_ROOT_LOGIN_EMAIL == $email && ADMIN_ROOT_PASSWORD == $password) {

				$admin_user = new EntityAdminUser();
				$admin_user->id = 9999; // 適当
				$admin_user->name = ADMIN_ROOT_USER_NAME;
				$admin_user->auth_level = PARAM_CONST_AUTH_LEVEL_ADMIN;

			} else {

				// $email = UtilLogin::hsc($email);
				// $password = UtilLogin::hsc($password);

				// データチェック
				$dao_admin_user = new DaoAdminUser();
				$admin_user = $dao_admin_user->select_where_one(array('email' => $email));
				if (!$admin_user)
					throw new Exception(ERR_MSG_LOGIN, 1);

				if (!password_verify($password, $admin_user->password))
					throw new Exception(ERR_MSG_LOGIN, 1);

			}

			// if (!UtilLogin::validate_token($token && password_verify($password, $user->password)))
			// 	throw new Exception(ERR_MSG_LOGIN, 1);

			// セッションIDの追跡を防ぐ
			$model_session->regenerate_id(true);

			// セッションにログイン情報をセット
			$model_session->set('id', $admin_user->id);
			$model_session->set('name', $admin_user->name);
			$model_session->set('auth_level', $admin_user->auth_level);

			$model_session->close();

			header('Location: ' . UtilCommon::get_base_url('top'));
			exit;

		} catch (Exception $e) {

			$this->_view->assign('error_message', $e->getMessage());
			$this->_view->assign('error_code', $e->getCode());
			// parent::setAction('form');
			parent::setView('login', 'form');
			return;	

		}

	}

	/**
	 * ログアウトアクション
	 *
	 */
	public function logoutAction() {

		// UtilLogin::logout();

		$model_session = new ModelSession();
		$model_session->set_dir(ADMIN_SESSION_DIR);
		$model_session->open();

		$model_session->set('id', null);
		$model_session->set('name', null);
		$model_session->set('auth_level', null);

		$model_session->destroy();

		header('Location: ' . UtilCommon::get_base_url('Login', 'form'));
		exit;		

	}

}