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

	const PASSWORD_LENGTH = 16;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$user = $model_session->get('user');
		$is_login = $model_session->is_login();
		
		$model_session->close();

		// ログイン種別チェック
		if ($is_login) {
			header('Location: ' . UtilCommon::get_base_url('top'));
			return;
		}

		$this->_view->assign('login_user', $user);

	}

	/**
	 * フォームアクション
	 *
	 */
	public function indexAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$token_byte = openssl_random_pseudo_bytes(CSRF_TOKEN_LENGTH);
		$csrf_token = bin2hex($token_byte);

		//セッションに設定
		$model_session->set('csrf_token', $csrf_token);
		$model_session->close();

		$this->_view->assign('csrf_token', $csrf_token);

		parent::setView('login', 'index');

	}

	/**
	 * 実行アクション
	 *
	 */
	public function execAction() {

		$email = $this->_request->getPost('email');
		$password = $this->_request->getPost('password');
		$csrf_token = $this->_request->getPost('csrf_token');

		$error_messages = array();

		try {

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();

			// CSRFチェック
			if ($csrf_token !== $model_session->get('csrf_token'))
				throw new Exception(ERR_MSG_LOGIN, 1);

			$error_messages = array();

			// メールアドレス : 未入力チェック
			if (UtilCommon::is_empty($email))
				$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');

			// パスワード : 未入力チェック
			if (UtilCommon::is_empty($password))
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$email = UtilLogin::hsc($email);
			$password = UtilLogin::hsc($password);

			// 会員登録状態チェック
			$dao_user = new DaoUser();
			$user = $dao_user->select_where_one(array('email' => $email));
			if (!$user)
				throw new Exception(ERR_MSG_LOGIN, 3);

			// パスワードチェック
			if (!password_verify($password, $user->password))
				throw new Exception(ERR_MSG_LOGIN, 4);

			// セッションIDの追跡を防ぐ
			$model_session->regenerate_id(true);

			// セッションにログイン情報をセット
			$model_session->set('id', $user->id);
			$model_session->set('user', $user);

			$model_session->close();

			// ログイン中を示す
			$this->_login_user = $user;

			// 最終ログイン日時を更新
			$sets['last_login_date'] = date('Y-m-d H:i:s');
			$wheres['id'] = $user->id;
			$user = $dao_user->update($sets, $wheres);

			// マイページへリダイレクト
			header('Location: ' . UtilCommon::get_base_url('top'));
			exit;

		} catch (Exception $e) {

			$this->_view->assign('error_message', count($error_messages) ? $error_messages : $e->getMessage());
			$this->_view->assign('csrf_token', $csrf_token);
			parent::setView('login');
			// parent::setAction('form');
			return;	
		}

		// $this->_view->assign('id', $id);

	}

	/**
	 * ログアウトアクション
	 *
	 */
	public function logoutAction() {

		// UtilLogin::logout();

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$model_session->destroy();

		header('Location: ' . UtilCommon::get_base_url('Login'));
		exit;		

	}

	/**
	 * パスワード再発行フォームアクション
	 *
	 */
	public function reissuePasswordFormAction() {

	}

	/**
	 * パスワード再発行確認アクション
	 *
	 */
	public function reissuePasswordConfirmAction() {

		$email = $this->_request->getPost('email');

		$error_messages = array();

		try {

			// 未入力チェック : メールアドレス
			if (!$email)
				$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');

			// 未登録チェック : メールアドレス
			$dao_user = new DaoUser();
			$user = $dao_user->select_where_one(array('email' => $email));
			if ($email && !$user)
				$error_messages['email'] = sprintf(ERR_MSG_NOT_FOUND, '会員情報');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$input_data['user'] = $user;
			$input_data['email'] = $email;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);
			$model_session->close();

			$this->_view->assign('email', $email);

		} catch (Exception $ve) {

			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('reissuePasswordForm');

		}


	}

	/**
	 * パスワード再発行完了アクション
	 *
	 */
	public function reissuePasswordFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		$error_messages = array();

		try {

			if (!$input_data)
				throw new Exception(ERR_MSG_RETRY, 1);

			// 認証コードを発行する
			$auth_code = UtilCommon::random_str(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');

			// ユーザ認証登録
			$entity_auth_email = new EntityAuthEmail();
			$entity_auth_email->id = null;
			$entity_auth_email->email = $input_data['email'];
			$entity_auth_email->auth_code = $auth_code;
			$entity_auth_email->is_authed = 0;
			$entity_auth_email->expire_date = date("Y-m-d H:i:s",strtotime("+1 day")); // 有効期限は、24時間後とする
			$dao_auth_email = new DaoAuthEmail();
			if(!$auth_email_id = $dao_auth_email->insert($entity_auth_email))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// セッションを削除する
			$model_session->destroy();
			$model_session->close();

			// 会員登録完了メールを送信
			$mail_params['email'] = $input_data['email'];
			$mail_params['auth_code'] = $auth_code;
			UtilMail::send(NOTICE_EMAIL, $input_data['email'] , MAIL_SUBJECT_REISSUE_PASSWORD, 'auth_password', $mail_params);


		} catch (Exception $e) {
			
			$this->_logger->fatal($e->getMessage());
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('reissuePasswordForm');
			return;

		}

	}

	/**
	 * パスワード再設定フォームアクション
	 *
	 */
	public function reissuePasswordEditFormAction() {

		$auth_code = $this->_request->getQuery('auth_code');

		try {

			// 未入力チェック
			if (!$auth_code)
				throw new Exception(ERR_MSG_BAD_OPERATION, 1);

			// 存在チェック
			$dao_auth_email = new DaoAuthEmail();
			$auth_email = $dao_auth_email->select_where_one(array('auth_code' => $auth_code, 'is_authed' => false));
			if (!$auth_email)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, 'パスワード再発行の申請'), 1);

			// 有効期限切れチェック
			if(strtotime(date("Y-m-d H:i:s")) > strtotime($auth_email->expire_date))
				throw new Exception(sprintf(ERR_MSG_EXPIRARE_DATE, 'URL'), 1);

			$input_data['auth_email'] = $auth_email;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);
			$model_session->close();

		} catch (Exception $e) {

			$this->_view->assign('error_message', $e->getMessage());

		}

	}

	/**
	 * パスワード再設定完了アクション
	 *
	 */
	public function reissuePasswordEditFinishAction() {

		$password = $this->_request->getPost('password');
		$re_password = $this->_request->getPost('re_password');

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		if (!$input_data)
			throw new Exception(ERR_MSG_RETRY, 1);

		$auth_email = $input_data['auth_email'];
		if (!$auth_email)
			throw new Exception(ERR_MSG_RETRY, 2);

		try {

			$error_messages = array();

			// 未入力チェック : 新しいパスワード
			if (!$password)
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, '新しいパスワード');

			// 文字数チェック : 新しいパスワード
			if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, '新しいパスワード', self::PASSWORD_LENGTH);

			// 未入力チェック : 新しいパスワード（確認）
			if (!$re_password)
				$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, '新しいパスワード（確認）');

			// 文字数チェック : 新しいパスワード（確認）
			if (!UtilCommon::is_length($re_password, self::PASSWORD_LENGTH))
				$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH, '新しいパスワード（確認）', self::PASSWORD_LENGTH);

			// 不一致チェック : 新しいパスワード, 新しいパスワード（確認）
			if ($password != $re_password)
				$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME, '新しいパスワード', '新しいパスワード（確認）');

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// ユーザパスワード更新
			$dao_user = new DaoUser();
			$dao_user->begin();

			// ユーザ存在チェック
			$user = $dao_user->select_where_one(array('email' => $auth_email->email));
			if (!$user)
				throw new Exception(ERR_MSG_RETRY, 3);

			$sets['password'] = UtilCommon::to_hash_password($password);
			$wheres['id'] = $user->id;
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_RETRY, 4);

			// 処理済みフラグ更新
			$sets = null;
			$wheres = null;
			$sets['is_authed'] = true;
			$wheres['id'] = $auth_email->id;
			$dao_auth_email = new DaoAuthEmail();
			if (false === $dao_auth_email->update($sets, $wheres))
				throw new Exception(ERR_MSG_RETRY, 1);			

			$dao_user->commit();

			$model_session->destroy();
			$model_session->close();

		} catch (ValidatiteException $e) {

			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('reissuePasswordEditForm');

		}

	}

}