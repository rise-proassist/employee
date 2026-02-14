<?php

/**
 * CONTROLLER : User
 *
 *　@author kanemiya
 */

class UserController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const PAGER_PER_PAGE = 10;

	const PAGER_DELTA = 5;

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

		// 一覧アクションリダイレクト
		header("HTTP/1.1 301 Moved Permanently"); 
		header("Location: " . UtilCommon::get_base_url('user', 'list')); 
		exit;

	}


	/**
	 * 一覧アクション
	 *
	 */
	public function listAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$code = $this->_request->getQuery('code');
		$name = $this->_request->getQuery('name');
		$name_kana = $this->_request->getQuery('name_kana');
		$email = $this->_request->getQuery('email');
		$tel = $this->_request->getQuery('tel');

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		// 従事者情報を検索
		$model_user_serach = new ModelUserSearch();

		if ($code)
			$model_user_serach->set_code($code);

		if ($name)
			$model_user_serach->set_name($name);

		if ($name_kana)
			$model_user_serach->set_name_kana($name_kana);

		if ($email)
			$model_user_serach->set_email($email);

		if ($tel)
			$model_user_serach->set_tel($tel);

		if ($sort_key)
			$model_user_serach->set_sort_key($sort_key);

		if ($sort_type)
			$model_user_serach->set_sort_type($sort_type);

		$model_user_serach->set_is_all(true);
		$model_user_serach->set_limit(self::PAGER_PER_PAGE);
		$model_user_serach->set_offset($offset);
		$model_user_serach->search();
		$users = $model_user_serach->get();

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($model_user_serach->get_num());
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/user/list/');
		if ($code)
			$pager->set_query('code', $code);

		if ($name)
			$pager->set_query('name', $name);

		if ($name_kana)
			$pager->set_query('name_kana', $name_kana);

		if ($email)
			$pager->set_query('email', $email);

		if ($tel)
			$pager->set_query('tel', $tel);

		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);

		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);

		$pager->create();

		$this->_view->assign('users', $users);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 詳細・更新フォームアクション
	 *
	 */
	public function updFormAction() {

		$id = $this->_request->getQuery('id');

		if (!$id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$model_user = new ModelUser($id);
		
		$this->_view->assign('user', $model_user->get());

	}

	/**
	 * 資格証明書却下完了アクション
	 *
	 */
	public function userCertDelFinishAction() {

		$user_cert_ids = $this->_request->getPost('user_cert_ids');
		$user_id = $this->_request->getPost('user_id');

		$model_user = new ModelUser($user_id);
		$user = $model_user->get();

		$dao_user_cert = new DaoUserCert();

		foreach ($user->user_certs as $user_cert) {

			foreach ($user_cert->cert_files as $cert_files) {

				foreach ($cert_files as $cert_file) {

					if (in_array($cert_file->id, $user_cert_ids)) {

						// 資格証明書ファイル削除
						if (file_exists($cert_file->file_dir))
							unlink($cert_file->file_dir);

						// 資格証明書データ削除
						$wheres['id'] = $cert_file->id;
						if (!$dao_user_cert->delete($wheres))
							throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

					}

				}	

			}

		}

		$this->_view->assign('id', $user_id);

	}

	/**
	 * 従事者承認状態更新完了アクション
	 *
	 */
	public function approveTypeUpdFinishAction() {

		$id = $this->_request->getPost('id');
		$approve_type = $this->_request->getPost('approve_type');

		if (!$id || !$approve_type)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		if (PARAM_CONST_USER_APPROVE_TYPE_PASSED != $approve_type && PARAM_CONST_USER_APPROVE_TYPE_REJECTED != $approve_type)
			throw new Exception(sprintf(ERR_MSG_NO_MODIFY, '承認状態'), __LINE__);

		// 承認状態を更新
		$sets['approve_type'] = $approve_type;
		$wheres['id'] = $id;
		$dao_user = new DaoUser();
		if (false === $dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		$model_user = new ModelUser($id);
		$user = $model_user->get();

		// 従事者に結果をメールで配信する
		$mail_params['name'] = $user->name;
		$mail_params['name_kana'] = $user->name_kana;
		$mail_params['tel'] = $user->tel;
		$mail_params['email'] = $user->email;
		$mail_params['post_code'] = $user->post_code;
		$mail_params['address'] = $user->address;
		$mail_params['birth_day'] = $user->birth_day;
		$mail_params['gender_type_name'] = $user->gender_type_name;
		$mail_params['country_name'] = $user->country_name;
		$mail_params['employ_type_name'] = $user->employ_type_name;
		$mail_params['company_name'] = $user->company_name ? $user->company_name : $user->etc_company_name;

		$model_mail = new ModelMail();
		$model_mail->set_from(NOTICE_EMAIL);
		$model_mail->set_replyto(NOTICE_EMAIL);
		switch ($approve_type) {
			case PARAM_CONST_USER_APPROVE_TYPE_PASSED:
				$model_mail->set_subject(MAIL_SUBJECT_REGIST_FINISH);
				$model_mail->create_body('approve_passed', $mail_params);
				break;

			case PARAM_CONST_USER_APPROVE_TYPE_REJECTED:
				$model_mail->set_subject(MAIL_SUBJECT_APPROVE_REJECTED);
				$model_mail->create_body('approve_rejected', $mail_params);
				break;
			
			default:
				// code...
				break;
		}
		
		$model_mail->set_address($user->email);
		$model_mail->send();
		unset($model_mail);

		$this->_view->assign('id', $user->id);
	}

	/**
	 * 従事者ステータス更新完了アクション
	 *
	 */
	public function statusTypeUpdFinishAction() {

		$id = $this->_request->getPost('id');
		$status_type = $this->_request->getPost('status_type');

		if (!$id || !$status_type)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$model_user = new ModelUser($id);
		$user = $model_user->get();
		if (!$user)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$sets['status_type'] = $status_type;
		$wheres['id'] = $id;
		$dao_user = new DaoUser();
		if (false === $dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		$this->_view->assign('id', $id);

	}

	/**
	 * CSVエクスポートアクション
	 *
	 */
	public function csvExportAction() {

		$code = $this->_request->getPost('code');
		$name = $this->_request->getPost('name');
		$name_kana = $this->_request->getPost('name_kana');
		$email = $this->_request->getPost('email');
		$tel = $this->_request->getPost('tel');

		$dao_user = new DaoUser();
		$users = $dao_user->select_for_search(
			array(
				'u.name' => $name,
				'u.name_kana' => $name_kana,
				'u.email' => $email,
				'u.tel' => $tel,
				'u.code' => $code,
			)
		);

		$headers = array(
			'ID*',
			'コード*',
			'ステータス種別*',
			'ステータス種別名',
			'承認種別*',
			'承認種別名',
			'氏名*',
			'氏名（ふりがな）*',
			'電話番号',
			'メールアドレス',
			'パスワード',
			'郵便番号',
			'住所',
			'性別種別*',
			'性別',
			'生年月日',
			'国籍ID*',
			'国籍名',
			'雇用形態種別*',
			'雇用形態名',
			'所属会社ID*',
			'所属会社名',
			'その他会社名',
		);

		$datas = array();
		foreach ($users as $key => $user) {
			$data['id'] = $user->id;
			$data['code'] = $user->code;
			$data['status_type'] = $user->status_type;
			$data['status_type_name'] = PARAM_CONST_USER_STATUS_TYPES[$user->status_type];
			$data['approve_type'] = $user->approve_type;
			$data['approve_type_name'] = PARAM_CONST_USER_APPROVE_TYPES[$user->approve_type];
			$data['name'] = $user->name;
			$data['name_kana'] = $user->name_kana;
			$data['tel'] = $user->tel;
			$data['email'] = $user->email;
			$data['password'] = null; // パスワードは出力しない
			$data['post_code'] = $user->post_code;
			$data['address'] = $user->address;
			$data['gender_type'] = $user->gender_type;
			$data['gender_name'] = PARAM_CONST_GENDER_TYPES[$user->gender_type];
			$data['birth_day'] = $user->birth_day;
			$data['country_id'] = $user->country_id;
			$data['country_name'] = $user->country_name;
			$data['employ_type'] = $user->employ_type;
			$data['employ_name'] = PARAM_CONST_EMPLOY_TYPES[$user->employ_type];
			$data['company_id'] = $user->company_id;
			$data['company_name'] = $user->company_name;
			$data['etc_company_name'] = $user->etc_company_name;
			$datas[] = $data;
		}


		UtilFile::get_csv(date("YmdHis") . '_users', $datas, $headers);
		exit;

	}

}