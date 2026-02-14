<?php

/**
 * CONTROLLER : Import
 *
 *　@author kanemiya
 */

class ImportController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

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
	public function indexAction() {}

	/**
	 * ユーザインポートフォームアクション
	 *
	 */
	public function userFormAction() {}

	/**
	 * ユーザインポート完了アクション
	 *
	 */
	public function userFinishAction() {

		$is_ignore_header = $this->_request->getPost('is_ignore_header');
		$file = $this->_request->getFiles('csv_file');

		if (!isset($file['tmp_name']) || !$file['tmp_name'] || !file_exists($file['tmp_name']))
			throw new Exception(sprintf(ERR_MSG_READ_FAILED, 'CSVファイル'), __LINE__);

		$row_num = 0;
		$error_messages = array();

		$dao_user = new DaoUser();
		$dao_user->begin();

		$fp = fopen($file['tmp_name'], "r");
		while($row = fgetcsv($fp)) {

			// ヘッダを除外する場合は1行目をスキップする
			if (!$row_num && $is_ignore_header) {
				$row_num++;
				continue;
			}

			// 未入力チェック : コード
			if (!$row[1]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, 'コード');
				$row_num++;
				continue;
			}

			// 未入力チェック : ステータス種別
			if (!$row[2]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, 'ステータス種別');
				$row_num++;
				continue;
			}

			// 未入力チェック : 承認種別
			if (!$row[4]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, 'コード');
				$row_num++;
				continue;
			}

			// 未入力チェック : 氏名
			if (!$row[6]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '氏名');
				$row_num++;
				continue;
			}

			// 未入力チェック : 氏名（ふりがな）
			if (!$row[7]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '氏名（ふりがな）');
				$row_num++;
				continue;
			}

			// メールアドレス重複チェック
			if ($row[9]) {
				$emaiL_user = $dao_user->select_where_one(array('email' => $row[9]));
				if ($emaiL_user && $emaiL_user->id != $row[0]) {
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_DUPLICATE_ENTRY, $row_num, 'メールアドレス');
					$row_num++;
					continue;
				}
			}

			// 未入力チェック : 性別種別
			if (!$row[13]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '性別種別');
				$row_num++;
				continue;
			}

			// 未入力チェック : 国籍ID
			if (!$row[16]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '国籍ID');
				$row_num++;
				continue;
			}

			// 未入力チェック : 雇用形態種別
			if (!$row[18]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '雇用形態種別');
				$row_num++;
				continue;
			}

			// 未入力チェック : 所属会社ID
			if (!mb_strlen($row[20])) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '所属会社ID');
				$row_num++;
				continue;
			}
			
			$id = $row[0];
			$code = $row[1];
			$status_type = $row[2];
			// $status_type_name = $row[3];
			$approve_type = $row[4];
			// $approve_type_name = $row[5];
			$name = mb_convert_encoding($row[6], "UTF-8", "SJIS");
			$name_kana =mb_convert_encoding($row[7], "UTF-8", "SJIS");
			$tel = $row[8];
			$email = $row[9];
			$password = $row[10];
			$post_code = $row[11];
			$address = mb_convert_encoding($row[12], "UTF-8", "SJIS");
			$gender_type = $row[13];
			// $gender_type_name = $row[14];
			$birth_day = $row[15];
			$country_id = $row[16];
			// $country_name = $row[17];
			$employ_type = $row[18];
			// $employ_type_name = $row[19];
			$company_id = $row[20];
			// $company_name = $row[21];
			$etc_company_name = $row[22];

			$user = $dao_user->select_by_key($id);
			if ($user) {

				// 既存更新
				$sets['code'] = $code;
				$sets['status_type'] = $status_type;
				$sets['approve_type'] = $approve_type;
				$sets['name'] = $name;
				$sets['name_kana'] = $name_kana;
				$sets['tel'] = $tel;
				$sets['email'] = $email;
				$sets['post_code'] = $post_code;
				$sets['address'] = $address;
				$sets['gender_type'] = $gender_type;
				$sets['birth_day'] = $birth_day;
				$sets['country_id'] = $country_id;
				$sets['employ_type'] = $employ_type;
				$sets['company_id'] = $company_id;
				$sets['etc_company_name'] = $etc_company_name;
				$wheres['id'] = $id;
				if (false === $dao_user->update($sets, $wheres))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_UPDATE_FAILD, $row_num);

			} else {

				// 新規登録
				$entity_user = new EntityUser();
				$entity_user->id = null;
				$entity_user->code = $code;
				$entity_user->status_type = $status_type;
				$entity_user->approve_type = $approve_type;
				$entity_user->name = $name;
				$entity_user->name_kana = $name_kana;
				$entity_user->tel = $tel;
				$entity_user->email = $email;
				$entity_user->password = $password;
				$entity_user->post_code = $post_code;
				$entity_user->address = $address;
				$entity_user->gender_type = $gender_type;
				$entity_user->birth_day = $birth_day;
				$entity_user->country_id = $country_id;
				$entity_user->employ_type = $employ_type;
				$entity_user->company_id = $company_id;
				$entity_user->etc_company_name = $etc_company_name;
				if (!$dao_user->insert($entity_user))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INSERT_FAILD, $row_num);

			}

			$row_num++;

		}

		if (!count($error_messages)) {
			$dao_user->commit();
		} else {
			$dao_user->rollback();
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('userForm');
			return;
		}

	}

	/**
	 * 現場インポートフォームアクション
	 *
	 */
	public function locationFormAction() {}

	/**
	 * 現場インポート完了アクション
	 *
	 */
	public function locationFinishAction() {

		$is_ignore_header = $this->_request->getPost('is_ignore_header');
		$file = $this->_request->getFiles('csv_file');

		if (!isset($file['tmp_name']) || !$file['tmp_name'] || !file_exists($file['tmp_name']))
			throw new Exception(sprintf(ERR_MSG_READ_FAILED, 'CSVファイル'), __LINE__);

		$row_num = 0;
		$error_messages = array();

		$dao_location = new DaoLocation();
		$dao_location->begin();

		$fp = fopen($file['tmp_name'], "r");
		while($row = fgetcsv($fp)) {

			// ヘッダを除外する場合は1行目をスキップする
			if (!$row_num && $is_ignore_header) {
				$row_num++;
				continue;
			}

			// 未入力チェック : 現場名
			if (!$row[1]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '現場名');
				$row_num++;
				continue;
			}

			// 未入力チェック : 現場日付
			if (!$row[2]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '現場日付');
				$row_num++;
				continue;
			}

			// 未入力チェック : アサイン済みフラグ	
			// if (!$row[5]) {
			// 	$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, 'アサイン済みフラグ');
			// 	$row_num++;
			// 	continue;
			// }

			// // 未入力チェック : 締め処理済みフラグ
			// if (!$row[6]) {
			// 	$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '締め処理済みフラグ');
			// 	$row_num++;
			// 	continue;
			// }
			
			$id = $row[0];
			$name = mb_convert_encoding($row[1], "UTF-8", "SJIS");
			$location_date = $row[2];
			$address = mb_convert_encoding($row[3], "UTF-8", "SJIS");
			$detail = mb_convert_encoding($row[4], "UTF-8", "SJIS");
			$is_assigned = $row[5] ? 1 : 0;
			$is_closed = $row[6] ? 1 : 0;

			$location = $dao_location->select_by_key($id);
			if ($location) {

				// 既存更新
				$sets['name'] = $name;
				$sets['location_date'] = $location_date;
				$sets['address'] = $address;
				$sets['detail'] = $detail;
				$sets['is_assigned'] = $is_assigned;
				$sets['is_closed'] = $is_closed;
				$wheres['id'] = $id;
				if (false === $dao_location->update($sets, $wheres))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_UPDATE_FAILD, $row_num);

			} else {

				// 新規登録
				$entity_location = new EntityLocation();
				$entity_location->id = null;
				$entity_location->name = $name;
				$entity_location->location_date = $location_date;
				$entity_location->address = $address;
				$entity_location->detail = $detail;
				$entity_location->is_assigned = $is_assigned;
				$entity_location->is_closed = $is_closed;
				if (!$dao_location->insert($entity_location))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INSERT_FAILD, $row_num);

			}

			$row_num++;

		}

		if (!count($error_messages)) {
			$dao_location->commit();
		} else {
			$dao_location->rollback();
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('locationForm');
			return;
		}

	}

	/**
	 * 現場シフトインポートフォームアクション
	 *
	 */
	public function locationShiftFormAction() {}

	/**
	 * 現場シフトインポート完了アクション
	 *
	 */
	public function locationShiftFinishAction() {

		$is_ignore_header = $this->_request->getPost('is_ignore_header');
		$file = $this->_request->getFiles('csv_file');

		if (!isset($file['tmp_name']) || !$file['tmp_name'] || !file_exists($file['tmp_name']))
			throw new Exception(sprintf(ERR_MSG_READ_FAILED, 'CSVファイル'), __LINE__);

		$row_num = 0;
		$error_messages = array();

		$dao_location_shift = new DaoLocationShift();
		$dao_location_shift->begin();

		$fp = fopen($file['tmp_name'], "r");
		while($row = fgetcsv($fp)) {

			// ヘッダを除外する場合は1行目をスキップする
			if (!$row_num && $is_ignore_header) {
				$row_num++;
				continue;
			}

			// 未入力チェック : 現場ID
			if (!$row[1]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '現場ID');
				$row_num++;
				continue;
			}

			// 未入力チェック : 現場シフト名
			if (!$row[3]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '現場シフト名');
				$row_num++;
				continue;
			}

			// 未入力チェック : 現場シフト日時（開始）	
			if (!$row[4]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '現場シフト日時（開始）');
				$row_num++;
				continue;
			}

			// 未入力チェック : 現場シフト日時（終了）
			if (!$row[5]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '現場シフト日時（終了）');
				$row_num++;
				continue;
			}

			// 日時逆転チェック : 現場シフト日時（終了）
			if (strtotime($row[4]) > strtotime($row[5])) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INPUT_PARAM, $row_num, '現場シフト日時（終了）');
				$row_num++;
				continue;
			} 
			
			$id = $row[0];
			$location_id = $row[1];
			// $location_name = $row[2];
			$name = mb_convert_encoding($row[3], "UTF-8", "SJIS");
			$shift_date_from = $row[4];
			$shift_date_to = $row[5];
			$note = mb_convert_encoding($row[6], "UTF-8", "SJIS");

			$location_shift = $dao_location_shift->select_by_key($id);
			if ($location_shift) {

				// 既存更新
				$sets['location_id'] = $location_id;
				$sets['name'] = $name;
				$sets['shift_date_from'] = $shift_date_from;
				$sets['shift_date_to'] = $shift_date_to;
				$sets['note'] = $note;
				$wheres['id'] = $id;
				if (false === $dao_location_shift->update($sets, $wheres))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_UPDATE_FAILD, $row_num);

			} else {

				// 新規登録
				$entity_location_shift = new EntityLocationShift();
				$entity_location_shift->id = null;
				$entity_location_shift->location_id = $location_id;
				$entity_location_shift->name = $name;
				$entity_location_shift->shift_date_from = $shift_date_from;
				$entity_location_shift->shift_date_to = $shift_date_to;
				$entity_location_shift->note = $note;
				if (!$dao_location_shift->insert($entity_location_shift))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INSERT_FAILD, $row_num);

			}

			$row_num++;

		}

		if (!count($error_messages)) {
			$dao_location_shift->commit();
		} else {
			$dao_location_shift->rollback();
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('locationShiftForm');
			return;
		}

	}

	/**
	 * 国籍マスタインポートフォームアクション
	 *
	 */
	public function countryFormAction() {}

	/**
	 * 国籍マスタインポート完了アクション
	 *
	 */
	public function countryFinishAction() {

		$is_ignore_header = $this->_request->getPost('is_ignore_header');
		$file = $this->_request->getFiles('csv_file');

		if (!isset($file['tmp_name']) || !$file['tmp_name'] || !file_exists($file['tmp_name']))
			throw new Exception(sprintf(ERR_MSG_READ_FAILED, 'CSVファイル'), __LINE__);

		$row_num = 0;
		$error_messages = array();

		$dao_country = new DaoCountry();
		$dao_country->begin();

		$fp = fopen($file['tmp_name'], "r");
		while($row = fgetcsv($fp)) {

			// ヘッダを除外する場合は1行目をスキップする
			if (!$row_num && $is_ignore_header) {
				$row_num++;
				continue;
			}

			// 未入力チェック : 名称
			if (!$row[1]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '名称');
				$row_num++;
				continue;
			}
			
			$id = $row[0];
			$name = mb_convert_encoding($row[1], "UTF-8", "SJIS");

			$country = $dao_country->select_by_key($id);
			if ($country) {

				// 既存更新
				$sets['name'] = $name;
				$wheres['id'] = $id;
				if (false === $dao_country->update($sets, $wheres))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_UPDATE_FAILD, $row_num);

			} else {

				// 新規登録
				$entity_country = new EntityCountry();
				$entity_country->id = null;
				$entity_country->name = $name;
				if (!$dao_country->insert($entity_country))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INSERT_FAILD, $row_num);

			}

			$row_num++;

		}

		if (!count($error_messages)) {
			$dao_country->commit();
		} else {
			$dao_country->rollback();
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('countryForm');
			return;
		}

	}

	/**
	 * 所属会社マスタインポートフォームアクション
	 *
	 */
	public function companyFormAction() {}

	/**
	 * 所属会社マスタインポート完了アクション
	 *
	 */
	public function companyFinishAction() {

		$is_ignore_header = $this->_request->getPost('is_ignore_header');
		$file = $this->_request->getFiles('csv_file');

		if (!isset($file['tmp_name']) || !$file['tmp_name'] || !file_exists($file['tmp_name']))
			throw new Exception(sprintf(ERR_MSG_READ_FAILED, 'CSVファイル'), __LINE__);

		$row_num = 0;
		$error_messages = array();

		$dao_company = new DaoCompany();
		$dao_company->begin();

		$fp = fopen($file['tmp_name'], "r");
		while($row = fgetcsv($fp)) {

			// ヘッダを除外する場合は1行目をスキップする
			if (!$row_num && $is_ignore_header) {
				$row_num++;
				continue;
			}

			// 未入力チェック : 名称
			if (!$row[1]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '名称');
				$row_num++;
				continue;
			}
			
			$id = $row[0];
			$name = mb_convert_encoding($row[1], "UTF-8", "SJIS");

			$company = $dao_company->select_by_key($id);
			if ($company) {

				// 既存更新
				$sets['name'] = $name;
				$wheres['id'] = $id;
				if (false === $dao_company->update($sets, $wheres))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_UPDATE_FAILD, $row_num);

			} else {

				// 新規登録
				$entity_company = new EntityCompany();
				$entity_company->id = null;
				$entity_company->name = $name;
				if (!$dao_company->insert($entity_company))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INSERT_FAILD, $row_num);

			}

			$row_num++;

		}

		if (!count($error_messages)) {
			$dao_company->commit();
		} else {
			$dao_company->rollback();
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('countryForm');
			return;
		}

	}

	/**
	 * 資格証明書マスタインポートフォームアクション
	 *
	 */
	public function certFormAction() {}

	/**
	 * 資格証明書マスタインポート完了アクション
	 *
	 */
	public function certFinishAction() {

		$is_ignore_header = $this->_request->getPost('is_ignore_header');
		$file = $this->_request->getFiles('csv_file');

		if (!isset($file['tmp_name']) || !$file['tmp_name'] || !file_exists($file['tmp_name']))
			throw new Exception(sprintf(ERR_MSG_READ_FAILED, 'CSVファイル'), __LINE__);

		$row_num = 0;
		$error_messages = array();

		$dao_cert = new DaoCert();
		$dao_cert->begin();

		$fp = fopen($file['tmp_name'], "r");
		while($row = fgetcsv($fp)) {

			// ヘッダを除外する場合は1行目をスキップする
			if (!$row_num && $is_ignore_header) {
				$row_num++;
				continue;
			}

			// 未入力チェック : 名称
			if (!$row[1]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '名称');
				$row_num++;
				continue;
			}

			// 未入力チェック : 略称
			if (!$row[2]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '略称');
				$row_num++;
				continue;
			}
			
			$id = $row[0];
			$name = mb_convert_encoding($row[1], "UTF-8", "SJIS");
			$short_name = mb_convert_encoding($row[2], "UTF-8", "SJIS");
			$allowance = $row[3];

			$cert = $dao_cert->select_by_key($id);
			if ($cert) {

				// 既存更新
				$sets['name'] = $name;
				$sets['short_name'] = $short_name;
				$sets['allowance'] = $allowance;
				$wheres['id'] = $id;
				if (false === $dao_cert->update($sets, $wheres))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_UPDATE_FAILD, $row_num);

			} else {

				// 新規登録
				$entity_cert = new EntityCert();
				$entity_cert->id = null;
				$entity_cert->name = $name;
				$entity_cert->short_name = $short_name;
				$entity_cert->allowance = $allowance;
				if (!$dao_cert->insert($entity_cert))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INSERT_FAILD, $row_num);

			}

			$row_num++;

		}

		if (!count($error_messages)) {
			$dao_cert->commit();
		} else {
			$dao_cert->rollback();
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('certForm');
			return;
		}

	}

	/**
	 * 源泉徴収金額マスタインポートフォームアクション
	 *
	 */
	public function withholdTaxFormAction() {}

	/**
	 * 源泉徴収金額マスタインポート完了アクション
	 *
	 */
	public function withholdTaxFinishAction() {

		$is_ignore_header = $this->_request->getPost('is_ignore_header');
		$file = $this->_request->getFiles('csv_file');

		if (!isset($file['tmp_name']) || !$file['tmp_name'] || !file_exists($file['tmp_name']))
			throw new Exception(sprintf(ERR_MSG_READ_FAILED, 'CSVファイル'), __LINE__);

		$row_num = 0;
		$error_messages = array();

		$dao_withhold_tax = new DaoWithholdTax();
		$dao_withhold_tax->begin();

		$fp = fopen($file['tmp_name'], "r");
		while($row = fgetcsv($fp)) {

			// ヘッダを除外する場合は1行目をスキップする
			if (!$row_num && $is_ignore_header) {
				$row_num++;
				continue;
			}

			// 未入力チェック : 算出種別
			if (!$row[1]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '算出種別');
				$row_num++;
				continue;
			}

			// 未入力チェック : 給与金額範囲（開始）
			if (!$row[3]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '給与金額範囲（開始）');
				$row_num++;
				continue;
			}

			// 未入力チェック : 給与金額範囲（終了）
			if (!$row[4]) {
				$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_EMPTY, $row_num, '給与金額範囲（終了）');
				$row_num++;
				continue;
			}
			
			$id = $row[0];
			$calc_type = $row[1];
			// $calc_type_name = $row[2];
			$amount_range_from = $row[3];
			$amount_range_to = $row[4];
			$amount_ratio = $row[5];
			$ratio_base_amount = $row[6];
			$ratio_base_tax_amount = $row[7];
			$tax_amount = $row[8];

			$withhold_tax = $dao_withhold_tax->select_by_key($id);
			if ($withhold_tax) {

				// 既存更新
				$sets['calc_type'] = $calc_type;
				$sets['amount_range_from'] = $amount_range_from;
				$sets['amount_range_to'] = $amount_range_to;
				$sets['amount_ratio'] = $amount_ratio;
				$sets['ratio_base_amount'] = $ratio_base_amount;
				$sets['ratio_base_tax_amount'] = $ratio_base_tax_amount;
				$sets['tax_amount'] = $tax_amount;
				$wheres['id'] = $id;
				if (false === $dao_withhold_tax->update($sets, $wheres))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_UPDATE_FAILD, $row_num);

			} else {

				// 新規登録
				$entity_withhold_tax = new EntityWithholdTax();
				$entity_withhold_tax->id = null;
				$entity_withhold_tax->calc_type = $calc_type;
				$entity_withhold_tax->amount_range_from = $amount_range_from;
				$entity_withhold_tax->amount_range_to = $amount_range_to;
				$entity_withhold_tax->amount_ratio = $amount_ratio;
				$entity_withhold_tax->ratio_base_amount = $ratio_base_amount;
				$entity_withhold_tax->ratio_base_tax_amount = $ratio_base_tax_amount;
				$entity_withhold_tax->tax_amount = $tax_amount;
				if (!$dao_withhold_tax->insert($entity_withhold_tax))
					$error_messages[] = sprintf(ERR_MSG_CSV_IMPORT_INSERT_FAILD, $row_num);

			}

			$row_num++;

		}

		if (!count($error_messages)) {
			$dao_withhold_tax->commit();
		} else {
			$dao_withhold_tax->rollback();
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('certForm');
			return;
		}

	}

}