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

	private $_login_person;

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

		if ($user) {
			// $this->_view->assign('login_user', $user);
			$this->_login_user = $user;
		}

		$lang_type = $model_session->get('lang_type');
		if ($user && isset($user->lang_type)) {
			$lang_type = $user->lang_type;
		}
		if (!isset(PARAM_CONST_LANG_TYPES[$lang_type])) {
			$lang_type = PARAM_CONST_LANG_TYPE_JP;
		}

		// サイトタイトル（ヘッダ）
		switch ($this->_action) {
			case 'index':
			case 'exec':
				$this->_view->assign('site_title', (PARAM_CONST_LANG_TYPE_EN == $lang_type) ? 'Log in' : 'ログイン');
				break;
			case 'logout':
				$this->_view->assign('site_title', (PARAM_CONST_LANG_TYPE_EN == $lang_type) ? 'Log out' : 'ログアウト');
				break;
			case 'reissuePasswordForm':
			case 'reissuePasswordConfirm':
			case 'reissuePasswordEditFinish':			
			default:
				$this->_view->assign('site_title', (PARAM_CONST_LANG_TYPE_EN == $lang_type) ? 'Reset password' : 'パスワードの再発行');
				break;
		}
		
		
	}

	/**
	 * フォームアクション
	 *
	 */
	public function indexAction() {

		// ログイン済みの場合は、TOPへ
		if ($this->_login_person) {
			header('Location: ' . UtilCommon::get_base_url('mypage'));
			exit;
		}

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

		// ログイン済みの場合は、TOPへ
		if ($this->_login_person) {
			header('Location: ' . UtilCommon::get_base_url('top'));
			exit;
		}

		$email = $this->_request->getPost('email');
		$password = $this->_request->getPost('password');
		$csrf_token = $this->_request->getPost('csrf_token');

		try {

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();

			// CSRFチェック
			if ($csrf_token !== $model_session->get('csrf_token'))
				throw new Exception(ERR_MSG_LOGIN, 1);

			// 未入力チェック
			if (!$email || !$password)
				throw new Exception(ERR_MSG_LOGIN, 2);

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
			$model_session->set('user', $user);

			$model_session->close();

			// 最終ログイン日時を更新
			$sets['last_login_date'] = date('Y-m-d H:i:s');
			$wheres['id'] = $user->id;
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// マイページへリダイレクト
			header('Location: ' . UtilCommon::get_base_url('mypage'));
			exit;

		} catch (Exception $e) {

			$this->_view->assign('error_message', $e->getMessage());
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
	 * 表示言語変更アクション
	 */
	public function changeLangAction() {

		$request_lang_type = (int)$this->_request->getPost('lang_type');
		$redirect_url = $this->_request->getPost('redirect_url');

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$user = $model_session->get('user');

		if (isset(PARAM_CONST_LANG_TYPES[$request_lang_type])) {
			$model_session->set('lang_type', $request_lang_type);

			if ($user) {
			$dao_user = new DaoUser();
			$sets['lang_type'] = $request_lang_type;
			$wheres['id'] = $user->id;
			$dao_user->update($sets, $wheres);

			$user->lang_type = $request_lang_type;
			$model_session->set('user', $user);
			}
		}

		$model_session->close();

		if (!$redirect_url || '/' !== substr($redirect_url, 0, 1)) {
			$redirect_url = ($user) ? '/mypage/' : '/login/';
		}

		header('Location: ' . $redirect_url);
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

		$request = $this->_request->getPost();
		$email = (isset($request['email'])) ? $request['email'] : null;

		try {

			$error_messages = array();

			// 未入力チェック : メールアドレス
			if (!$email)
				$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス') . '<br>' . sprintf(ERR_MSG_EMPTY_EN, 'Email');

			// 未登録チェック : メールアドレス
			$dao_user = new DaoUser();
			$user = $dao_user->select_where_one(array('email' => $email));
			if (!$user)
				$error_messages['email'] = sprintf(ERR_MSG_NOT_FOUND, 'アカウント') . '<br>' . sprintf(ERR_MSG_NOT_FOUND_EN, 'Acount');

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new Exception("Error Processing Request", 1);

			$input_data['user_id'] = $user->id;
			$input_data['email'] = $email;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);
			$model_session->close();

			$this->_view->assign('input_data', $input_data);

		} catch (Exception $ve) {

			$this->_logger->fatal($ve->getMessage());
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
		$model_session->destroy();
		$model_session->close();

		$error_messages = array();

		try {

			if (!$input_data)
				throw new Exception(ERR_MSG_RETRY, 1);

			// アクセスコードを発行
			$access_code = UtilCommon::random_str(16, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');

			$entity_user_reissue_password = new EntityUserReissuePassword();
			$entity_user_reissue_password->id = null;
			$entity_user_reissue_password->user_id = $input_data['user_id'];
			$entity_user_reissue_password->access_code = $access_code;
			$entity_user_reissue_password->expire_date = date("Y-m-d H:i:s", strtotime("+1 day"));
			$entity_user_reissue_password->is_processed = 0;
			$dao_user_reissue_password = new DaoUserReissuePassword();
			if (false === $dao_user_reissue_password->insert($entity_user_reissue_password))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// メール送信
			$mail_params['email'] = $input_data['email'];
			$mail_params['url'] = UtilCommon::get_base_url('login', 'reissuePasswordEditForm') . '?code=' . $access_code;
			$model_mail = new ModelMail();
			$model_mail->set_from(NOTICE_EMAIL);
			$model_mail->set_replyto(NOTICE_EMAIL);
			$model_mail->set_subject(MAIL_SUBJECT_REISSUE_PASSWORD);
			$model_mail->create_body('reissue_password', $mail_params);
			$model_mail->set_address($input_data['email']);
			$model_mail->send();

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

		$request = $this->_request->getQuery();
		$access_code = (isset($request['code'])) ? $request['code'] : null;

		try {

			// 未入力チェック
			if (!$access_code)
				throw new Exception(ERR_MSG_BAD_OPERATION . '<br>' . ERR_MSG_BAD_OPERATION_EN, __LINE__);

			// 存在チェック
			$dao_user_reissue_password = new DaoUserReissuePassword();
			$user_reissue_password = $dao_user_reissue_password->select_where_one(array('access_code' => $access_code, 'is_processed' => false));
			if (!$user_reissue_password)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, 'パスワード再発行の申請') . '<br>' . sprintf(ERR_MSG_NOT_FOUND_EN, 'Request a new password'), __LINE__);

			// 有効期限切れチェック
			if(strtotime(date("Y-m-d H:i:s")) > strtotime($user_reissue_password->expire_date))
				throw new Exception(sprintf(ERR_MSG_EXPIRARE_DATE, 'URL') . '<br>' . sprintf(ERR_MSG_EXPIRARE_DATE_EN, 'URL'), __LINE__);

			$input_data['user_id'] = $user_reissue_password->user_id;
			$input_data['reissue_password_id'] = $user_reissue_password->id;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);
			$model_session->close();

		} catch (Exception $e) {

			$this->_logger->fatal($e->getMessage());
			$this->_view->assign('error_message', $e->getMessage());

		}

	}

	/**
	 * パスワード再設定完了アクション
	 *
	 */
	public function reissuePasswordEditFinishAction() {

		$request = $this->_request->getPost();
		$password = (isset($request['password'])) ? $request['password'] : null;
		$re_password = (isset($request['re_password'])) ? $request['re_password'] : null;

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		try {

			$error_messages = array();

			// 未入力チェック : ユーザID
			if (!$input_data || !$input_data['user_id']|| !$input_data['reissue_password_id'])
				throw new Exception(ERR_MSG_RETRY, 1);

			// 未入力チェック : 新しいパスワード
			if (!$password)
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, '新しいパスワード') . '<br>' . sprintf(ERR_MSG_EMPTY_EN, 'Password');

			// 文字数チェック : 新しいパスワード
			if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, '新しいパスワード', self::PASSWORD_LENGTH) . '<br>' . sprintf(ERR_MSG_LENTGTH_EN, 'Password', self::PASSWORD_LENGTH);

			// 未入力チェック : 新しいパスワード（確認）
			if (!$re_password)
				$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, '新しいパスワード（確認）') . '<br>' . sprintf(ERR_MSG_EMPTY_EN, 'Re-enter Password');

			// 文字数チェック : 新しいパスワード（確認）
			if (!UtilCommon::is_length($re_password, self::PASSWORD_LENGTH))
				$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH, '新しいパスワード（確認）', self::PASSWORD_LENGTH) . '<br>' . sprintf(ERR_MSG_LENTGTH_EN, 'Re-enter Password', self::PASSWORD_LENGTH);

			// 不一致チェック : 新しいパスワード, 新しいパスワード（確認）
			if ($password != $re_password)
				$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME, '新しいパスワード', '新しいパスワード（確認）') . '<br>' . sprintf(ERR_MSG_NO_SAME_EN, 'New Password', 'Re-enter Password');


			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new Exception("Error Processing Request", 1);

			// ユーザパスワード更新
			$dao_user = new DaoUser();
			$dao_user->begin();

			$sets['password'] = UtilCommon::to_hash_password($password);
			$wheres['id'] = $input_data['user_id'];
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_RETRY, __LINE__);

			// 処理済みフラグ更新
			$dao_user_reissue_password = new DaoUserReissuePassword();
			$sets = null;
			$wheres = null;
			$sets['is_processed'] = true;
			$wheres['id'] = $input_data['reissue_password_id'];
			if (false === $dao_user_reissue_password->update($sets, $wheres))
				throw new Exception(ERR_MSG_RETRY, __LINE__);			

			$dao_user->commit();

			$model_session->destroy();
			$model_session->close();

		} catch (Exception $e) {

			$this->_logger->fatal($e->getMessage());
			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('reissuePasswordEditForm');

		}

	}

}