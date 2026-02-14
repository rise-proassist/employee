<?php

/**
 * CONTROLLER : Account
 *
 *　@author kanemiya
 */

class AccountController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const NICK_NAME_LENGTH = 16;
	const NAME_LENGTH = 16;
	const PASSWORD_MIN_LENGTH = 6;
	const PASSWORD_MAX_LENGTH = 16;
	const EMAIL_LENGTH = 64;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		// $user = $model_session->get('user');
		$is_login = $model_session->is_login();

		$model_session->close();

		// ログイン種別チェック
		if ($is_login) {
			header('Location: ' . UtilCommon::get_base_url('main'));
			return;
		}

		// 未ログイン状態の場合は、ログイン情報はnull
		$this->_view->assign('login_user', null);

	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {}

	/**
	 * 会員登録（メールアドレス）フォームアクション
	 *
	 */
	public function registEmailFormAction() {

	}

	/**
	 * 会員登録（メールアドレス）確認アクション
	 *
	 */
	public function registEmailConfirmAction() {

		$email = $this->_request->getPost('email');

		$error_messages = array();

		try {

			// メールアドレス : 未入力チェック
			if (UtilCommon::is_empty($email))
				$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');		

			// メールアドレス : 文字数チェック
			if (!UtilCommon::is_length($email, self::EMAIL_LENGTH))
				$error_messages['email'] = sprintf(ERR_MSG_LENTGTH, 'メールアドレス', self::EMAIL_LENGTH);

			// メールアドレス : フォーマットチェック
			if (!UtilCommon::is_mail($email))
				$error_messages['email'] = sprintf(ERR_MSG_INPUT, 'メールアドレス');

			// メールアドレス : 重複チェック
			$dao_user = new DaoUser();
			$user = $dao_user->select_where_one(array('email' => $email));
			if ($email && $user)
				$error_messages['email'] = sprintf(ERR_MSG_USED_YET, 'メールアドレス');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			// 入力内容をセッションに保存
			$input_data['email'] = $email;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

			$this->_view->assign('email', $email);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('registEmailForm');

		}

	}


	/**
	 * 会員登録（メールアドレス）完了アクション
	 *
	 */
	public function registEmailFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		try {

			if (!$input_data)
				throw new Exception("Error Processing Request", 1);

			// 認証コードを発行する
			$auth_code = UtilCommon::random_str(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');

			// ユーザ認証登録
			$entity_auth_user = new EntityAuthUser();
			$entity_auth_user->id = null;
			$entity_auth_user->email = $input_data['email'];
			$entity_auth_user->auth_code = $auth_code;
			$entity_auth_user->is_authed = 0;
			$entity_auth_user->expire_date = date("Y-m-d H:i:s",strtotime("+1 day")); // 有効期限は、24時間後とする
			$dao_auth_user = new DaoAuthUser();
			if(!$auth_user_id = $dao_auth_user->insert($entity_auth_user))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// セッションを削除する
			$model_session->destroy();
			$model_session->close();

			// 会員登録完了メールを送信
			$mail_params['email'] = $input_data['email'];
			$mail_params['auth_code'] = $auth_code;
			UtilMail::send(NOTICE_EMAIL, $input_data['email'] , MAIL_SUBJECT_REGIST_FINISH, 'auth_email', $mail_params);

		} catch (Exception $e) {

			echo $e->getMessage();

		}

	}

	/**
	 * 会員登録（プロフィール）フォームアクション
	 *
	 */
	public function registProfileFormAction() {

		$auth_code = $this->_request->getQuery('auth_code');

		$dao_auth_user = new DaoAuthUser();
		$auth_user = $dao_auth_user->select_where_one(array('auth_code' => $auth_code, 'is_authed' => 0));

		// 認証ユーザ : 必須チェック
		if (!$auth_user)
			throw new Exception(sprintf(ERR_MSG_INVALID, '認証URL'));

		// 認証ユーザ : 有効期限チェック
		if (date("Y-m-d H:i:s") > $auth_user->expire_date)
			throw new Exception(sprintf(ERR_MSG_EXPIRE_DATE, '認証URL'));

		// 入力内容をセッションに保存
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$model_session->set('auth_user', $auth_user);

		$this->_view->assign('auth_user', $auth_user);
		$this->_view->assign('prefectures', PARAM_CONST_PREFECTURES);

	}


	/**
	 * 会員登録（プロフィール）確認アクション
	 *
	 */
	public function registProfileConfirmAction() {

		$password = $this->_request->getPost('password');
		$re_password = $this->_request->getPost('re_password');
		$nick_name = $this->_request->getPost('nick_name');
		$name = $this->_request->getPost('name');
		$birth_day = $this->_request->getPost('birth_day');
		$tel = $this->_request->getPost('tel');
		$address_1 = $this->_request->getPost('address_1');
		$address_2 = $this->_request->getPost('address_2');
		$address_3 = $this->_request->getPost('address_3');

		$error_messages = array();

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$auth_user = $model_session->get('auth_user');

		if (!$auth_user)
			throw new Exception(ERR_MSG_RETRY);

		try {

			// パスワード : 未入力チェック
			if (UtilCommon::is_empty($password))
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');		

			// パスワード : 文字数チェック
			if ($password && !UtilCommon::is_length_range($password, self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH_RANGE, 'パスワード', self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH);

			// パスワード（再入力） : 未入力チェック
			if (UtilCommon::is_empty($re_password))
				$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, 'パスワード（再入力） ');		

			// パスワード（再入力） : 文字数チェック
			if ($re_password && !UtilCommon::is_length_range($re_password, self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH))
				$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH_RANGE, 'パスワード（再入力）', self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH);

			// パスワード（再入力） : 不一致チェック
			if ($password != $re_password)
				$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME, 'パスワード', 'パスワード（再入力）');

			// ニックネーム : 未入力チェック
			if (UtilCommon::is_empty($nick_name))
				$error_messages['nick_name'] = sprintf(ERR_MSG_EMPTY, 'ニックネーム');		

			// ニックネーム : 文字数チェック
			if (!UtilCommon::is_length($nick_name, self::NICK_NAME_LENGTH))
				$error_messages['nick_name'] = sprintf(ERR_MSG_LENTGTH, 'ニックネーム', self::NICK_NAME_LENGTH);

			// お名前 : 未入力チェック
			if (UtilCommon::is_empty($name))
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, 'お名前');		

			// お名前 : 文字数チェック
			if (!UtilCommon::is_length($nick_name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(NAME_LENGTH, 'お名前', self::NAME_LENGTH);

			// 電話番号 : 未入力チェック
			if (UtilCommon::is_empty($tel))
				$error_messages['tel'] = sprintf(ERR_MSG_EMPTY, '電話番号');

			// 電話番号 : 数値チェック
			if ($tel && !UtilCommon::is_num($tel))
				$error_messages['tel'] = sprintf(ERR_MSG_INPUT, '電話番号');

			// 生年月日 : 未入力チェック
			if (UtilCommon::is_empty($birth_day))
				$error_messages['birth_day'] = sprintf(ERR_MSG_INPUT, '生年月日');

			// 生年月日 : 日付フォーマットチェック
			if ($birth_day && !UtilCommon::is_date($birth_day))
				$error_messages['birth_day'] = sprintf(ERR_MSG_INPUT, '生年月日');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			// 入力内容をセッションに保存
			$input_data['password'] = $password;
			$input_data['nick_name'] = $nick_name;
			$input_data['name'] = $name;
			$input_data['birth_day'] = $birth_day;
			$input_data['tel'] = $tel;

			$model_session->set('input_data', $input_data);

			$this->_view->assign('auth_user', $auth_user);
			$this->_view->assign('nick_name', $nick_name);
			$this->_view->assign('name', $name);
			$this->_view->assign('birth_day', $birth_day);
			$this->_view->assign('tel', $tel);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('prefectures', PARAM_CONST_PREFECTURES);
			$this->_view->assign('auth_user', $auth_user);

			// フォームへ遷移
			$this->setAction('registProfileForm');

		}

	}

	/**
	 * 会員登録（プロフィール）完了アクション
	 *
	 */
	public function registProfileFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');
		$auth_user = $model_session->get('auth_user');

		if (!$input_data)
			throw new Exception("Error Processing Request", 1);

		// アカウント登録
		$dao_user = new DaoUser();
		$dao_user->begin();

		$entity_user = new EntityUser();
		$entity_user->id = null;
		$entity_user->email = $auth_user->email;
		$entity_user->password = UtilCommon::to_hash_password($input_data['password']);
		$entity_user->code = ""; // 一旦、空をセット
		$entity_user->name = $input_data['name'];
		$entity_user->birth_day = $input_data['birth_day'];
		$entity_user->tel = $input_data['tel'];
		$entity_user->nick_name = $input_data['nick_name'];
		$entity_user->status_type = USER_STATUS_TYPE_ACTIVE;
		$entity_user->is_cert = false;

		if(!$user_id = $dao_user->insert($entity_user))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// 会員コードを発行する
		$user_code = UtilCommon::gererate_user_code($user_id);

		$sets['code'] = $user_code;
		$wheres['id'] = $user_id;
		if (false === $dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// ユーザ年齢認証用の証明書ファイル設置先ディレクトリを作成
		$upload_dir = ASSETS_DIR . '/user/' . $user_code;
		mkdir($upload_dir, 0777);

		// ユーザ認証を承認済みにする
		$sets = null;
		$wheres = null;
		$sets['is_authed'] = 1;
		$wheres['id'] = $auth_user->id;
		$dao_user_auth = new DaoAuthUser();
		if (false === $dao_user_auth->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// セッションを削除する
		$model_session->destroy();
		$model_session->close();

		$dao_user->commit();
		// $dao_user->rollback();

		// 会員登録完了メールを送信
		$mail_params['email'] = $auth_user->email;
		$mail_params['user_code'] = $user_code;
		$mail_params['name'] = $input_data['name'];
		$mail_params['birth_day'] = $input_data['birth_day'];
		$mail_params['tel'] = $input_data['tel'];
		$mail_params['nick_name'] = $input_data['nick_name'];

		UtilMail::send(NOTICE_EMAIL, $auth_user->email , MAIL_SUBJECT_REGIST_FINISH, 'regist_finish', $mail_params);

	}

}