<?php

/**
 * CONTROLLER : MyPage
 *
 *　@author kanemiya
 */

class MypageController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const NICK_NAME_LENGTH = 16;
	const NAME_LENGTH = 16;
	const PASSWORD_MIN_LENGTH = 6;
	const PASSWORD_MAX_LENGTH = 16;
	const EMAIL_LENGTH = 64;

	const PRODUCT_NAME_LENGTH = 50;
	const OVER_PRICE = 999999;

	const MYPAGE_PRODUCT_PAGER_PER_PAGE = 10;
	const MYPAGE_PRODUCT_PAGER_DELTA = 5;

	const UPLOAD_PRODUCT_IMAGE_WIDTH = 400;

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
		if (!$is_login) {
			header('Location: ' . UtilCommon::get_base_url('login'));
			return;
		}

		$this->_login_user = $user;	
		$this->_view->assign('login_user', $user);
		$this->_view->assign('login_user_points', $model_session->get('user_points'));
	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$this->_view->assign('user', $user);
	}

	/**
	 * マイプロフィールアクション
	 *
	 */
	public function myProfileAction() {

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$this->_view->assign('user', $user);
		
	}

	/**
	 * メールアドレス変更フォームアクション
	 *
	 */
	public function emailUpdFormAction() {

		// ユーザ情報
		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$this->_view->assign('user', $user);

	}

	/**
	 * メールアドレス変更確認アクション
	 *
	 */
	public function emailUpdConfirmAction() {

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
			if ($user && $email == $user->email)
				$error_messages['email'] = sprintf(ERR_MSG_USED_YET, 'メールアドレス');

			// メールアドレス : 未変更チェック
			if ($user && $user->email && $email == $user->email)
				$error_messages['email'] = sprintf(ERR_MSG_NOT_MODIFI, 'メールアドレス');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception(ERR_MSG_RETRY, __LINE__);

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
			$this->setAction('emailUpdForm');

		}

	}

	/**
	 * メールアドレス変更メール送信完了アクション
	 *
	 */
	public function emailUpdSendMailFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		if (!$input_data)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 認証コードを発行する
		$auth_code = UtilCommon::random_str(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');

		// ユーザ認証登録
		$dao_auth_email = new DaoAuthEmail();
		$entity_auth_email = new EntityAuthEmail();
		$entity_auth_email->id = null;
		$entity_auth_email->email = $input_data['email'];
		$entity_auth_email->auth_code = $auth_code;
		$entity_auth_email->is_authed = 0;
		$entity_auth_email->expire_date = date("Y-m-d H:i:s", strtotime("+1 day")); // 有効期限は、24時間後とする
		if(!$auth_user_id = $dao_auth_email->insert($entity_auth_email))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// セッションを削除する
		$model_session->unset('input_data');
		$model_session->close();

		// ユーザ情報
		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		// メールアドレス変更確認URLメールを送信
		$mail_params['user'] = $user;
		$mail_params['auth_code'] = $auth_code;
		UtilMail::send(NOTICE_EMAIL, $input_data['email'] , MAIL_SUBJECT_MYPAGE_UPD_EMAIL_FINISH, 'upd_auth_email', $mail_params);

	}

	/**
	 * プロフィール（メールアドレス）変更完了アクション
	 *
	 */
	public function emailUpdFinishAction() {

		$auth_code = $this->_request->getQuery('auth_code');

		$dao_auth_email = new DaoAuthEmail();
		$auth_email = $dao_auth_email->select_where_one(array('auth_code' => $auth_code, 'is_authed' => 0));

		// 認証ユーザ : 必須チェック
		if (!$auth_email)
			throw new Exception(sprintf(ERR_MSG_INVALID, '確認URL'));

		// 認証ユーザ : 有効期限チェック
		if (date("Y-m-d H:i:s") > $auth_email->expire_date)
			throw new Exception(sprintf(ERR_MSG_EXPIRE_DATE, '確認URL'));

		// メールアドレスを更新
		$sets['email'] = $auth_email->email;
		$wheres['id'] = $this->_login_user->id;
		$dao_user = new DaoUser();
		if (false === $dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// ユーザ認証を承認済みにする
		$sets['is_authed'] = 1;
		$wheres['id'] = $auth_email->id;
		if (false === $dao_auth_email->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

	}

	/**
	 * パスワード変更フォームアクション
	 *
	 */
	public function passwordUpdFormAction() {}

	/**
	 * パスワード変更確認アクション
	 *
	 */
	public function passwordUpdConfirmAction() {

		$password = $this->_request->getPost('password');
		$re_password = $this->_request->getPost('re_password');

		$error_messages = array();

		try {

			// パスワード : 未入力チェック
			if (UtilCommon::is_empty($password))
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');		

			// パスワード : 文字数チェック
			if (!UtilCommon::is_length_range($password, self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH_RANGE, 'パスワード', self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH);

			// パスワード（再入力） : 未入力チェック
			if (UtilCommon::is_empty($re_password))
				$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, 'パスワード（再入力）');		

			// パスワード（再入力） : 文字数チェック
			if (!UtilCommon::is_length_range($re_password, self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH))
				$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH_RANGE, 'パスワード（再入力）', self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH);

			// パスワード（再入力） : 不一致チェック
			if ($password != $re_password)
				$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME, 'パスワード', 'パスワード（再入力）');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new ValidationException("Error Processing Request", 1);

			// 入力内容をセッションに保存
			$input_data['password'] = $password;
			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

		} catch (ValidationException $ve) {

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('passwordUpdForm');

		}

	}

	/**
	 * パスワード変更完了アクション
	 *
	 */
	public function passwordUpdFinishAction() {

		$password = $this->_request->getPost('password');
		$re_password = $this->_request->getPost('re_password');

		$error_messages = array();

		try {

			// パスワード : 未入力チェック
			if (UtilCommon::is_empty($password))
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');		

			// パスワード : 文字数チェック
			if (!UtilCommon::is_length_range($password, self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH_RANGE, 'パスワード', self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH);

			// パスワード（再入力） : 未入力チェック
			if (UtilCommon::is_empty($re_password))
				$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, 'パスワード（再入力）');		

			// パスワード（再入力） : 文字数チェック
			if (!UtilCommon::is_length_range($re_password, self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH))
				$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH_RANGE, 'パスワード（再入力）', self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH);

			// パスワード（再入力） : 不一致チェック
			if ($password != $re_password)
				$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME, 'パスワード', 'パスワード（再入力）');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new ValidationException("Error Processing Request", 1);

			// パスワードを更新
			$sets['password'] = UtilCommon::to_hash_password($password);
			$wheres['id'] = $this->_login_user->id;
			$dao_user = new DaoUser();
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// パスワード変更完了メールを送信
			$mail_params['name'] = $this->_login_user->name;
			UtilMail::send(
				NOTICE_EMAIL, 
				$this->_login_user->email , 
				MAIL_SUBJECT_MYPAGE_UPD_PASSWORD_FINISH,
				'mypage_upd_password',
				$mail_params
			);

		} catch (ValidationException $ve) {

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('passwordUpdForm');

		}

	}

	/**
	 * 会員情報変更フォームアクション
	 *
	 */
	public function myProfileUpdFormAction() {

		// ユーザ情報
		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$this->_view->assign('user', $user);
		
	}

	/**
	 * 会員情報変更確認アクション
	 *
	 */
	public function myProfileUpdConfirmAction() {

		$nick_name = $this->_request->getPost('nick_name');
		$name = $this->_request->getPost('name');
		$birth_day = $this->_request->getPost('birth_day');
		$tel = $this->_request->getPost('tel');

		$error_messages = array();

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		try {

			// ニックネーム : 未入力チェック
			if (UtilCommon::is_empty($nick_name))
				$error_messages['nick_name'] = sprintf(ERR_MSG_EMPTY, 'ニックネーム');		

			// ニックネーム : 文字数チェック
			if (!UtilCommon::is_length($nick_name, self::NICK_NAME_LENGTH))
				$error_messages['nick_name'] = sprintf(ERR_MSG_LENTGTH, 'ニックネーム', self::NICK_NAME_LENGTH);

			// 名前 : 未入力チェック
			if (UtilCommon::is_empty($name))
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '名前');		

			// 名前 : 文字数チェック
			if (!UtilCommon::is_length($nick_name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(NAME_LENGTH, '名前', self::NAME_LENGTH);

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
				throw new Exception(ERR_MSG_RETRY, __LINE__);	

			// 入力内容をセッションに保存
			$input_data['nick_name'] = $nick_name;
			$input_data['name'] = $name;
			$input_data['birth_day'] = $birth_day;
			$input_data['tel'] = $tel;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

			$this->_view->assign('nick_name', $nick_name);
			$this->_view->assign('name', $name);
			$this->_view->assign('birth_day', $birth_day);
			$this->_view->assign('tel', $tel);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('prefectures', PARAM_CONST_PREFECTURES);

			// フォームへ遷移
			$this->setAction('myProfileUpdForm');

		}

	}

	/**
	 * 会員情報変更完了アクション
	 *
	 */
	public function myProfileUpdFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');
		$auth_user = $model_session->get('auth_user');

		if (!$input_data)
			throw new Exception("Error Processing Request", 1);

		// ユーザ情報更新
		$dao_user = new DaoUser();
		$sets['name'] = $input_data['name'];
		$sets['birth_day'] = $input_data['birth_day'];
		$sets['tel'] = $input_data['tel'];
		$sets['nick_name'] = $input_data['nick_name'];
		$wheres['id'] = $this->_login_user->id;
		if (false === $dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// セッションの更新した内容に書き換える
		$user = $this->_login_user;
		$user->name = $input_data['name'];
		$user->birth_day = $input_data['birth_day'];
		$user->tel = $input_data['tel'];
		$model_session->set('user', $user);

		// セッションを削除する
		$model_session->close();

		$dao_user->commit();
		// $dao_user->rollback();

	}

	/**
	 * プロフィール（画像）変更フォームアクション
	 *
	 */
	public function myProfileImageUpdFormAction() {

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$this->_view->assign('user', $user);

	}

	/**
	 * 本人確認申請フォームアクション
	 *
	 */
	public function certFormAction() {

		// 存在チェック : ユーザ
		$dao_user = new DaoUser();
		$user = $dao_user->select_by_key($this->_login_user->id);
		if (!$user)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 認証済みチェック
		if ($user->is_cert)
			throw new Exception(ERR_MSG_CERTED, __LINE__);

		// 申請済みチェック
		$dao_user_cert = new DaoUserCert();
		if ($dao_user_cert->select_count(array('user_id' => $this->_login_user->id, 'is_certed' => 0)))
			throw new Exception(ERR_MSG_CERT_NOW, __LINE__);

	}

	/**
	 * 年齢認証申請確認アクション
	 *
	 */
	public function certConfirmAction() {

		$requests = $this->_request->getFiles();
		$files = array();

		try {

			$error_messages = array();

			if ($requests) {

				foreach ($requests as $key => $request) {

					switch ($request['error']) {

						case 4: // ファイルはアップロードされませんでした。
							// アップロード無しも該当するので正常系とする
							break;

						case 1: // アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
						case 2: // アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
						case 3: // アップロードされたファイルは一部のみしかアップロードされていません。
						case 6: // テンポラリフォルダがありません。
						case 7: // ディスクへの書き込みに失敗しました。
						case 8: // PHP の拡張モジュールがファイルのアップロードを中止しました
							$error_messages['file'] = sprintf(ERR_MSG_UPLOAD_FILE_FAILED, $request['error']);
							break;

						default:
							// 正常系
							break;
					}

					if (!$request['size'])
						continue;

					$keys = explode('_', $key);
					$files[] = $request;

				}

			}

			if (!count($files) && !$error_messages)
				$error_messages['file'] = sprintf(ERR_MSG_RQUIRED, 'ファイルアップロード');		

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception(ERR_MSG_RETRY, __LINE__);

			$upload_dir = SECURE_DIR . '/temp';
			$key = time() . rand();
			$upload_certs = array();

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();

			foreach ($files as $file) {
				$secure_temp_name = sprintf("%s_%07s.%s", date("YmdHis"), $this->_login_user->id, UtilFile::get_extension($file['name']));
				$upload_cert['secure_temp_name'] = $secure_temp_name;
				$upload_cert['type'] = exif_imagetype($file['tmp_name']);
				$upload_cert['data'] = file_get_contents($file['tmp_name']);
				$image_session_token = UtilCommon::random_str(20, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567') . $this->_login_user->id;
				$upload_cert['image_session_token'] = $image_session_token;
				$upload_certs[] = $upload_cert;
				$model_session->set('upload_certs', $upload_certs);

				UtilFile::upload_file($file['tmp_name'], $secure_temp_name, $upload_dir);				
			}

			foreach ($files as $key => $file) {
				
				$files[$key]['image_session_token'] = $image_session_token;

			}

			$model_session->set('files', $files);

			// $model_session->close();

			$this->_view->assign('files', $files[0]);

		} catch (Exception $e) {	

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('certForm');

		}		
	}

	/**
	 * 年齢認証申請完了アクション
	 *
	 */
	public function certFinishAction() {

		try {

			// セッションから登録値を取得
			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();

			// 本人確認証明書ファイルの設置
			$upload_temp_dir = SECURE_DIR . '/temp';

			$dao_user_cert = new DaoUserCert();
			$upload_certs = $model_session->get('upload_certs');
			if ($upload_certs) {

				// 資格証アップロード & データ登録
				foreach ($upload_certs as $upload_cert) {

					$file_secure_temp_name = $upload_temp_dir . '/' . $upload_cert['secure_temp_name'];
					$file = sprintf("%s_%07s.%s", date("YmdHis"), $this->_login_user->id, UtilFile::get_extension($file_secure_temp_name));
					$file_name = CERT_UPLOAD_DIR . '/' . $file;
					rename($file_secure_temp_name, $file_name);

					// アカウント証明書登録
					$entity_user_cert = new EntityUserCert();
					$entity_user_cert->id = null;
					$entity_user_cert->user_id = $this->_login_user->id;
					$entity_user_cert->image_file = $file;
					$entity_user_cert->is_certed = 0; // 認証前
					$user_cert_id = $dao_user_cert->insert($entity_user_cert);
					if (!$user_cert_id)
						throw new Exception(ERR_MSG_RETRY, __LINE__);

				}

			}
			
			// $model_session->close();

			// 申請者へメールを送信
			$params['nick_name'] = $this->_login_user->nick_name;
			UtilMail::send(NOTICE_EMAIL, $this->_login_user->email, MAIL_SUBJECT_MYPAGE_CERT, 'mypage_cert', $params);

			// 管理者へメールを送信
			$admin_params['user'] = $this->_login_user;
			$admin_params['age'] = UtilCommon::get_age(UtilCommon::date_to_year( $this->_login_user->birth_day), UtilCommon::date_to_month( $this->_login_user->birth_day), UtilCommon::date_to_day( $this->_login_user->birth_day));
			$admin_params['user_cert_id'] = $user_cert_id;
			UtilMail::send(NOTICE_EMAIL, ADMIN_EMAIL, MAIL_SUBJECT_MYPAGE_CERT_ADMIN, 'mypage_cert_admin', $admin_params);

		} catch (Exception $e) {

			echo $e->getMessage();

		}

	}

	/**
	 * 退会フォームアクション
	 *
	 */
	public function resignFormAction() {}

	/**
	 * 退会確認アクション
	 *
	 */
	public function resignConfirmAction() {

		$is_agree_1 = $this->_request->getPost('is_agree_1');

		$error_messages = array();

		try {

			// 未入力チェック : 「退会される際のご注意」のチェック
			if (UtilCommon::is_empty($is_agree_1))
				$error_messages['is_agree_1'] = sprintf(ERR_MSG_EMPTY, '「退会される際のご注意」のチェック');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new ValidationException("Error Processing Request", 1);

		} catch (ValidationException $e) {

			$this->_view->assign('error_messages', $error_messages);

			$this->setAction('resignForm');

		}

	}

	/**
	 * 退会完了アクション
	 *
	 */
	public function resignFinishAction() {

		$dao_user = new DaoUser();
		$dao_user->begin();

		// 存在チェックユーザ
		$user = $dao_user->select_by_key($this->_login_user->id);
		if (!$user)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// プロフィール画像削除
		if ($user->profile_image_file) {

			$profile_image_file_dir = sprintf("%s/%s", ASSETS_USER_DIR, $user->code);
			UtilFile::delete_file($user->profile_image_file, $profile_image_file_dir);

		}

		// 本人確認の証明画像を削除
		if ($user->is_cert) {

			$dao_user_cert = new DaoUserCert();
			$user_certs = $dao_user_cert->select_where(array('user_id' => $user->id));
			if ($user_certs) {

				foreach ((array)$user_certs as $user_cert) {
					UtilFile::delete_file($user_cert->image_file, CERT_UPLOAD_DIR);
				}

				if (!$dao_user_cert->delete(array(array('user_id' => $user->id))))
					throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			}

		}

		// ブログ情報削除


		// 会員情報削除（論理削除）
		$sets['email'] = "";
		$sets['password'] = "";
		$sets['name'] = "";
		$sets['birth_day'] = "";
		$sets['tel'] = "";
		$sets['prefecture_id'] = 0;
		$sets['address_1'] = "";
		$sets['address_2'] = "";
		$sets['address_3'] = "";
		$sets['nick_name'] = "";
		$sets['profile_image_file'] = null;
		$sets['status_type'] = USER_STATUS_TYPE_RESIGNED; // ステータスを退会に変更
		$sets['is_cert'] = 0;
		$wheres['id'] = $user->id;
		if (false === $dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		$dao_user->commit();

		// 退会手続き完了メールを送信
		$params['nick_name'] = $user->nick_name;
		UtilMail::send(NOTICE_EMAIL, $user->email, MAIL_SUBJECT_RESIGN_FINISH, 'resign_finish', $params);

		// ログアウト
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$model_session->destroy();

	}

	/**
	 * 出品一覧アクション
	 *
	 */
	public function productListAction() {

		$pg = $this->_request->getQuery('pg') ? $this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$status_type = $this->_request->getQuery('status_type');

		// 出品検索
		$model_product_search = new ModelProductSearch();
		$model_product_search->set_sell_user_id($this->_login_user->id);

		if ($status_type)
			$model_product_search->set_status_type($status_type);

		// 検索
		$model_product_search->set_sort_type(PARAM_PRODUCT_SEARCH_SORT_TYPE_NEW_ENTRY);
		$model_product_search->set_limit(self::MYPAGE_PRODUCT_PAGER_PER_PAGE);
		$model_product_search->set_offset($offset);
		$model_product_search->search();
		$products = $model_product_search->get();

		// 全件数
		$product_num = $model_product_search->get_num();

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::MYPAGE_PRODUCT_PAGER_PER_PAGE);
		$pager->set_total_rec($product_num);
		$pager->set_show_nav(self::MYPAGE_PRODUCT_PAGER_DELTA);
		$pager->set_query('sell_user_id', $this->_login_user->id);
		$pager->set_query('status_type', $status_type);
		$pager->set_path('/mypage/productList/');
		$pager->create();
// echo '<pre>';
// var_dump($products);
// echo '</pre>';
		$this->_view->assign('products', $products);
		$this->_view->assign('pager', $pager);
		$this->_view->assign('product_status_types', PARAM_CONST_PRODUCT_STATUS_TYPES);

	}

	/**
	 * 出品登録フォームアクション
	 *
	 */
	public function productAddFormAction() {

		$dao_category = new DaoCategory();
		$categories = $dao_category->select_all();

		$this->_view->assign('categories', $categories);

	}

	/**
	 * 出品登録確認アクション
	 *
	 */
	public function productAddConfirmAction() {

		$name = $this->_request->getPost('name');
		$category_id = $this->_request->getPost('category_id');
		$detail = $this->_request->getPost('detail');
		$price = $this->_request->getPost('price');

		$product_images = $this->_request->getFiles('product_images');
		$error_messages = array();

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$dao_category = new DaoCategory();
		$category = $dao_category->select_by_key($category_id);

		try {

			// 商品名 : 未入力チェック
			if (UtilCommon::is_empty($name))
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '商品名');		

			// 商品名 : 文字数チェック
			if (!UtilCommon::is_length($name, self::PRODUCT_NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '商品名', self::PRODUCT_NAME_LENGTH);

			// カテゴリ : 未入力チェック
			if (UtilCommon::is_empty($category_id))
				$error_messages['category_id'] = sprintf(ERR_MSG_EMPTY, 'カテゴリ');

			// カテゴリ : 範囲外チェック
			if (!$category)
				$error_messages['category_id'] = sprintf(ERR_MSG_NOT_FOUND, 'カテゴリ');	

			// 価格 : 数値チェック
			if (!UtilCommon::is_num($price, self::NAME_LENGTH))
				$error_messages['price'] = sprintf(ERR_MSG_NUM, '価格');	

			// 価格 : 限界値チェック
			if (self::OVER_PRICE < $price)
				$error_messages['price'] = sprintf(ERR_MSG_OVER_PRICE, '価格', number_format(self::OVER_PRICE));	

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception(ERR_MSG_RETRY, __LINE__);	

			// 入力内容をセッションに保存
			$input_data['name'] = $name;
			$input_data['category_id'] = $category_id;
			$input_data['detail'] = $detail;
			$input_data['price'] = $price;

			// 画像がアップロードされている場合は、一時領域へ設置する
			$tmp_product_images = array();
			$product_image_file_names = array();
			if (is_array($product_images['tmp_name']) && $product_images['tmp_name'][0]) {

				mt_srand(UtilCommon::make_seed());
				$image_prefix = date("YmdHis") . '_' . $this->_login_user->code . '_' . mt_rand();
				foreach ($product_images['tmp_name'] as $key => $product_image_tmp_name) {
					$product_image_file_name = $image_prefix . '_' . sprintf("%03d", $key+1) . '.' . UtilFile::get_extension($product_images['name'][$key]);
					UtilFile::upload_file($product_image_tmp_name, $product_image_file_name, ASSETS_TMP_DIR);
					$tmp_dir_product_images[] = ASSETS_TMP_DIR . '/' . $product_image_file_name;
					$tmp_src_product_images[] = ASSETS_TMP_SRC . '/' . $product_image_file_name;
					$product_image_file_names[] = $product_image_file_name;
					$tmp_names[] = $product_image_tmp_name;
				}

				$input_data['tmp_dir_product_images'] = $tmp_dir_product_images;
				$input_data['tmp_src_product_images'] = $tmp_src_product_images;
				$input_data['product_image_file_names'] = $product_image_file_names;
				$input_data['tmp_names'] = $tmp_names;

				$this->_view->assign('tmp_src_product_images', $tmp_src_product_images);

			}

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

			$this->_view->assign('name', $name);
			$this->_view->assign('category', $category);
			$this->_view->assign('detail', $detail);
			$this->_view->assign('price', $price);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);

			if (isset($dao_category))
				$dao_category = new DaoCategory();

			$categories = $dao_category->select_all();
			$this->_view->assign('categories', $categories);

			// フォームへ遷移
			$this->setAction('productAddForm');

		}

	}

	/**
	 * 出品登録完了アクション
	 *
	 */
	public function productAddFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		if (!$input_data)
			throw new Exception("Error Processing Request", 1);

		// 出品商品登録
		$dao_product = new DaoProduct();
		$dao_product->begin();

		$entity_product = new EntityProduct();
		$entity_product->name = $input_data['name'];
		$entity_product->code = "";
		$entity_product->detail = $input_data['detail'];
		$entity_product->price = $input_data['price'];
		$entity_product->category_id = $input_data['category_id'];
		$entity_product->sell_user_id = $this->_login_user->id;
		$entity_product->status_type = PARAM_CONST_PRODUCT_STATUS_TYPE_ON_SALE;
		$product_id = $dao_product->insert($entity_product);
		if (!$product_id)
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// 品番を発行する
		$product_code = UtilCommon::gererate_product_code($product_id);

		$sets['code'] = $product_code;
		$wheres['id'] = $product_id;
		if (false === $dao_product->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// 出品商品画像登録
		if (isset($input_data['tmp_names'])) {

			$dao_product_image = new DaoProductImage();
			$assets_product_dir = ASSETS_PRODUCT_DIR . '/' . $product_code;
			foreach ((array)$input_data['tmp_dir_product_images'] as $key => $tmp_dir_product_image) {

				// ディレクトリがなければ作成する
				if (!is_dir($assets_product_dir)) {
					
					if (!mkdir($assets_product_dir, 0777))
						throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);
				
				}

				// ファイルを公開領域へ移動させる
				$product_image_file_name = sprintf("%s_%03d.%s", $product_code, $key+1, UtilFile::get_extension($input_data['product_image_file_names'][$key]));
				rename($tmp_dir_product_image, $assets_product_dir . '/' . $product_image_file_name);

				// ファイルをリサイズする
				$image = new Imagick($assets_product_dir . '/' . $product_image_file_name);
				$image->resizeImage(self::UPLOAD_PRODUCT_IMAGE_WIDTH, 0, imagick::FILTER_MITCHELL, 1);
				$image->writeImage($assets_product_dir . '/' . $product_image_file_name);
				$image->destroy();

				$entity_product_image = new EntityProductImage();
				$entity_product_image->product_id = $product_id;
				$entity_product_image->file_name = $product_image_file_name;
				if (!$dao_product_image->insert($entity_product_image))
					throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			}

		}

		// セッションを削除する
		$model_session->unset('input_data');
		$model_session->close();

		$dao_product->commit();
		// $dao_product->rollback();

	}

	/**
	 * 出品更新フォームアクション
	 *
	 */
	public function productUpdFormAction() {

		$id = $this->_request->getQuery('id');

		// 出品存在 & 自身の出品チェック
		$model_product = new ModelProduct($id);
		$product = $model_product->get();
		if (!$product || $product->sell_user->id != $this->_login_user->id)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '出品'), __LINE__);	

		$dao_category = new DaoCategory();
		$categories = $dao_category->select_all();

		// 出品ステータス（出品停止は除外する）
		$status_types = PARAM_CONST_PRODUCT_STATUS_TYPES;
		unset($status_types[PARAM_CONST_PRODUCT_STATUS_TYPE_SOLD_OUT]);
		unset($status_types[PARAM_CONST_PRODUCT_STATUS_TYPE_STOP]);

		$this->_view->assign('categories', $categories);
		$this->_view->assign('status_types', $status_types);
		$this->_view->assign('product', $product);

	}

	/**
	 * 出品更新確認アクション
	 *
	 */
	public function productUpdConfirmAction() {

		$name = $this->_request->getPost('name');
		$category_id = $this->_request->getPost('category_id');
		$detail = $this->_request->getPost('detail');
		$price = $this->_request->getPost('price');
		$status_type = $this->_request->getPost('status_type');

		$id = $this->_request->getPost('id');

		$product_images = $this->_request->getFiles('product_images');
		$error_messages = array();

		$dao_category = new DaoCategory();
		$category = $dao_category->select_by_key($category_id);

		// 出品存在 & 自身の出品チェック
		$model_product = new ModelProduct($id);
		$product = $model_product->get();
		if (!$product || $product->sell_user->id != $this->_login_user->id)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '出品'), __LINE__);	

		try {

			// 商品名 : 未入力チェック
			if (UtilCommon::is_empty($name))
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '商品名');		

			// 商品名 : 文字数チェック
			if (!UtilCommon::is_length($name, self::PRODUCT_NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '商品名', self::PRODUCT_NAME_LENGTH);

			// カテゴリ : 未入力チェック
			if (UtilCommon::is_empty($category_id))
				$error_messages['category_id'] = sprintf(ERR_MSG_EMPTY, 'カテゴリ');

			// カテゴリ : 範囲外チェック
			if (!$category)
				$error_messages['category_id'] = sprintf(ERR_MSG_NOT_FOUND, 'カテゴリ');	

			// 価格 : 数値チェック
			if (!UtilCommon::is_num($price, self::NAME_LENGTH))
				$error_messages['price'] = sprintf(ERR_MSG_NUM, '価格');	

			// 価格 : 限界値チェック
			if (self::OVER_PRICE < $price)
				$error_messages['price'] = sprintf(ERR_MSG_OVER_PRICE, '価格', number_format(self::OVER_PRICE));

			// 出品ステータス : 範囲外チェック
			if (
				PARAM_CONST_PRODUCT_STATUS_TYPE_NOT_SALE != $status_type 
				&& PARAM_CONST_PRODUCT_STATUS_TYPE_ON_SALE!= $status_type 
			)
				$error_messages['category_id'] = sprintf(ERR_MSG_NOT_FOUND, 'ステータス');	

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception(ERR_MSG_RETRY, __LINE__);	

			// 入力内容をセッションに保存
			$input_data['name'] = $name;
			$input_data['category_id'] = $category_id;
			$input_data['detail'] = $detail;
			$input_data['price'] = $price;
			$input_data['status_type'] = $status_type;
			$input_data['code'] = $product->code;
			$input_data['product_image_seq_no'] = is_array($product->product_images) ? count($product->product_images) : 0;
			$input_data['id'] = $id;

			// 画像がアップロードされている場合は、一時領域へ設置する
			$tmp_product_images = array();
			$tmp_src_product_images = array();
			$product_image_file_names = array();
			if (is_array($product_images['tmp_name']) && $product_images['tmp_name'][0]) {

				mt_srand(UtilCommon::make_seed());
				$image_prefix = date("YmdHis") . '_' . $this->_login_user->code . '_' . mt_rand();
				foreach ($product_images['tmp_name'] as $key => $product_image_tmp_name) {
					$product_image_file_name = $image_prefix . '_' . sprintf("%03d", $key+1) . '.' . UtilFile::get_extension($product_images['name'][$key]);
					UtilFile::upload_file($product_image_tmp_name, $product_image_file_name, ASSETS_TMP_DIR);
					$tmp_dir_product_images[] = ASSETS_TMP_DIR . '/' . $product_image_file_name;
					$tmp_src_product_images[] = ASSETS_TMP_SRC . '/' . $product_image_file_name;
					$product_image_file_names[] = $product_image_file_name;
					$tmp_names[] = $product_image_tmp_name;
				}

				$input_data['tmp_dir_product_images'] = $tmp_dir_product_images;
				$input_data['tmp_src_product_images'] = $tmp_src_product_images;
				$input_data['product_image_file_names'] = $product_image_file_names;
				$input_data['tmp_names'] = $tmp_names;

			}

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

			$this->_view->assign('name', $name);
			$this->_view->assign('category', $category);
			$this->_view->assign('detail', $detail);
			$this->_view->assign('price', $price);
			$this->_view->assign('product_images', $product->product_images);
			$this->_view->assign('tmp_src_product_images', $tmp_src_product_images);
			$this->_view->assign('status_type_name', PARAM_CONST_PRODUCT_STATUS_TYPES[$status_type]);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);

			if (isset($dao_category))
				$dao_category = new DaoCategory();

			$categories = $dao_category->select_all();

			$status_types = PARAM_CONST_PRODUCT_STATUS_TYPES;
			unset($status_types[PARAM_CONST_PRODUCT_STATUS_TYPE_SOLD_OUT]);
			unset($status_types[PARAM_CONST_PRODUCT_STATUS_TYPE_STOP]);

			$this->_view->assign('status_types', $status_types);
			$this->_view->assign('categories', $categories);
			$this->_view->assign('product', $product);

			// フォームへ遷移
			$this->setAction('productUpdForm');

		}

	}

	/**
	 * 出品更新完了アクション
	 *
	 */
	public function productUpdFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		if (!$input_data)
			throw new Exception("Error Processing Request", 1);

		// 出品商品登録
		$dao_product = new DaoProduct();
		$dao_product->begin();

		$sets['name'] = $input_data['name'];
		$sets['detail'] = $input_data['detail'];
		$sets['price'] = $input_data['price'];
		$sets['category_id'] = $input_data['category_id'];
		$sets['status_type'] = $input_data['status_type'];
		$wheres['id'] = $input_data['id'];
		if (false === $dao_product->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// 出品商品画像登録
		if (isset($input_data['tmp_names'])) {

			$dao_product_image = new DaoProductImage();
			$assets_product_dir = ASSETS_PRODUCT_DIR . '/' . $input_data['code'];
			$product_image_seq_no = $input_data['product_image_seq_no'];
			foreach ((array)$input_data['tmp_dir_product_images'] as $key => $tmp_dir_product_image) {

				// ディレクトリがなければ作成する
				if (!is_dir($assets_product_dir)) {
					
					if (!mkdir($assets_product_dir, 0777))
						throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);
				
				}

				// ファイルを公開領域へ移動させる
				$product_image_seq_no++;
				$product_image_file_name = sprintf("%s_%03d.%s", $input_data['code'], $product_image_seq_no, UtilFile::get_extension($input_data['product_image_file_names'][$key]));
				rename($tmp_dir_product_image, $assets_product_dir . '/' . $product_image_file_name);
				
				// ファイルをリサイズする
				$image = new Imagick($assets_product_dir . '/' . $product_image_file_name);
				$image->resizeImage(self::UPLOAD_PRODUCT_IMAGE_WIDTH, 0, imagick::FILTER_MITCHELL, 1);
				$image->writeImage($assets_product_dir . '/' . $product_image_file_name);
				$image->destroy();

				$entity_product_image = new EntityProductImage();
				$entity_product_image->product_id = $input_data['id'];
				$entity_product_image->file_name = $product_image_file_name;
				if (!$dao_product_image->insert($entity_product_image))
					throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			}

		}

		// セッションを削除する
		$model_session->unset('input_data');
		$model_session->close();

		$dao_product->commit();
		// $dao_product->rollback();

	}

	/**
	 * 出品削除確認アクション
	 *
	 */
	public function productDelConfirmAction() {

		$id = $this->_request->getQuery('id');

		// 出品存在 & 自身の出品チェック
		$model_product = new ModelProduct($id);
		$product = $model_product->get();
		if (!$product || $product->sell_user->id != $this->_login_user->id)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '出品'), __LINE__);

		// 出品ステータスチェック
		if (PARAM_CONST_PRODUCT_STATUS_TYPE_NOT_SALE != $product->status_type)
			throw new Exception(ERR_MSG_DELETE_NOT_SALE_ONLY, __LINE__);

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$model_session->set('del_product_id', $id);

		$this->_view->assign('product', $product);

	}

	/**
	 * 出品削除完了アクション
	 *
	 */
	public function productDelFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$del_product_id = $model_session->get('del_product_id');

		if (!$del_product_id)
			throw new Exception("Error Processing Request", 1);

		$model_product = new ModelProduct($del_product_id);
		$product = $model_product->get();

		// 出品削除
		$dao_product = new DaoProduct();
		$dao_product->begin();

		$wheres['id'] = $del_product_id;
		if (false === $dao_product->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		// 出品画像データ削除
		$wheres = null;
		$wheres['product_id'] = $del_product_id;
		$dao_product_image = new DaoProductImage();
		if (false === $dao_product_image->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		// 出品画像ファイル削除
		$assets_product_dir = ASSETS_PRODUCT_DIR . '/' . $product->code . '/';
		if (is_dir($assets_product_dir)) {

			// 画像削除
			foreach ((array)$product->product_images as $product_image) {

				if (false === UtilFile::delete_file($product_image->file_name, $assets_product_dir))
					throw new Exception(ERR_MSG_RETRY, __LINE__);			
			
			}

			// ディレクトリ削除
			if (false === rmdir($assets_product_dir))
				throw new Exception(ERR_MSG_RETRY, __LINE__);		

		}

		// セッションを削除する
		$model_session->unset('del_product_id');
		$model_session->close();

		$dao_product->commit();
		// $dao_product->rollback();

	}

}


