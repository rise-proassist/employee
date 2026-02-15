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

	private $_login_user;

	const NAME_LENGTH = 32;

	const NAME_KANA_LENGTH = 32;

	const TEL_LENGTH = 16;

	const EMAIL_LENGTH = 64;

	const PASSWORD_LENGTH = 16;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		// サイトタイトル（ヘッダ）
		$this->_view->assign('site_title', '新規従事者の登録');

	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {

		throw new Exception("リクエストされたページは存在しません。", 1);	

	}

	/**
	 * 言語選択フォームアクション
	 *
	 */
	public function langFormAction() {

		$this->_view->assign('lang_types', PARAM_CONST_ALL_LANG_TYPES);

	}

	/**
	 * 新規会員登録フォームアクション
	 *
	 */
	public function registFormAction() {

		$lang_type = $this->_request->getPost('lang_type');

		$error_messages = array();

		try {

			// 氏名 : 未入力チェック
			if (UtilCommon::is_empty($lang_type))
				$error_messages['lang_type'] = sprintf(ERR_MSG_EMPTY, '言語') . '<br>' . sprintf(ERR_MSG_EMPTY_EN, 'Language');

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);

			$input_data['lang_type'] = $lang_type;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

			// 性別種別
			switch ($lang_type) {
				case PARAM_CONST_LANG_TYPE_EN:
					$gender_types = PARAM_CONST_EN_GENDER_TYPES;
					break;

				case PARAM_CONST_LANG_TYPE_JP:
				default:
					$gender_types = PARAM_CONST_GENDER_TYPES;
					break;
			}

			// 雇用形態
			switch ($lang_type) {
				case PARAM_CONST_LANG_TYPE_EN:
					$employ_types = PARAM_CONST_EN_EMPLOY_TYPES;
					break;

				case PARAM_CONST_LANG_TYPE_JP:
				default:
					$employ_types = PARAM_CONST_EMPLOY_TYPES;
					break;
			}

			// 国籍マスタ
			$dao_country = new DaoCountry();
			$countries = $dao_country->select_all();

			// 所属会社マスタ
			$dao_company = new DaoCompany();
			$companies = $dao_company->select_all();

			$this->_view->assign('gender_types', $gender_types);
			$this->_view->assign('employ_types', $employ_types);
			$this->_view->assign('countries', $countries);
			$this->_view->assign('companies', $companies);
			$this->_view->assign('lang_type', $lang_type);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('lang_types', PARAM_CONST_ALL_LANG_TYPES);	

			// フォームへ遷移
			$this->setAction('langForm');

		}		


	}

	/**
	 * 新規会員登録フォームアクション
	 *
	 */
	public function registCertFormAction() {

		$name = $this->_request->getPost('name');
		$name_kana = $this->_request->getPost('name_kana');
		$tel = $this->_request->getPost('tel');
		$email = $this->_request->getPost('email');
		$password = $this->_request->getPost('password');
		$re_password = $this->_request->getPost('re_password');
		$post_code = $this->_request->getPost('post_code');
		$address = $this->_request->getPost('address');
		$gender_type = $this->_request->getPost('gender_type');
		$birth_day = $this->_request->getPost('birth_day');
		$country_id = $this->_request->getPost('country_id');
		$employ_type = $this->_request->getPost('employ_type');
		$company_id = $this->_request->getPost('company_id');
		$etc_company_name = $this->_request->getPost('etc_company_name');

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data'); // $input_data['lang_type']

		$error_messages = array();

		try {

			// 氏名 : 未入力チェック
			if (UtilCommon::is_empty($name)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name'] = sprintf(ERR_MSG_EMPTY_EN, 'Name');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '氏名');
						break;
				}
			}

			// 氏名 : 文字数チェック
			if (!UtilCommon::is_length($name, self::NAME_LENGTH)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name'] = sprintf(ERR_MSG_LENTGTH_EN, 'Name', self::NAME_LENGTH);
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '氏名', self::NAME_LENGTH);
						break;
				}
			}

			// 氏名（ふりがな） : 未入力チェック
			// if (UtilCommon::is_empty($name_kana)) {
			// 	switch ($input_data['lang_type']) {
			// 		case PARAM_CONST_LANG_TYPE_EN:
			// 			$error_messages['name_kana'] = sprintf(ERR_MSG_EMPTY_EN, 'Frigana');
			// 			break;

			// 		case PARAM_CONST_LANG_TYPE_JP:
			// 		default:
			// 			$error_messages['name_kana'] = sprintf(ERR_MSG_EMPTY, 'ふりがな');
			// 			break;
			// 	}
			// }

			// 氏名（ふりがな） : 文字数チェック
			if ($name_kana && !UtilCommon::is_length($name_kana, self::NAME_KANA_LENGTH)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name_kana'] = sprintf(ERR_MSG_LENTGTH_EN, 'Frigana', self::NAME_KANA_LENGTH);
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name_kana'] = sprintf(ERR_MSG_LENTGTH, '氏名（ふりがな）', self::NAME_KANA_LENGTH);
				}
			}

			// 氏名（ふりがな） : ひらがな以外入力チェック
			if ($name_kana && !UtilCommon::is_hira_kata_alpha($name_kana)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name_kana'] = sprintf(ERR_MSG_HIRAGANA_EN, 'Frigana');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name_kana'] = sprintf(ERR_MSG_HIRAGANA, '氏名（ふりがな）');
				}
			}

			// 電話番号 : 未入力チェック
			if (UtilCommon::is_empty($tel)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['tel'] = sprintf(ERR_MSG_EMPTY_EN, 'Telephone number');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['tel'] = sprintf(ERR_MSG_EMPTY, '電話番号');
				}
			}

			// 電話番号 : 文字数チェック
			if (!UtilCommon::is_length($tel, self::TEL_LENGTH)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['tel'] = sprintf(ERR_MSG_LENTGTH_EN, 'Telephone number', self::TEL_LENGTH);
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['tel'] = sprintf(ERR_MSG_LENTGTH, '電話番号', self::TEL_LENGTH);
				}
			}

			// 電話番号 : 数値チェック
			if (!UtilCommon::is_num($tel)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['tel'] = sprintf(ERR_MSG_NUM_EN, 'Telephone number');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['tel'] = sprintf(ERR_MSG_NUM, '電話番号');
				}
			}

			// メールアドレス : 未入力チェック
			if (UtilCommon::is_empty($email)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['email'] = sprintf(ERR_MSG_EMPTY_EN, 'Email');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');
						break;
				}
			}

			// メールアドレス : 文字数チェック
			if ($email && !UtilCommon::is_length($email, self::EMAIL_LENGTH)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['email'] = sprintf(ERR_MSG_LENTGTH_EN, 'Email', self::EMAIL_LENGTH);
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['email'] = sprintf(ERR_MSG_LENTGTH, 'メールアドレス', self::EMAIL_LENGTH);
				}
			}

			// メールアドレス : 形式チェック
			if ($email && !UtilCommon::is_mail($email)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['email'] = sprintf(ERR_MSG_INVALID_EN, 'Email');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['email'] = sprintf(ERR_MSG_INVALID, 'メールアドレス');
				}
			}

			// メールアドレス : 重複チェック
			if ($email) {
				$dao_user = new DaoUser();
				$user = $dao_user->select_where_one(array('email' => $email));
				if ($user) {
					switch ($input_data['lang_type']) {
						case PARAM_CONST_LANG_TYPE_EN:
							$error_messages['email'] = sprintf(ERR_MSG_DUPLICATE_EN, 'Email');
							break;

						case PARAM_CONST_LANG_TYPE_JP:
						default:
							$error_messages['email'] = sprintf(ERR_MSG_DUPLICATE, 'メールアドレス');
					}
				}
			}

			// パスワード : 未入力チェック
			if (UtilCommon::is_empty($password)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['password'] = sprintf(ERR_MSG_EMPTY_EN, 'Password');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');
						break;
				}		
			}

			// パスワード : 文字数チェック
			if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['password'] = sprintf(ERR_MSG_LENTGTH_EN, 'Password', self::PASSWORD_LENGTH);
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード', self::PASSWORD_LENGTH);
						break;
				}	
			}

			// パスワード（再入力） : 未入力チェック
			if (UtilCommon::is_empty($re_password)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY_EN, 'Re-enter Password');	
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, 'パスワード（再入力）');	
						break;
				}		
			}

			// パスワード（再入力） : 文字数チェック
			if (!UtilCommon::is_length($re_password, self::PASSWORD_LENGTH)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH_EN, 'Re-enter Password', self::PASSWORD_LENGTH);
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード（再入力）', self::PASSWORD_LENGTH);
						break;
				}	
			}

			// パスワード（再入力）: 不一致チェック
			if ($re_password != $password) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME_EN,'Password', 'Re-enter Password', self::PASSWORD_LENGTH);;
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME,'パスワード', 'パスワード（再入力）', self::PASSWORD_LENGTH);
						break;
				}
			}

			// 郵便番号
			if (UtilCommon::is_empty($post_code)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['post_code'] = sprintf(ERR_MSG_EMPTY_EN, 'Post code');	
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['post_code'] = sprintf(ERR_MSG_EMPTY, '郵便番号');	
						break;
				}	
			}

			// 住所
			if (UtilCommon::is_empty($address)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['address'] = sprintf(ERR_MSG_EMPTY_EN, 'Address');	
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['address'] = sprintf(ERR_MSG_EMPTY, '住所');	
						break;
				}	
			}

			// 性別 : 未入力チェック
			if (UtilCommon::is_empty($gender_type)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['gender_type'] = sprintf(ERR_MSG_EMPTY_EN, 'Sex');	
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['gender_type'] = sprintf(ERR_MSG_EMPTY, '性別');	
						break;
				}
			}

			// 性別 : 規定値外入力チェック
			if (!isset(PARAM_CONST_GENDER_TYPES[$gender_type])) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['gender_id'] = sprintf(ERR_MSG_INPUT_EN, 'Sex');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['gender_id'] = sprintf(ERR_MSG_INPUT, '性別');
						break;
				}
			}

			// 生年月日 : 未入力チェック
			if (UtilCommon::is_empty($birth_day)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['birth_day'] = sprintf(ERR_MSG_EMPTY_EN, 'Birthday');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['birth_day'] = sprintf(ERR_MSG_EMPTY, '生年月日');
						break;
				}
			}

			// 生年月日 : 未来日チェック
			if ($birth_day > date('Y-m-d')) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['birth_day'] = sprintf(ERR_MSG_INVALID_EN, 'Birthday');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['birth_day'] = sprintf(ERR_MSG_INVALID, '生年月日');
						break;
				}
			}

			// 国籍 : 未入力チェック
			if (!mb_strlen($country_id)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['country_id'] = sprintf(ERR_MSG_EMPTY_EN, 'Nationality');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['country_id'] = sprintf(ERR_MSG_EMPTY, '国籍');
						break;
				}
			}

			// 国籍 : 規定値外入力チェック
			$dao_country = new DaoCountry();
			$country = $dao_country->select_by_key($country_id);
			if (!$country) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['country_id'] = sprintf(ERR_MSG_INPUT_EN, 'Nationality');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['country_id'] = sprintf(ERR_MSG_INPUT, '国籍');	
						break;
				}
			}

			// 雇用形態 : 未入力チェック
			if (!mb_strlen($employ_type)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['employ_type'] = sprintf(ERR_MSG_EMPTY_EN, 'Employment');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['employ_type'] = sprintf(ERR_MSG_EMPTY, '雇用形態');
						break;
				}
			}

			// 雇用形態 : 規定値外入力チェック
			if (!isset(PARAM_CONST_EMPLOY_TYPES[$employ_type])) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['employ_type'] = sprintf(ERR_MSG_INPUT_EN, 'Employment');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['employ_type'] = sprintf(ERR_MSG_INPUT, '雇用形態');	
						break;
				}
			}

			// 所属会社 : 未入力チェック
			if (!mb_strlen($company_id)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['company_id'] = sprintf(ERR_MSG_EMPTY_EN, 'Affiliated company');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['company_id'] = sprintf(ERR_MSG_EMPTY, '所属会社');
						break;
				}
			}

			// 所属会社 : 規定値外入力チェック
			$dao_company = new DaoCompany();
			$company = $dao_company->select_by_key($company_id);
			if (!$company_id && !$company && UtilCommon::is_empty($etc_company_name)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['company_id'] = sprintf(ERR_MSG_INPUT_EN, 'Affiliated company');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['company_id'] = sprintf(ERR_MSG_INPUT, '所属会社');	
						break;
				}
			}

			// 所属会社（その他）
			if (!$company_id && UtilCommon::is_empty($etc_company_name)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['etc_company_name'] = sprintf(ERR_MSG_EMPTY_EN, 'Affiliated company（Other）');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['etc_company_name'] = sprintf(ERR_MSG_EMPTY, '所属会社（その他）');
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$input_data['name'] = $name;
			$input_data['name_kana'] = $name_kana;
			$input_data['tel'] = $tel;
			$input_data['email'] = $email;
			$input_data['password'] = $password;
			$input_data['post_code'] = $post_code;
			$input_data['address'] = $address;
			$input_data['gender_type'] = $gender_type;
			$input_data['birth_day'] = $birth_day;
			$input_data['country_id'] = $country_id;
			$input_data['employ_type'] = $employ_type;
			$input_data['company_id'] = $company_id;
			switch ($input_data['lang_type']) {
				case PARAM_CONST_LANG_TYPE_EN:
					$input_data['gender_type_name'] = PARAM_CONST_EN_GENDER_TYPES[$gender_type];
					$input_data['employ_type_name'] = PARAM_CONST_EN_EMPLOY_TYPES[$employ_type];
					break;

				case PARAM_CONST_LANG_TYPE_JP:
				default:
					$input_data['gender_type_name'] = PARAM_CONST_GENDER_TYPES[$gender_type];
					$input_data['employ_type_name'] = PARAM_CONST_EMPLOY_TYPES[$employ_type];
					break;
			}
			$input_data['country_name'] = $country->name;
			$input_data['company_name'] = isset($company) ? $company->name : null;
			$input_data['etc_company_name'] = $etc_company_name;

			// $model_session = new ModelSession();
			// $model_session->set_dir(SESSION_DIR);
			// $model_session->open();
			$model_session->set('input_data', $input_data);

			// $model_session->close();
		
			// 資格者証マスタ
			$dao_cert = new DaoCert();
			$certs = $dao_cert->select_all();

			$this->_view->assign('certs', $certs);
			$this->_view->assign('lang_type', $input_data['lang_type']);

		} catch (Exception $e) {

			// 国籍マスタ
			if (!isset($dao_country))
				$dao_country = new DaoCountry();

			$countries = $dao_country->select_all();

			// 所属会社マスタ
			if (!isset($dao_company))
				$dao_company = new DaoCompany();

			$companies = $dao_company->select_all();

			// 性別種別
			switch ($input_data['lang_type']) {
				case PARAM_CONST_LANG_TYPE_EN:
					$gender_types = PARAM_CONST_EN_GENDER_TYPES;
					break;

				case PARAM_CONST_LANG_TYPE_JP:
				default:
					$gender_types = PARAM_CONST_GENDER_TYPES;
					break;
			}

			// 雇用形態
			switch ($input_data['lang_type']) {
				case PARAM_CONST_LANG_TYPE_EN:
					$employ_types = PARAM_CONST_EN_EMPLOY_TYPES;
					break;

				case PARAM_CONST_LANG_TYPE_JP:
				default:
					$employ_types = PARAM_CONST_EMPLOY_TYPES;
					break;
			}
			$this->_view->assign('gender_types', $gender_types);	
			$this->_view->assign('employ_types', $employ_types);	
			$this->_view->assign('countries', $countries);	
			$this->_view->assign('companies', $companies);	
			$this->_view->assign('lang_type', $input_data['lang_type']);	

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('registForm');

		}	

	}

	/**
	 * 新規会員登録同意書1アクション
	 *
	 */
	public function registAgree1Action() {
		
		$requests = $this->_request->getFiles();
		$files = array();

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

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
					$request['cert_id'] = $keys[1];
					$request['cert_type'] = $keys[2];
					$files[] = $request;

				}

			}

			if (!count($files) && !count($error_messages)) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['file'] = sprintf(ERR_MSG_RQUIRED_EN, 'Any file upload');	
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['file'] = sprintf(ERR_MSG_RQUIRED, 'いずれかのファイルアップロード');	
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);

			$upload_dir = SECURE_DIR . '/temp';
			$key = time() . rand();
			$upload_certs = array();

			foreach ($files as $file) {
				$upload_cert['cert_id'] = $file['cert_id'];
				$upload_cert['cert_type'] = $file['cert_type'];
				$secure_temp_name = sprintf("%s_%s_%s.%s", $key, $file['cert_id'], $file['cert_type'], UtilFile::get_extension($file['name']));
				$upload_cert['secure_temp_name'] = $secure_temp_name;
				$upload_cert['type'] = exif_imagetype($file['tmp_name']);
				$upload_cert['data'] = file_get_contents($file['tmp_name']);
				$upload_certs[] = $upload_cert;
				$model_session->set('upload_certs', $upload_certs);

				UtilFile::upload_file($file['tmp_name'], $secure_temp_name, $upload_dir);				
			}

			// 資格者証マスタ
			$dao_cert = new DaoCert();
			$certs = $dao_cert->select_all();

			foreach ($files as $key => $file) {

				foreach ($certs as $cert) {
					
					if ($file['cert_id'] == $cert->id)
						$files[$key]['cert_name'] = sprintf("%s（%s）", $cert->name, PARAM_CONST_CERT_TYPES[$file['cert_type']]);

				}

			}

			$model_session->set('files', $files);
			$this->_view->assign('lang_type', $input_data['lang_type']);

			// $model_session->close();

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);

			// 資格者証マスタ
			$dao_cert = new DaoCert();
			$certs = $dao_cert->select_all();

			$this->_view->assign('certs', $certs);
			$this->_view->assign('lang_type', $input_data['lang_type']);

			// フォームへ遷移
			$this->setAction('registCertForm');

		}	

	}

	/**
	 * 新規会員登録同意書2アクション
	 *
	 */
	public function registAgree2Action() {

		$is_agree = $this->_request->getPost('is_agree');

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		$error_messages = array();

		try {

			// 同意する : 未入力チェック
			if (!$is_agree) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['is_agree'] = sprintf(ERR_MSG_CONSENT_EN, 'the above');	
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['is_agree'] = sprintf(ERR_MSG_CONSENT, '上記');
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$this->_view->assign('lang_type', $input_data['lang_type']);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('lang_type', $input_data['lang_type']);
			
			// 同意書1へ遷移
			$this->setAction('registAgree1');

		}

	}

	/**
	 * 新規会員登録同意書3アクション
	 *
	 */
	public function registAgree3Action() {

		$is_agree = $this->_request->getPost('is_agree');

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		$error_messages = array();

		try {

			// 同意する : 未入力チェック
			if (!$is_agree) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['is_agree'] = sprintf(ERR_MSG_CONSENT_EN, 'the above');	
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['is_agree'] = sprintf(ERR_MSG_CONSENT, '上記');
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$this->_view->assign('input_data', $input_data);
			$this->_view->assign('lang_type', $input_data['lang_type']);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('lang_type', $input_data['lang_type']);
			
			// 同意書2へ遷移
			$this->setAction('registAgree2');

		}

	}

	/**
	 * 新規会員登録確認アクション
	 *
	 */
	public function registConfirmAction() {

		$is_agree = $this->_request->getPost('is_agree');

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		$error_messages = null;

		try {

			// 同意する : 未入力チェック
			if (!$is_agree) {
				switch ($input_data['lang_type']) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['is_agree'] = sprintf(ERR_MSG_CONSENT_EN, 'Non-Disclosure Agreement (NDA)');
						break;

					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['is_agree'] = sprintf(ERR_MSG_CONSENT, '秘密保持契約書（NDA）');
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$files = $model_session->get('files');

			$this->_view->assign('input_data', $input_data);
			$this->_view->assign('files', $files);
			$this->_view->assign('lang_type', $input_data['lang_type']);

		} catch (Exception $e) {

			$this->_view->assign('input_data', $input_data);
			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('lang_type', $input_data['lang_type']);
			
			// 同意書3へ遷移
			$this->setAction('registAgree3');

		}

	}

	/**
	 * 新規会員登録確認アクション
	 *
	 */
	public function registFinishAction() {

		// セッションから登録値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		try {

			if (!$input_data)
				throw new Exception("Error Processing Request", 1);

			// アカウント登録
			$dao_user = new DaoUser();
			$dao_user->begin();

			$entity_user = new EntityUser();
			$entity_user->id = null;
			$entity_user->code = ""; // とりあえず空文字
			$entity_user->status_type = PARAM_CONST_USER_STATUS_TYPE_ENABLE;
			$entity_user->approve_type = PARAM_CONST_USER_APPROVE_TYPE_WAIT;
			$entity_user->name = $input_data['name'];
			$entity_user->name_kana = $input_data['name_kana'];
			$entity_user->tel = $input_data['tel'];
			$entity_user->email = $input_data['email'];
			$entity_user->password = UtilCommon::to_hash_password($input_data['password']);
			$entity_user->post_code = $input_data['post_code'];
			$entity_user->address = $input_data['address'];
			$entity_user->gender_type = $input_data['gender_type'];
			$entity_user->birth_day = $input_data['birth_day'];
			$entity_user->country_id = $input_data['country_id'];
			$entity_user->employ_type = $input_data['employ_type'];
			$entity_user->company_id = $input_data['company_id'];
			$entity_user->etc_company_name = $input_data['etc_company_name'];
			$entity_user->lang_type =  $input_data['lang_type'];
			if(!$last_user_id = $dao_user->insert($entity_user))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 従事者コードを発行する
			$code = UtilCommon::gererate_user_code($last_user_id);
			$sets['code'] = $code;
			$wheres['id'] = $last_user_id;
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// アカウント証明書ファイルの設置
			$upload_dir = CERT_UPLOAD_DIR . '/' . $code;
			$upload_temp_dir = SECURE_DIR . '/temp';
			mkdir($upload_dir, 0777);

			$upload_certs = $model_session->get('upload_certs');
			if ($upload_certs) {

				$dao_user_cert = new DaoUserCert();

				foreach ($upload_certs as $upload_cert) {

					$file_secure_temp_name = $upload_temp_dir . '/' . $upload_cert['secure_temp_name'];

					// 00000000000_00_0.png
					$file = sprintf("%s_%s_%s.%s", $code, sprintf('%02d', $upload_cert['cert_id']), $upload_cert['cert_type'], UtilFile::get_extension($file_secure_temp_name));
					$file_name = $upload_dir . '/' . $file;
					rename($file_secure_temp_name, $file_name);

					// アカウント証明書登録
					$entity_user_cert = new EntityUserCert();
					$entity_user_cert->id = null;
					$entity_user_cert->user_id = $last_user_id;
					$entity_user_cert->cert_id = $upload_cert['cert_id'];
					$entity_user_cert->cert_type = $upload_cert['cert_type'];
					$entity_user_cert->file_name = $file;
					$entity_user_cert->is_approved = 0;
					if (!$un_cert_id = $dao_user_cert->insert($entity_user_cert))
						throw new Exception("Error Processing Request", 1);	

				}

			}

			// セッションを削除する
			$model_session->destroy();
			$model_session->close();

			$dao_user->commit();
			// $dao_user->rollback();

			// 会員登録完了メールを送信
			$mail_params['name'] = $input_data['name'];
			$mail_params['name_kana'] = $input_data['name_kana'];
			$mail_params['tel'] = $input_data['tel'];
			$mail_params['birth_day'] = $input_data['birth_day'];
			$mail_params['email'] = $input_data['email'];
			$mail_params['post_code'] = $input_data['post_code'];
			$mail_params['address'] = $input_data['address'];
			$mail_params['gender_type_name'] = $input_data['gender_type_name'];
			$mail_params['employ_type_name'] = $input_data['employ_type_name'];
			$mail_params['country_name'] = $input_data['country_name'];
			$mail_params['company_name'] = $input_data['etc_company_name'] ? $input_data['etc_company_name'] : $input_data['company_name'];

			$model_mail = new ModelMail();
			$model_mail->set_from(NOTICE_EMAIL);
			$model_mail->set_replyto(NOTICE_EMAIL);
			switch ($input_data['lang_type']) {
				case PARAM_CONST_LANG_TYPE_EN:
					$model_mail->set_subject(MAIL_SUBJECT_REGIST_FINISH_EN);
					$model_mail->create_body('regist_finish_en', $mail_params);
					break;

				case PARAM_CONST_LANG_TYPE_JP:
				default:
					$model_mail->set_subject(MAIL_SUBJECT_REGIST_FINISH);
					$model_mail->create_body('regist_finish', $mail_params);
					break;
			}
			$model_mail->set_address($input_data['email']);
			$model_mail->send();
			unset($model_mail);

			$this->_view->assign('lang_type', $input_data['lang_type']);

		} catch (Exception $e) {

			echo $e->getMessage();
			
			if (isset($dao_user))
				$dao_user->rollback();

			$this->_view->assign('lang_type', $input_data['lang_type']);

		}

	}

}