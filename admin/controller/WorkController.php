<?php

/**
 * CONTROLLER : Work
 *
 *　@author kanemiya
 */

class WorkController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const PAGER_PER_PAGE = 10;

	const PAGER_DELTA = 5;

	const NAME_LENGTH = 32;

	const NAME_KANA_LENGTH = 32;

	const TEL_LENGTH = 16;

	const EMAIL_LENGTH = 64;

	const PASSWORD_LENGTH = 16;

	function __construct() {
	
		parent::__construct();

	}

	/**
	 * 事前処理
	 *
	 */
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

	/**
	 * デフォルトアクション
	 *
	 */
	public function indexAction() {

		// リストアクションへ遷移
		$this->changeOtherAction('list');

	}


	/**
	 * 記録シフトリストアクション
	 *
	 */
	public function listAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$location_name = $this->_request->getQuery('location_name');
		$location_shift_name = $this->_request->getQuery('location_shift_name');
		$shift_date_from = $this->_request->getQuery('shift_date_from');
		$shift_date_to = $this->_request->getQuery('shift_date_to');

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		// 初期条件 : アサイン確定済みの現場のみ
		$wheres['l.is_assigned'] = true;

		if ($location_name)
			$wheres['l.name'] = $location_name;

		if ($location_shift_name)
			$wheres['ls.name'] = $location_shift_name;

		if ($shift_date_from)
			$wheres['ls.shift_date_from'] = $shift_date_from;

		if ($shift_date_to)
			$wheres['ls.shift_date_to'] = $shift_date_to;

		$sorts = null;
		if ($sort_key && $sort_type) {

			switch ($sort_type) {

				case PARAM_CONST_LIST_SORT_DESC:
					$sorts[$sort_key] = 'DESC';
					break;

				case PARAM_CONST_LIST_SORT_ASC:
				default:
					$sorts[$sort_key] = 'ASC';
					break;

			}

		}

		// アサイン済みの現場
		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where_with_location(
			$wheres,
			// array(
			// 	'l.is_assigned' => true
			// ),
			$sorts,
			// array(
			// 	'l.location_date' => 'DESC',
			// 	'ls.shift_date_from' => 'ASC'
			// ),
			self::PAGER_PER_PAGE,
			$offset
		);

		// 全件数
		$location_shift_all = $dao_location_shift->select_where_with_location(
			array(
				'l.is_assigned' => true
			)
		);
		$total_num = $location_shift_all ? count($location_shift_all) : 0;

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/work/list/');
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		// $pager->set_query('location_id', $location_id);
		$pager->create();

		$this->_view->assign('location_shifts', $location_shifts);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 打刻現場シフト一覧CSVエクスポートアクション
	 *
	 */
	public function listCsvExportAction() {

		$location_name = $this->_request->getPost('location_name');
		$location_shift_name = $this->_request->getPost('location_shift_name');
		$shift_date_from = $this->_request->getPost('shift_date_from');
		$shift_date_to = $this->_request->getPost('shift_date_to');

		$wheres = array('l.is_assigned' => true);
		if ($location_name)
			$wheres['l.name'] = $location_name;

		if ($location_shift_name)
			$wheres['ls.name'] = $location_shift_name;

		if ($shift_date_from)
			$wheres['ls.shift_date_from'] = $shift_date_from;

		if ($shift_date_to)
			$wheres['ls.shift_date_to'] = $shift_date_to;

		// アサイン済みの現場
		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where_with_location($wheres);
		$location_shift_ids = array();
		foreach ($location_shifts as $location_shift) {
			$location_shift_ids[] = $location_shift->id;
		}

		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_users = $dao_location_assign_user->select_where_with_user(array('lau.location_shift_id' => $location_shift_ids));

		$headers = array(
			'現場ID',
			'現場名',
			'現場シフトID',
			'現場シフト名',
			'現場シフト日時（開始）',
			'現場シフト日時（終了）',
			'従事者コード',
			'従事者名',
			'従事者名（ふりがな）',
			'性別',
			'始業日時',
			'終業日時',
			'コメント',
		);

		$datas = array();
		foreach ($location_assign_users as $location_assign_user) {
			$data['location_id'] = $location_assign_user->location_id;
			$data['location_name'] = $location_assign_user->location_name;
			$data['location_shift_id'] = $location_assign_user->location_shift_id;
			$data['location_shift_name'] = $location_assign_user->location_shift_name;
			$data['shift_date_from'] = $location_assign_user->shift_date_from;
			$data['shift_date_to'] = $location_assign_user->shift_date_to;
			$data['code'] = $location_assign_user->code;
			$data['name'] = $location_assign_user->name;
			$data['name_kana'] = $location_assign_user->name_kana;
			$data['gender'] = PARAM_CONST_GENDER_TYPES[$location_assign_user->gender_type];
			$data['work_date_from'] = $location_assign_user->work_date_from;
			$data['work_date_to'] = $location_assign_user->work_date_to;
			$data['comment'] = $location_assign_user->comment;
			$datas[] = $data;
		}

		UtilFile::get_csv(date("YmdHis") . '_records', $datas, $headers);
		exit;

	}

	/**
	 * 記録フォームアクション
	 *
	 */
	public function recordFormAction() {

		$id = $this->_request->getQuery('id');
		$type = $this->_request->getQuery('type');

		// 未入力チェック : 現場シフトID
		if (!$id)
			throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフトID'), __LINE__);

		// 未入力チェック : 打刻種別
		if (!$type)
			throw new Exception(sprintf(ERR_MSG_EMPTY, '打刻種別'), __LINE__);

		// 入力値チェック : 打刻種別
		if (!array_key_exists($type, PARAM_WORK_TYPES))
			throw new Exception(sprintf(ERR_MSG_INPUT, '打刻種別'), __LINE__);

		// 存在チェック : 現場シフト
		$dao_location_shift = new DaoLocationShift();
		$location_shift = $dao_location_shift->select_where_with_location(array('ls.id' => $id))[0];
		if (!$location_shift)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場シフト情報'), __LINE__);

		// アサイン済みチェック : 現場
		if (!$location_shift->is_assigned)
			throw new Exception(sprintf(ERR_MSG_NOT_CONFIRM_ASSIGIN, '現場シフト'), __LINE__);

		// 締め処理済みチェック : 現場
		if ($location_shift->is_closed)
			throw new Exception(sprintf(ERR_MSG_CLOSED, '現場シフト'), __LINE__);

		$this->_view->assign('location_shift', $location_shift);
		$this->_view->assign('work_type_name', PARAM_WORK_TYPES[$type]);
		$this->_view->assign('type', $type);

	}

	/**
	 * 現場シフト名簿アクション
	 *
	 */
	public function recordListAction() {

		$id = $this->_request->getQuery('id');

		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_users = $dao_location_assign_user->select_where_with_user(array('lau.location_shift_id' => $id));

		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where_with_location(array('ls.id' => $id));

		$this->_view->assign('location_assign_users', $location_assign_users);
		$this->_view->assign('location_shift', $location_shifts[0]);

	}

	/**
	 * 記録リストアクション
	 *
	 */
	public function recordUpdFormAction() {

		$id = $this->_request->getQuery('id');

		// 未入力チェック : 現場シフトID
		if (!$id)
			throw new Exception(sprintf(ERR_MSG_EMPTY, '現場アサイン従事者ID'), __LINE__);

		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_where_with_user(array('lau.id' => $id));
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者情報'), __LINE__);

		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where_with_location(array('ls.id' => $location_assign_user[0]->location_shift_id));

		$this->_view->assign('location_assign_user', $location_assign_user[0]);
		$this->_view->assign('location_shift', $location_shifts[0]);
		$this->_view->assign('id', $location_assign_user[0]->location_shift_id);

	}

	/**
	 * 記録リストアクション
	 *
	 */
	public function recordUpdFinishAction() {

		$work_date_from = $this->_request->getPost('work_date_from');
		$work_date_to = $this->_request->getPost('work_date_to');
		$work_date_from = $work_date_from ? str_replace('T', ' ', $work_date_from) . ':00' : null;
		$work_date_to = $work_date_to ? str_replace('T', ' ', $work_date_to) . ':00' : null;
		$comment = $this->_request->getPost('comment');

		$id = $this->_request->getPost('id');
		// 未入力チェック : 現場シフトID
		if (!$id)
			throw new Exception(sprintf(ERR_MSG_EMPTY, '現場アサイン従事者ID'), __LINE__);

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_by_key($id);
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者情報'), __LINE__);		

		$sets['work_date_from'] = $work_date_from ? $work_date_from : null;
		if (!$location_assign_user->is_modify_from && $location_assign_user->work_date_from != $work_date_from)
			$sets['is_modify_from'] = true;
		$sets['work_date_to'] = $work_date_to ? $work_date_to : null;
		if (!$location_assign_user->is_modify_to && $location_assign_user->work_date_to != $work_date_to)
			$sets['is_modify_to'] = true;
		$sets['comment'] = $comment;
		$wheres['id'] = $id;
		$dao_location_assign_user = new DaoLocationAssignUser();
		if (!false === $dao_location_assign_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		$this->_view->assign('id', $id);

	}

	/**
	 * 従事者追加アクション
	 *
	 */
	public function addUserFormAction() {

		$id = $this->_request->getQuery('id');

		$dao_country = new DaoCountry();
		$dao_company = new DaoCompany();

		$this->_view->assign('id', $id);
		$this->_view->assign('countries', $dao_country->select_all());
		$this->_view->assign('companies', $dao_company->select_all());

	}

	public function addUserFinishAction() {

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
		$note = $this->_request->getPost('note');

		$location_shift_id = $this->_request->getPost('id');
		if (!$location_shift_id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$error_messages = array();

		try {

			// 氏名 : 未入力チェック
			if (UtilCommon::is_empty($name))
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '氏名');

			// 氏名 : 文字数チェック
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '氏名', self::NAME_LENGTH);

			// 氏名（ふりがな） : 未入力チェック
			if (UtilCommon::is_empty($name_kana))
				$error_messages['name_kana'] = sprintf(ERR_MSG_EMPTY, '氏名（ふりがな）');

			// 氏名（ふりがな） : 文字数チェック
			if (!UtilCommon::is_length($name_kana, self::NAME_KANA_LENGTH))
				$error_messages['name_kana'] = sprintf(ERR_MSG_LENTGTH, '氏名（ふりがな）', self::NAME_KANA_LENGTH);

			// 氏名（ふりがな） : ひらがな&カタカナ&アルファベットチェック
			if (!UtilCommon::is_hira_kata_alpha($name_kana))
				$error_messages['name_kana'] = sprintf(ERR_MSG_HIRAGANA_KATAKANA_ALPHA, '氏名（ふりがな）');

			// 電話番号 : 未入力チェック
			// if (UtilCommon::is_empty($tel))
			// 	$error_messages['tel'] = sprintf(ERR_MSG_EMPTY, '電話番号');

			// 電話番号 : 文字数チェック
			if (!UtilCommon::is_length($tel, self::TEL_LENGTH))
				$error_messages['tel'] = sprintf(ERR_MSG_LENTGTH, '電話番号', self::TEL_LENGTH);

			// 電話番号 : 数値チェック
			if (!UtilCommon::is_num($tel))
				$error_messages['tel'] = sprintf(ERR_MSG_NUM, '電話番号');

			// メールアドレス : 未入力チェック
			// if (UtilCommon::is_empty($email))
			// 	$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');		

			// メールアドレス : 文字数チェック
			if (!UtilCommon::is_length($email, self::EMAIL_LENGTH))
				$error_messages['email'] = sprintf(ERR_MSG_LENTGTH, 'メールアドレス', self::EMAIL_LENGTH);

			// メールアドレス : 形式チェック
			if (!UtilCommon::is_mail($email))
				$error_messages['email'] = sprintf(ERR_MSG_INVALID, 'メールアドレス');

			// メールアドレス : 重複チェック
			$dao_user = new DaoUser();
			if ($email) {
				$user = $dao_user->select_where_one(array('email' => $email));
				if ($user)
					$error_messages['email'] = sprintf(ERR_MSG_USED_YET, 'メールアドレス');
			}

			// パスワード : 未入力チェック
			// if (UtilCommon::is_empty($password))
			// 	$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');		

			// パスワード : 文字数チェック
			// if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH))
			// 	$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード', self::PASSWORD_LENGTH);

			// パスワード（再入力） : 未入力チェック
			// if (UtilCommon::is_empty($re_password))
			// 	$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, 'パスワード（再入力）');		

			// パスワード（再入力） : 文字数チェック
			// if (!UtilCommon::is_length($re_password, self::PASSWORD_LENGTH))
			// 	$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード（再入力）', self::PASSWORD_LENGTH);

			// パスワード（再入力）: 不一致チェック
			// if ($re_password != $password)
			// 	$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME,'パスワード', 'パスワード（再入力）', self::PASSWORD_LENGTH);

			// 郵便番号 : 未入力チェック
			// if (UtilCommon::is_empty($post_code))
			// 	$error_messages['post_code'] = sprintf(ERR_MSG_EMPTY, '郵便番号');	

			// 住所 : 未入力チェック
			// if (UtilCommon::is_empty($address))
			// 	$error_messages['address'] = sprintf(ERR_MSG_EMPTY, '住所');	

			// 性別 : 未入力チェック
			if (UtilCommon::is_empty($gender_type))
				$error_messages['gender_type'] = sprintf(ERR_MSG_EMPTY, '性別');	

			// 性別 : 規定値外入力チェック
			if (!isset(PARAM_CONST_GENDER_TYPES[$gender_type]))
				$error_messages['gender_id'] = sprintf(ERR_MSG_INPUT, '性別');

			// 生年月日 : 未入力チェック
			// if (UtilCommon::is_empty($birth_day))
			// 	$error_messages['birth_day'] = sprintf(ERR_MSG_EMPTY, '生年月日');

			// 生年月日 : 未来日チェック
			if ($birth_day > date('Y-m-d'))
				$error_messages['birth_day'] = sprintf(ERR_MSG_INVALID, '生年月日');

			// 国籍 : 未入力チェック
			if (!strlen($country_id))
				$error_messages['country_id'] = sprintf(ERR_MSG_EMPTY, '国籍');

			// 国籍 : 規定値外入力チェック
			$dao_country = new DaoCountry();
			$country = $dao_country->select_by_key($country_id);
			if (!$country)
				$error_messages['country_id'] = sprintf(ERR_MSG_INPUT, '国籍');	

			// 雇用形態 : 未入力チェック
			if (!strlen($employ_type))
				$error_messages['employ_type'] = sprintf(ERR_MSG_EMPTY, '雇用形態');

			// 雇用形態 : 規定値外入力チェック
			if (!isset(PARAM_CONST_EMPLOY_TYPES[$employ_type]))
				$error_messages['employ_type'] = sprintf(ERR_MSG_INPUT, '雇用形態');

			// 所属会社
			if (!mb_strlen($company_id))
				$error_messages['company_id'] = sprintf(ERR_MSG_EMPTY, '所属会社');	

			// 所属会社 : 規定値外入力チェック
			$dao_company = new DaoCompany();
			$company = $dao_company->select_by_key($company_id);
			if (!$company_id && !$company && UtilCommon::is_empty($etc_company_name))
				$error_messages['company_id'] = sprintf(ERR_MSG_INPUT, '所属会社');	

			// 所属会社（その他）
			if (!$company_id && UtilCommon::is_empty($etc_company_name))
				$error_messages['etc_company_name'] = sprintf(ERR_MSG_EMPTY, '所属会社（その他）');	

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", __LINE__);


			$dao_user->begin();

			$entity_user = new EntityUser();
			$entity_user->id = null;
			$entity_user->code = ""; // とりあえず空文字
			$entity_user->status_type = PARAM_CONST_USER_STATUS_TYPE_ENABLE;
			$entity_user->approve_type = PARAM_CONST_USER_APPROVE_TYPE_PASSED; // 無条件で承認
			$entity_user->name = $name;
			$entity_user->name_kana = $name_kana;
			$entity_user->tel = $tel;
			$entity_user->email = $email;
			$entity_user->post_code = $post_code;
			$entity_user->address = $address;
			$entity_user->gender_type = $gender_type;
			$entity_user->birth_day = $birth_day;
			$entity_user->country_id = $country_id;
			$entity_user->employ_type = $employ_type;
			$entity_user->company_id = $company_id;
			$entity_user->etc_company_name = $etc_company_name;
			$entity_user->note = $note;
			if(!$last_user_id = $dao_user->insert($entity_user))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 従事者コードを発行する
			$code = UtilCommon::gererate_user_code($last_user_id);
			$sets['code'] = $code;
			$wheres['id'] = $last_user_id;
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 現場アサイン従事者に登録する
			$entity_location_assign_user = new EntityLocationAssignUser();
			$entity_location_assign_user->id = null;
			$entity_location_assign_user->location_shift_id = $location_shift_id;
			$entity_location_assign_user->user_id = $last_user_id;
			$dao_location_assign_user = new DaoLocationAssignUser();
			if (!$dao_location_assign_user->insert($entity_location_assign_user))
				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			$dao_user->commit();
			// $dao_user->rollback();

		} catch (Exception $e) {

			// 国籍マスタ
			if (!isset($dao_country))
				$dao_country = new DaoCountry();

			// 所属会社マスタ
			if (!isset($dao_company))
				$dao_company = new DaoCompany();

			// $this->_view->assign('gender_types', PARAM_CONST_GENDER_TYPES);
			// $this->_view->assign('employ_types', PARAM_CONST_EMPLOY_TYPES);
			$this->_view->assign('countries', $dao_country->select_all());	
			$this->_view->assign('companies', $dao_company->select_all());		

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('addUserForm');

		}

		$this->_view->assign('id', $location_shift_id);


	}

}