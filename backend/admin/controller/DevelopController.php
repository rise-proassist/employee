<?php

/**
 * CONTROLLER : 開発用
 *
 *　@author kanemiya
 */

class DevelopController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(ADMIN_SESSION_DIR);
		$model_session->open();

		// ログインチェック
		if (!$model_session->get('id'))
			parent::setView('login', 'form');

		// ユーザIDをセット
		$this->_user_id = $model_session->get('id');

		// ヘッダ判定用
		$this->_view->assign('login_user_id', $model_session->get('id'));
		$this->_view->assign('login_user_name', $model_session->get('name'));
		$this->_view->assign('login_user_auth_type', $model_session->get('auth_type'));

		$model_session->close();
		
	}

	public function indexAction() {

	}

	public function sendMailFormAction() {
	
	}

	public function sendMailFinishAction() {

		$email = $this->_request->getPost('email');

		try {

			$error_messages = array();

			// 未入力チェック : メールアドレス
			if (!$email)
				$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('sendMailForm');
			return;

		}

		// メールを送信
		$result = UtilMail::send('test@gtuned.net', $email, 'テストメール', 'test_mail');

	}


}