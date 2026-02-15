<?php

/**
 * CONTROLLER : マスターデータ管理
 *
 *　@author kanemiya
 */

class MasterDataController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const PAGER_PER_PAGE = 10;

	const PAGER_DELTA = 5;

	const NAME_LENGTH = 30;

	const SHORT_NAME_LENGTH = 10;

	const VALUE_LENGTH = 30;

	const ERROR_CODE_ERROR_PARAM = 10000;

	const ERROR_CODE_NOT_FOUND = 10001;
	

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
		$this->changeOtherAction('genderList');

	}

	/**
	 * 国籍マスタ一覧アクション
	 *
	 */
	public function countryListAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$name = $this->_request->getQuery('name');

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

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

		// 国籍マスタ
		$dao_country = new DaoCountry();
		$countries = $dao_country->select_where($wheres, $sorts, self::PAGER_PER_PAGE, $offset);

		// 全件数
		$total_num = $dao_country->select_count($wheres);

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/masterData/countryList/');
		if ($name)
			$pager->set_query('name', $name);
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		$pager->create();

		$this->_view->assign('countries', $countries);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 国籍マスタ一覧CSVエクスポートアクション
	 *
	 */
	public function countryListCsvExportAction() {

		$name = $this->_request->getPost('name');

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

		// 国籍マスタ
		$dao_country = new DaoCountry();
		$countries = $dao_country->select_where($wheres);

		$headers = array(
			'ID',
			'名称',
		);

		$datas = array();
		foreach ($countries as $country) {
			$data['id'] = $country->id;
			$data['name'] = $country->name;
			$datas[] = $data;
		}

		UtilFile::get_csv(date("YmdHis") . '_countries', $datas, $headers);
		exit;

	}

	/**
	 * 国籍マスタ登録フォームアクション
	 *
	 */
	public function countryAddFormAction() {}

	/**
	 * 国籍マスタ登録完了アクション
	 *
	 */
	public function countryAddFinishAction() {

		$name = $this->_request->getPost('name');

		try {

			$error_messages = array();

			// 未入力チェック : 名称
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '名称');

			// 文字数チェック : 名称
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '名称', self::NAME_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 登録
			$entity_country = new EntityCountry();
			$entity_country->id = null;
			$entity_country->name = $name;
			$dao_country = new DaoCountry();
			if (!$dao_country->insert($entity_country))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());

			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('countryAddForm');

		}

	}

	/**
	 * 国籍マスタ更新フォームアクション
	 *
	 */
	public function countryUpdFormAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_country = new DaoCountry();
		$country = $dao_country->select_by_key($id);

		if (!$country)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '国籍'), self::ERROR_CODE_NOT_FOUND);	

		$this->_view->assign('country', $country);

	}

	/**
	 * 国籍マスタ更新完了アクション
	 *
	 */
	public function countryUpdFinishAction() {

		$name = $this->_request->getPost('name');
		$id = $this->_request->getPost('id');

		try {

			$error_messages = array();

			// 未入力チェック : 通番
			if (!$id)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 存在チェック
			$dao_country = new DaoCountry();
			$country = $dao_country->select_by_key($id);
			if (!$country)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 未入力チェック : 名前
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '名前');

			// 文字数チェック : 名前
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '名前', self::NAME_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 更新
			$sets['name'] = $name;
			$wheres['id'] = $id;
			if (!$dao_country->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('countryUpdForm');

		}

	}

	/**
	 * 国籍マスタ更新フォームアクション
	 *
	 */
	public function countryDelConfirmAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_country = new DaoCountry();
		$country = $dao_country->select_by_key($id);

		if (!$country)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '国籍'), self::ERROR_CODE_NOT_FOUND);

		// マスタに紐づくデータがある場合、削除不可
		$dao_user = new DaoUser();
		$is_del = !$dao_user->select_count(array('country_id' => $id)) ? true : false;

		$this->_view->assign('country', $country);
		$this->_view->assign('is_del', $is_del);

	}


	/**
	 * 国籍マスタ削除完了アクション
	 *
	 */
	public function countryDelFinishAction() {

		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

		// 存在チェック
		$dao_country = new DaoCountry();
		$country = $dao_country->select_by_key($id);
		if (!$country)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

		// 削除
		$wheres['id'] = $id;
		if (!$dao_country->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

	}

	/**
	 * 所属会社マスタ一覧アクション
	 *
	 */
	public function companyListAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$name = $this->_request->getQuery('name');

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

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

		// 所属会社マスタ
		$dao_company = new DaoCompany();
		$companies = $dao_company->select_where($wheres, $sorts, self::PAGER_PER_PAGE, $offset);

		// 全件数
		$total_num = $dao_company->select_count($wheres);

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/masterData/countryList/');
		if ($name)
			$pager->set_query('name', $name);
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		$pager->create();

		$this->_view->assign('companies', $companies);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 所属会社マスタ一覧CSVエクスポートアクション
	 *
	 */
	public function companyListCsvExportAction() {

		$name = $this->_request->getPost('name');

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

		// 所属会社マスタ
		$dao_company = new DaoCompany();
		$companies = $dao_company->select_where($wheres);

		$headers = array(
			'ID',
			'名称',
		);

		$datas = array();
		foreach ($companies as $company) {
			$data['id'] = $company->id;
			$data['name'] = $company->name;
			$datas[] = $data;
		}

		UtilFile::get_csv(date("YmdHis") . '_companies', $datas, $headers);
		exit;

	}

	/**
	 * 所属会社マスタ登録フォームアクション
	 *
	 */
	public function companyAddFormAction() {}

	/**
	 * 所属会社マスタ登録完了アクション
	 *
	 */
	public function companyAddFinishAction() {

		$name = $this->_request->getPost('name');

		try {

			$error_messages = array();

			// 未入力チェック : 名称
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '名称');

			// 文字数チェック : 名称
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '名称', self::NAME_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 登録
			$entity_company = new EntityCompany();
			$entity_company->id = null;
			$entity_company->name = $name;
			$dao_company = new DaoCompany();
			if (!$dao_company->insert($entity_company))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());

			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('countryAddForm');

		}

	}

	/**
	 * 所属会社マスタ更新フォームアクション
	 *
	 */
	public function companyUpdFormAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_company = new DaoCompany();
		$company = $dao_company->select_by_key($id);

		if (!$company)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '所属会社'), self::ERROR_CODE_NOT_FOUND);	

		$this->_view->assign('company', $company);

	}

	/**
	 * 所属会社マスタ更新完了アクション
	 *
	 */
	public function companyUpdFinishAction() {

		$name = $this->_request->getPost('name');
		$id = $this->_request->getPost('id');

		try {

			$error_messages = array();

			// 未入力チェック : 通番
			if (!$id)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 存在チェック
			$dao_company = new DaoCompany();
			$company = $dao_company->select_by_key($id);
			if (!$company)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 未入力チェック : 名前
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '名前');

			// 文字数チェック : 名前
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '名前', self::NAME_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 更新
			$sets['name'] = $name;
			$wheres['id'] = $id;
			if (!$dao_company->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('companyUpdForm');

		}

	}

	/**
	 * 所属会社マスタ更新フォームアクション
	 *
	 */
	public function companyDelConfirmAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_company = new DaoCompany();
		$company = $dao_company->select_by_key($id);

		if (!$company)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '所属会社'), self::ERROR_CODE_NOT_FOUND);

		// マスタに紐づくデータがある場合、削除不可
		$dao_user = new DaoUser();
		$is_del = !$dao_user->select_count(array('company_id' => $id)) ? true : false;

		$this->_view->assign('company', $company);
		$this->_view->assign('is_del', $is_del);

	}

	/**
	 * 所属会社マスタ削除完了アクション
	 *
	 */
	public function companyDelFinishAction() {

		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

		// 存在チェック
		$dao_company = new DaoCompany();
		$company = $dao_company->select_by_key($id);
		if (!$company)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

		// 削除
		$wheres['id'] = $id;
		if (!$dao_company->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

	}

	/**
	 * 資格証明書マスタ一覧アクション
	 *
	 */
	public function certListAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$name = $this->_request->getQuery('name');

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

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

		// 資格証明書マスタ
		$dao_cert = new DaoCert();
		$certs = $dao_cert->select_where($wheres, $sorts, self::PAGER_PER_PAGE, $offset);

		// 全件数
		$total_num = $dao_cert->select_count($wheres);

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/masterData/countryList/');
		if ($name)
			$pager->set_query('name', $name);
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		$pager->create();

		$this->_view->assign('certs', $certs);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 資格証明書マスタ一覧CSVエクスポートアクション
	 *
	 */
	public function certListCsvExportAction() {

		$name = $this->_request->getPost('name');

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

		// 資格証明書マスタ
		$dao_cert = new DaoCert();
		$certs = $dao_cert->select_where($wheres);

		$headers = array(
			'ID',
			'名称',
			'略称',
			'手当',
		);

		$datas = array();
		foreach ($certs as $cert) {
			$data['id'] = $cert->id;
			$data['name'] = $cert->name;
			$data['short_name'] = $cert->short_name;
			$data['allowance'] = $cert->allowance;
			$datas[] = $data;
		}

		UtilFile::get_csv(date("YmdHis") . '_certs', $datas, $headers);
		exit;

	}

	/**
	 * 資格証明書マスタ登録フォームアクション
	 *
	 */
	public function certAddFormAction() {}

	/**
	 * 資格証明書マスタ登録完了アクション
	 *
	 */
	public function certAddFinishAction() {

		$name = $this->_request->getPost('name');
		$short_name = $this->_request->getPost('short_name');
		$allowance = $this->_request->getPost('allowance');

		try {

			$error_messages = array();

			// 未入力チェック : 名称
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '名称');

			// 文字数チェック : 名称
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '名称', self::NAME_LENGTH);

			// 未入力チェック : 名称（略称）
			if (!$short_name)
				$error_messages['short_name'] = sprintf(ERR_MSG_EMPTY, '名称（略称）');

			// 文字数チェック : 名称（略称）
			if (!UtilCommon::is_length($short_name, self::SHORT_NAME_LENGTH))
				$error_messages['short_name'] = sprintf(ERR_MSG_LENTGTH, '名称（略称）', self::SHORT_NAME_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 登録
			$entity_cert = new EntityCert();
			$entity_cert->id = null;
			$entity_cert->name = $name;
			$entity_cert->short_name = $short_name;
			$entity_cert->allowance = $allowance;
			$dao_cert = new DaoCert();
			if (!$dao_cert->insert($entity_cert))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());

			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('certAddForm');

		}

	}

	/**
	 * 資格証明書マスタ更新フォームアクション
	 *
	 */
	public function certUpdFormAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_cert = new DaoCert();
		$cert = $dao_cert->select_by_key($id);

		if (!$cert)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '資格証明書'), self::ERROR_CODE_NOT_FOUND);	

		$this->_view->assign('cert', $cert);

	}

	/**
	 * 資格証明書マスタ更新完了アクション
	 *
	 */
	public function certUpdFinishAction() {

		$name = $this->_request->getPost('name');
		$short_name = $this->_request->getPost('short_name');
		$allowance = $this->_request->getPost('allowance');
		$id = $this->_request->getPost('id');

		try {

			$error_messages = array();

			// 未入力チェック : 通番
			if (!$id)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 存在チェック
			$dao_cert = new DaoCert();
			$cert = $dao_cert->select_by_key($id);
			if (!$cert)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 未入力チェック : 名前
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '名前');

			// 文字数チェック : 名前
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '名前', self::NAME_LENGTH);

			// 未入力チェック : 名称（略称）
			if (!$short_name)
				$error_messages['short_name'] = sprintf(ERR_MSG_EMPTY, '名称（略称）');

			// 文字数チェック : 名称（略称）
			if (!UtilCommon::is_length($short_name, self::SHORT_NAME_LENGTH))
				$error_messages['short_name'] = sprintf(ERR_MSG_LENTGTH, '名称（略称）', self::SHORT_NAME_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 更新
			$sets['name'] = $name;
			$sets['short_name'] = $short_name;
			$sets['allowance'] = $allowance;
			$wheres['id'] = $id;
			if (!$dao_cert->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('certUpdForm');

		}

	}

	/**
	 * 資格証明書マスタ更新フォームアクション
	 *
	 */
	public function certDelConfirmAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_cert = new DaoCert();
		$cert = $dao_cert->select_by_key($id);

		if (!$cert)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '資格証明書'), self::ERROR_CODE_NOT_FOUND);

		// マスタに紐づくデータがある場合、削除不可
		// $dao_user = new DaoUser();
		// $is_del = !$dao_user->select_count(array('cert_id' => $id)) ? true : false;
		$is_del = true; // TODO 仮

		$this->_view->assign('cert', $cert);
		$this->_view->assign('is_del', $is_del);

	}

	/**
	 * 資格証明書マスタ削除完了アクション
	 *
	 */
	public function certDelFinishAction() {

		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

		// 存在チェック
		$dao_cert = new DaoCert();
		$cert = $dao_cert->select_by_key($id);
		if (!$cert)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

		// 削除
		$wheres['id'] = $id;
		if (!$dao_cert->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

	}

	/**
	 * 源泉徴収金額マスタ一覧アクション
	 *
	 */
	public function withholdTaxListAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

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

		// 源泉徴収金額マスタ
		$dao_withhold_tax = new DaoWithholdTax();
		$withhold_taxs = $dao_withhold_tax->select_where(null, $sorts, self::PAGER_PER_PAGE, $offset);

		// 全件数
		$total_num = $dao_withhold_tax->select_count();

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/masterData/withholdTaxList/');
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		$pager->create();

		$this->_view->assign('withhold_taxs', $withhold_taxs);
		$this->_view->assign('pager', $pager);
		$this->_view->assign('withhold_tax_calc_types', WITHHOLD_TAX_CALC_TYPES);

	}

	/**
	 * 源泉徴収金額マスタ一覧CSVエクスポートアクション
	 *
	 */
	public function withholdTaxListCsvExportAction() {

		// 源泉徴収金額マスタ
		$dao_withhold_tax = new DaoWithholdTax();
		$withhold_taxs = $dao_withhold_tax->select_all();

		$headers = array(
			'ID',
			'算出種別',
			'算出種別名',
			'給与金額範囲（開始）',
			'給与金額範囲（終了）',
			'給与金額加算割合',
			'割合加算基準金額',
			'割合加算税金額',
			'税金額',
		);

		$datas = array();
		foreach ($withhold_taxs as $withhold_tax) {
			$data['id'] = $withhold_tax->id;
			$data['calc_type'] = $withhold_tax->calc_type;
			$data['calc_type_name'] = WITHHOLD_TAX_CALC_TYPES[$withhold_tax->calc_type];
			$data['amount_range_from'] = $withhold_tax->amount_range_from;
			$data['amount_range_to'] = $withhold_tax->amount_range_to;
			$data['amount_ratio'] = $withhold_tax->amount_ratio;
			$data['ratio_base_amount'] = $withhold_tax->ratio_base_amount;
			$data['ratio_base_tax_amount'] = $withhold_tax->ratio_base_tax_amount;
			$data['tax_amount'] = $withhold_tax->tax_amount;
			$datas[] = $data;
		}

		UtilFile::get_csv(date("YmdHis") . '_withhold_taxs', $datas, $headers);
		exit;

	}

	/**
	 * 源泉徴収金額マスタ登録フォームアクション
	 *
	 */
	public function withholdTaxAddFormAction() {

		$this->_view->assign('withhold_tax_calc_types', WITHHOLD_TAX_CALC_TYPES);
	
	}

	/**
	 * 源泉徴収金額マスタ登録完了アクション
	 *
	 */
	public function withholdTaxAddFinishAction() {

		$calc_type = $this->_request->getPost('calc_type');
		$amount_range_from = $this->_request->getPost('amount_range_from');
		$amount_range_to = $this->_request->getPost('amount_range_to');
		$amount_ratio = $this->_request->getPost('amount_ratio');
		$ratio_base_amount = $this->_request->getPost('ratio_base_amount');
		$ratio_base_tax_amount = $this->_request->getPost('ratio_base_tax_amount');
		$tax_amount = $this->_request->getPost('tax_amount');

		try {

			$error_messages = array();

			// 未入力チェック : 算出種別
			if (!$calc_type)
				$error_messages['calc_type'] = sprintf(ERR_MSG_EMPTY, '算出種別');

			// 未入力チェック : 給与金額範囲（開始）
			if (!$amount_range_from)
				$error_messages['amount_range_from'] = sprintf(ERR_MSG_EMPTY, '給与金額範囲（開始）');

			// 未入力チェック : 給与金額範囲（終了）	
			if (!$amount_range_to)
				$error_messages['amount_range_to'] = sprintf(ERR_MSG_EMPTY, '給与金額範囲（終了）');

			// 逆転チェック : 給与金額範囲（終了）	
			if ($amount_range_from > $amount_range_to)
				$error_messages['amount_range_to'] = sprintf(ERR_MSG_UNDER, '給与金額範囲（終了）', '給与金額範囲（開始）');

			switch ($calc_type) {

				case WITHHOLD_TAX_CALC_TYPE_RAGE:

					// 未入力チェック : 算出種別
					if (!$tax_amount)
						$error_messages['tax_amount'] = sprintf(ERR_MSG_EMPTY, '税金額');

					break;

				case WITHHOLD_TAX_CALC_TYPE_RATIO:

					// 未入力チェック : 給与金額加算割合	
					if (!$amount_ratio)
						$error_messages['amount_ratio'] = sprintf(ERR_MSG_EMPTY, '給与金額加算割合');

					// 未入力チェック : 割合加算基準金額	
					if (!$ratio_base_amount)
						$error_messages['ratio_base_amount'] = sprintf(ERR_MSG_EMPTY, '割合加算基準金額');

					// 未入力チェック : 割合加算税金額	
					if (!$ratio_base_tax_amount)
						$error_messages['ratio_base_tax_amount'] = sprintf(ERR_MSG_EMPTY, '割合加算税金額');

					break;				
				
				default:
					$error_messages['calc_type'] = sprintf(ERR_MSG_INPUT, '算出種別');
					break;
			}

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", __LINE__);

			// 登録
			$entity_withhold_tax = new EntityWithholdTax();
			$entity_withhold_tax->id = null;
			$entity_withhold_tax->calc_type = $calc_type;
			$entity_withhold_tax->amount_range_from = $amount_range_from;
			$entity_withhold_tax->amount_range_to = $amount_range_to;

			if (WITHHOLD_TAX_CALC_TYPE_RATIO == $calc_type){
				$entity_withhold_tax->amount_ratio = $amount_ratio;
				$entity_withhold_tax->ratio_base_amount = $ratio_base_amount;
			}

			if (WITHHOLD_TAX_CALC_TYPE_RAGE == $calc_type)
				$entity_withhold_tax->tax_amount = $tax_amount;

			$dao_withhold_tax = new DaoWithholdTax();
			if (!$dao_withhold_tax->insert($entity_withhold_tax))
				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('withhold_tax_calc_types', WITHHOLD_TAX_CALC_TYPES);

			parent::setAction('withholdTaxAddForm');

		}

	}

	/**
	 * 源泉徴収金額マスタ更新フォームアクション
	 *
	 */
	public function withholdTaxUpdFormAction() {

		$id = $this->_request->getQuery('id');

		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		$dao_withhold_tax = new DaoWithholdTax();
		$withhold_tax = $dao_withhold_tax->select_by_key($id);
		if (!$withhold_tax)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '源泉徴収金額'), __LINE__);	

		$this->_view->assign('withhold_tax', $withhold_tax);
		$this->_view->assign('withhold_tax_calc_types', WITHHOLD_TAX_CALC_TYPES);

	}

	/**
	 * 源泉徴収金額マスタ更新完了アクション
	 *
	 */
	public function withholdTaxUpdFinishAction() {

		$calc_type = $this->_request->getPost('calc_type');
		$amount_range_from = $this->_request->getPost('amount_range_from');
		$amount_range_to = $this->_request->getPost('amount_range_to');
		$amount_ratio = $this->_request->getPost('amount_ratio');
		$ratio_base_amount = $this->_request->getPost('ratio_base_amount');
		$ratio_base_tax_amount = $this->_request->getPost('ratio_base_tax_amount');
		$tax_amount = $this->_request->getPost('tax_amount');
		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		// 存在チェック
		$dao_withhold_tax = new DaoWithholdTax();
		$withhold_tax = $dao_withhold_tax->select_by_key($id);
		if (!$withhold_tax)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '源泉徴収金額'), __LINE__);	

		try {

			$error_messages = array();

			// 未入力チェック : 算出種別
			if (!$calc_type)
				$error_messages['calc_type'] = sprintf(ERR_MSG_EMPTY, '算出種別');

			// 未入力チェック : 給与金額範囲（開始）
			if (!$amount_range_from)
				$error_messages['amount_range_from'] = sprintf(ERR_MSG_EMPTY, '給与金額範囲（開始）');

			// 未入力チェック : 給与金額範囲（終了）	
			if (!$amount_range_to)
				$error_messages['amount_range_to'] = sprintf(ERR_MSG_EMPTY, '給与金額範囲（終了）');

			// 逆転チェック : 給与金額範囲（終了）	
			if ($amount_range_from > $amount_range_to)
				$error_messages['amount_range_to'] = sprintf(ERR_MSG_UNDER, '給与金額範囲（終了）', '給与金額範囲（開始）');

			switch ($calc_type) {

				case WITHHOLD_TAX_CALC_TYPE_RAGE:

					// 未入力チェック : 算出種別
					if (!$tax_amount)
						$error_messages['tax_amount'] = sprintf(ERR_MSG_EMPTY, '税金額');

					break;

				case WITHHOLD_TAX_CALC_TYPE_RATIO:

					// 未入力チェック : 給与金額加算割合	
					if (!$amount_ratio)
						$error_messages['amount_ratio'] = sprintf(ERR_MSG_EMPTY, '給与金額加算割合');

					// 未入力チェック : 割合加算基準金額	
					if (!$ratio_base_amount)
						$error_messages['ratio_base_amount'] = sprintf(ERR_MSG_EMPTY, '割合加算基準金額');

					// 未入力チェック : 割合加算税金額	
					if (!$ratio_base_tax_amount)
						$error_messages['ratio_base_tax_amount'] = sprintf(ERR_MSG_EMPTY, '割合加算税金額');

					break;				
				
				default:
					$error_messages['calc_type'] = sprintf(ERR_MSG_INPUT, '算出種別');
					break;
			}

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", __LINE__);

			// 更新
			$sets['calc_type'] = $calc_type;
			$sets['amount_range_from'] = $amount_range_from;
			$sets['amount_range_to'] = $amount_range_to;
			if (WITHHOLD_TAX_CALC_TYPE_RATIO == $calc_type) {
				$sets['amount_ratio'] = $amount_ratio;
				$sets['ratio_base_amount'] = $ratio_base_amount;
				$sets['ratio_base_tax_amount'] = $ratio_base_tax_amount;
				$sets['tax_amount'] = null;
			}

			if (WITHHOLD_TAX_CALC_TYPE_RAGE == $calc_type) {
				$sets['amount_ratio'] = null;
				$sets['ratio_base_amount'] = null;
				$sets['ratio_base_tax_amount'] = null;
				$sets['tax_amount'] = $tax_amount;
			}

			$wheres['id'] = $id;
			if (!$dao_withhold_tax->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('withhold_tax_calc_types', WITHHOLD_TAX_CALC_TYPES);

			parent::setAction('withholdTaxUpdForm');

		}

	}

	/**
	 * 源泉徴収金額マスタ削除フォームアクション
	 *
	 */
	public function withholdTaxDelConfirmAction() {

		$id = $this->_request->getQuery('id');

		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		$dao_withhold_tax = new DaoWithholdTax();
		$withhold_tax = $dao_withhold_tax->select_by_key($id);
		if (!$withhold_tax)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '源泉徴収金額'), __LINE__);

		$this->_view->assign('withhold_tax', $withhold_tax);
		$this->_view->assign('withhold_tax_calc_types', WITHHOLD_TAX_CALC_TYPES);

	}

	/**
	 * 源泉徴収金額マスタ削除完了アクション
	 *
	 */
	public function withholdTaxDelFinishAction() {

		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

		// 存在チェック
		$dao_withhold_tax = new DaoWithholdTax();
		$withhold_tax = $dao_withhold_tax->select_by_key($id);
		if (!$withhold_tax)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '源泉徴収金額'), __LINE__);	

		// 削除
		$wheres['id'] = $id;
		if (!$dao_withhold_tax->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

	}

}