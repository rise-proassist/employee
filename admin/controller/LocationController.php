<?php

/**
 * CONTROLLER : Location
 *
 *　@author kanemiya
 */

class LocationController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const PAGER_PER_PAGE = 10;

	const PAGER_DELTA = 5;

	const NAME_LENGTH = 30;

	const VALUE_LENGTH = 30;

	const ASSIGN_LIST_ROW_NUM = 4;

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
	 * 現場一覧アクション
	 *
	 */
	public function listAction() {

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

				case PARAM_CONST_LIST_SORT_ASC:
					$sorts[$sort_key] = 'ASC';
					break;

				case PARAM_CONST_LIST_SORT_DESC:
				default:
					$sorts[$sort_key] = 'ASC';
					break;

			}

		}

		// 現場
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_where($wheres, $sorts, self::PAGER_PER_PAGE, $offset);

		// 全件数
		$total_num = $dao_location->select_count($wheres);

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/location/list/');
		$pager->set_query('name', $name);
		$pager->create();

		$this->_view->assign('locations', $locations);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 現場一覧CSVエクスポートアクション
	 *
	 */
	public function listCsvExportAction() {

		$name = $this->_request->getPost('name');

		// 現場
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_where(
			array('name' => $name)
		);

		$headers = array(
			'ID',
			'現場名',
			'現場日付',
			'住所（集合場所）',
			'詳細',
			'アサイン済みフラグ',
			'締め処理済みフラグ',
		);

		$datas = array();
		foreach ($locations as $location) {
			$data['id'] = $location->id;
			$data['name'] = $location->name;
			$data['locaton_date'] = $location->location_date;
			$data['address'] = $location->address;
			$data['detail'] = $location->detail;
			$data['is_assigned'] = $location->is_assigned;
			$data['is_closed'] = $location->is_closed;
			$datas[] = $data;
		}


		UtilFile::get_csv(date("YmdHis") . '_locations', $datas, $headers);
		exit;

	}

	/**
	 * 現場登録フォームアクション
	 *
	 */
	public function addFormAction() {}

	/**
	 * 現場登録完了アクション
	 *
	 */
	public function addFinishAction() {

		$name = $this->_request->getPost('name');
		$location_date = $this->_request->getPost('location_date');
		$address = $this->_request->getPost('address');
		$detail = $this->_request->getPost('detail');

		try {

			$error_messages = array();

			// 未入力チェック : 現場名
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '現場名');

			// 文字数チェック : 現場名
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '現場名', self::NAME_LENGTH);

			// 未入力チェック : 現場日付
			if (!$location_date)
				$error_messages['location_date'] = sprintf(ERR_MSG_EMPTY, '現場日付');

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 登録
			$entity_location = new EntityLocation();
			$entity_location->id = null;
			$entity_location->name = $name;
			$entity_location->location_date = $location_date;
			$entity_location->address = $address;
			$entity_location->detail = $detail;
			$entity_location->is_assigned = false;
			$entity_location->is_closed = false;
			$dao_location = new DaoLocation();
			if (!$dao_location->insert($entity_location))
				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());

			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('addForm');

		}

	}

	/**
	 * 現場更新フォームアクション
	 *
	 */
	public function updFormAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		$dao_location = new DaoLocation();
		$location = $dao_location->select_by_key($id);

		if (!$location)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, ''), __LINE__);	

		$this->_view->assign('location', $location);

	}

	/**
	 * 現場更新完了アクション
	 *
	 */
	public function updFinishAction() {

		$name = $this->_request->getPost('name');
		$location_date = $this->_request->getPost('location_date');
		$address = $this->_request->getPost('address');
		$detail = $this->_request->getPost('detail');
		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		try {

			$error_messages = array();

			// 未入力チェック : 現場名
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '現場名');

			// 文字数チェック : 現場名
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '現場名', self::NAME_LENGTH);

			// 未入力チェック : 現場日付
			if (!$location_date)
				$error_messages['location_date'] = sprintf(ERR_MSG_EMPTY, '現場日付');

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 現場更新
			$sets['name'] = $name;
			$sets['location_date'] = $location_date;
			$sets['address'] = $address;
			$sets['detail'] = $detail;
			$wheres['id'] = $id;
			$dao_location = new DaoLocation();
			if (!$dao_location->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 現場シフトの登録があれば、シフト日時更新
			$dao_location_shift = new DaoLocationShift();
			$location_shifts = $dao_location_shift->select_where(array('location_id' => $id));
			foreach ((array)$location_shifts as $location_shift) {
				$sets = null;
				$sets['shift_date_from'] = $location_date . ' ' . substr($location_shift->shift_date_from, 11);
				$sets['shift_date_to'] = $location_date . ' ' . substr($location_shift->shift_date_to, 11);
				$wheres['id'] = $location_shift->id;
				if (false === $dao_location_shift->update($sets, $wheres))
					throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			}

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);

			parent::setAction('updForm');

		}

	}

	/**
	 * 現場削除フォームアクション
	 *
	 */
	public function delConfirmAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_location = new DaoLocation();
		$location = $dao_location->select_by_key($id);

		if (!$location)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場'), self::ERROR_CODE_NOT_FOUND);

		// マスタに紐づくデータがある場合、削除不可
		// $dao_user = new DaoUser();
		// $is_del = !$dao_user->select_count(array('country_id' => $id)) ? true : false;
		$is_del = true; // TODO

		$this->_view->assign('location', $location);
		$this->_view->assign('is_del', $is_del);

	}


	/**
	 * 現場削除完了アクション
	 *
	 */
	public function delFinishAction() {

		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		// 存在チェック
		$dao_location = new DaoLocation();
		$location = $dao_location->select_by_key($id);
		if (!$location)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		// 削除
		$wheres['id'] = $id;
		if (!$dao_location->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

	}

	/**
	 * 現場締め処理完了アクション
	 *
	 */
	public function updIsClosedFinishAction() {

		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		// 存在チェック
		$dao_location = new DaoLocation();
		$location = $dao_location->select_by_key($id);
		if (!$location)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場情報'), __LINE__);

		// 締め処理済みチェック
		if ($location->is_closed)
			throw new Exception(sprintf(ERR_MSG_CLOSED, '現場'), __LINE__);

		$dao_location->begin();

		// 締め処理フラグ更新
		$sets['is_closed'] = true;
		$wheres['id'] = $id;
		if (false === $dao_location->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		// 現場アサイン従事者実績を登録
		$model_location = new ModelLocation($id);
		$location = $model_location->get();
		$dao_location_assign_user = new DaoLocationAssignUser();
		foreach ($location->location_shifts as $location_shift) {

			$sets = null;
			$wheres = null;
			foreach ((array)$location_shift->location_assign_users as $location_assign_user) {

				$sets['receipt_user_name'] = $location_assign_user->name;
				$sets['receipt_user_post_code'] = $location_assign_user->post_code;
				$sets['receipt_user_address'] = $location_assign_user->address;
				$sets['receipt_user_tel'] = $location_assign_user->tel;
				$sets['receipt_user_birth_day'] = $location_assign_user->birth_day;
				$sets['is_confirmed'] = false;
				$sets['is_paid'] = false;
				$wheres['id'] = $location_assign_user->id;
				if (false === $dao_location_assign_user->update($sets, $wheres))
					throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			}

		}

		$dao_location->commit();
		// $dao_location->rollback();

	}

	/**
	 * 現場シフト一覧アクション
	 *
	 */
	public function shiftListAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$location_id = $this->_request->getQuery('location_id');

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		$wheres = null;
		if ($location_id)
			$wheres['location_id'] = $location_id;

		$sorts = null;
		if ($sort_key && $sort_type) {

			switch ($sort_type) {

				case PARAM_CONST_LIST_SORT_ASC:
					$sorts[$sort_key] = 'ASC';
					break;

				case PARAM_CONST_LIST_SORT_DESC:
				default:
					$sorts[$sort_key] = 'DESC';
					break;

			}

		} else {
			$sorts['id'] = 'DESC';
		}

		// 現場
		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where_with_location(
			$wheres,
			$sorts,
			// array('l.location_date' => 'DESC', 'ls.shift_date_from' => 'ASC'),
			self::PAGER_PER_PAGE,
			$offset
		);

		// 全件数
		$total_num = $dao_location_shift->select_count($wheres);

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/location/shiftList/');
		$pager->set_query('location_id', $location_id);
		$pager->create();

		// 現場
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_all(array('location_date' => 'DESC'));

		$this->_view->assign('location_shifts', $location_shifts);
		$this->_view->assign('locations', $locations);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 現場シフト一覧CSVエクスポートアクション
	 *
	 */
	public function shiftListCsvExportAction() {

		$location_id = $this->_request->getPost('location_id');

		$wheres = null;
		if ($location_id)
			$wheres['location_id'] = $location_id;

		// 現場
		$dao_location_shift = new DaoLocationShift();
		$location_shifts = $dao_location_shift->select_where_with_location(
			$wheres
		);

		$headers = array(
			'ID',
			'現場ID*',
			'現場名',
			'現場シフト名*',
			'現場シフト日時（開始）*',
			'現場シフト日時（終了）*',
			'備考',
		);

		$datas = array();
		foreach ($location_shifts as $location_shift) {
			$data['id'] = $location_shift->id;
			$data['location_id'] = $location_shift->location_id;
			$data['location_name'] = $location_shift->location_name;
			$data['shift_name'] = $location_shift->shift_name;
			$data['shift_date_from'] = $location_shift->shift_date_from;
			$data['shift_date_to'] = $location_shift->shift_date_to;
			$data['note'] = $location_shift->note;
			$datas[] = $data;
		}

		UtilFile::get_csv(date("YmdHis") . '_location_shifts', $datas, $headers);
		exit;

	}

	/**
	 * 現場シフト登録フォームアクション
	 *
	 */
	public function shiftAddFormAction() {

		// 現場
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_all(array('location_date' => 'DESC'));
		foreach ($locations as $key => $location) {

			// 過去の現場は削除
			if ($location->location_date < date("Y-m-d"))
				unset($locations[$key]);

		}

		$this->_view->assign('locations', $locations);

	}

	/**
	 * 現場シフト登録完了アクション
	 *
	 */
	public function shiftAddFinishAction() {

		$location_id = $this->_request->getPost('location_id');
		$name = $this->_request->getPost('name');
		$shift_date_from = $this->_request->getPost('shift_date_from');
		$shift_time_from = $this->_request->getPost('shift_time_from');
		$shift_date_to = $this->_request->getPost('shift_date_to');
		$shift_time_to = $this->_request->getPost('shift_time_to');
		$note = $this->_request->getPost('note');

		try {

			$error_messages = array();

			// 未入力チェック : 現場ID
			if (!$location_id)
				$error_messages['location_id'] = sprintf(ERR_MSG_EMPTY, '現場');

			// 存在チェック : 現場ID
			$dao_location = new DaoLocation();
			$location = $dao_location->select_by_key($location_id);
			if (!$location)
				$error_messages['location_id'] = sprintf(ERR_MSG_NOT_FOUND, '現場');

			// 未入力チェック : 現場名
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '現場名');

			// 文字数チェック : 現場名
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '現場名', self::NAME_LENGTH);

			// 未入力チェック : シフト時間（開始）
			if (!$shift_time_from)
				$error_messages['shift_time'] = sprintf(ERR_MSG_EMPTY, 'シフト時間（開始）');

			// 未入力チェック : シフト時間（終了）
			if (!$shift_time_to)
				$error_messages['shift_time'] = sprintf(ERR_MSG_EMPTY, 'シフト時間（終了）');

			// 日時逆転チェック : シフト時間
			$shift_date_from = $shift_date_from . ' ' . $shift_time_from . ':00';
			$shift_date_to = $shift_date_to . ' ' . $shift_time_to . ':00';
			if (strtotime($shift_date_from) > strtotime($shift_date_to))
				$error_messages['shift_time'] = sprintf(ERR_MSG_DATE_REVERSE, 'シフト時間（開始）', 'シフト時間（終了）');

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", __LINE__);

			// 登録
			$entity_location_shift = new EntityLocationShift();
			$entity_location_shift->id = null;
			$entity_location_shift->location_id = $location_id;
			$entity_location_shift->name = $name;
			$entity_location_shift->shift_date_from = $shift_date_from;
			$entity_location_shift->shift_date_to = $shift_date_to;
			$entity_location_shift->note = $note;
			$dao_location_shift = new DaoLocationShift();
			if (!$dao_location_shift->insert($entity_location_shift))
				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);

			// 現場
			$dao_location = new DaoLocation();
			$locations = $dao_location->select_all();
			$this->_view->assign('locations', $locations);

			parent::setAction('shiftAddForm');

		}

	}

	/**
	 * 現場シフト更新フォームアクション
	 *
	 */
	public function shiftUpdFormAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		// 存在チェック
		$dao_location_shift = new DaoLocationShift();
		$location_shift = $dao_location_shift->select_where_with_location(array('ls.id' => $id));
		if (!$location_shift)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場シフト'), __LINE__);

		// 現場
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_all(array('location_date' => 'DESC'));

		$this->_view->assign('locations', $locations);
		$this->_view->assign('location_shift', $location_shift[0]);

	}

	/**
	 * 現場シフト更新完了アクション
	 *
	 */
	public function shiftUpdFinishAction() {

		$location_id = $this->_request->getPost('location_id');
		$name = $this->_request->getPost('name');
		$request_num = $this->_request->getPost('request_num');
		$shift_date_from = $this->_request->getPost('shift_date_from');
		$shift_time_from = $this->_request->getPost('shift_time_from');
		$shift_date_to = $this->_request->getPost('shift_date_to');
		$shift_time_to = $this->_request->getPost('shift_time_to');
		$note = $this->_request->getPost('note');
		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		try {

			$error_messages = array();

			// 存在チェック
			$dao_location_shift = new DaoLocationShift();
			$location_shift = $dao_location_shift->select_by_key($id);

			// 未入力チェック : 現場ID
			if (!$location_id)
				$error_messages['location_id'] = sprintf(ERR_MSG_EMPTY, '現場');

			// 存在チェック : 現場ID
			$dao_location = new DaoLocation();
			$location = $dao_location->select_by_key($location_id);
			if (!$location)
				$error_messages['location_id'] = sprintf(ERR_MSG_NOT_FOUND, '現場');

			// 未入力チェック : 現場名
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '現場名');

			// 文字数チェック : 現場名
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '現場名', self::NAME_LENGTH);

			// 未入力チェック : 要請数
			if (!$request_num)
				$error_messages['request_num'] = sprintf(ERR_MSG_EMPTY, '要請数');

			// 未入力チェック : シフト時間（開始）
			if (!$shift_time_from)
				$error_messages['shift_time'] = sprintf(ERR_MSG_EMPTY, 'シフト時間（開始）');

			// 未入力チェック : シフト時間（終了）
			if (!$shift_time_to)
				$error_messages['shift_time'] = sprintf(ERR_MSG_EMPTY, 'シフト時間（終了）');

			// 日時逆転チェック : シフト時間
			$shift_date_from = $shift_date_from . ' ' . $shift_time_from . ':00';
			$shift_date_to = $shift_date_to . ' ' . $shift_time_to . ':00';
			if (strtotime($shift_date_from) > strtotime($shift_date_to))
				$error_messages['shift_time'] = sprintf(ERR_MSG_DATE_REVERSE, 'シフト時間（開始）', 'シフト時間（終了）');

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 更新
			$sets['location_id'] = $location_id;
			$sets['name'] = $name;
			$sets['request_num'] = $request_num;
			$sets['shift_date_from'] = $shift_date_from;
			$sets['shift_date_to'] = $shift_date_to;
			$sets['note'] = $note;
			$wheres['id'] = $id;
			if (false === $dao_location_shift->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);

			// 現場
			$dao_location = new DaoLocation();
			$locations = $dao_location->select_all(array('location_date' => 'DESC'));

			$this->_view->assign('locations', $locations);
			$this->_view->assign('location_shift', $location_shift);

			parent::setAction('shiftUpdForm');

		}

	}

	/**
	 * 現場シフト更新フォームアクション
	 *
	 */
	public function shiftDelConfirmAction() {

		$id = $this->_request->getQuery('id');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		// 存在チェック
		$dao_location_shift = new DaoLocationShift();
		$location_shift = $dao_location_shift->select_where_with_location(array('ls.id' => $id));
		if (!$location_shift)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場シフト'), __LINE__);

		// マスタに紐づくデータがある場合、削除不可
		// $dao_user = new DaoUser();
		// $is_del = !$dao_user->select_count(array('country_id' => $id)) ? true : false;
		$is_del = true; // TODO

		$this->_view->assign('location_shift', $location_shift[0]);
		$this->_view->assign('is_del', $is_del);

	}


	/**
	 * 現場シフト削除完了アクション
	 *
	 */
	public function shiftDelFinishAction() {

		$id = $this->_request->getPost('id');

		// 未入力チェック : 通番
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		// 存在チェック
		$dao_location_shift = new DaoLocationShift();
		$location_shift = $dao_location_shift->select_by_key($id);
		if (!$location_shift)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		// 削除
		$wheres['id'] = $id;
		if (!$dao_location_shift->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

	}

	/**
	 * アサイン一覧アクション
	 *
	 */
	public function assignListAction() {

		$location_id = $this->_request->getQuery('id');

		$users = null;
		$certs = null;
		$location = null;
		$locations = null;
		$location_shifts = null;
		$location_shift_users = null;

		// 締め処理がされていない現場
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_where(array('is_closed' => 0), array('location_date' => 'DESC'));

		if ($location_id) {

			// 選択した現場情報
			$model_location = new ModelLocation($location_id);
			$location = $model_location->get();
			// $location_shifts = $location->location_shifts;

			// 有効な承認済みの全従事者情報
			$dao_user_request_shift = new DaoUserRequestShift();
			$user_request_shifts = $dao_user_request_shift->select_where_with_user(
				array(
					'urs.shift_date_from' => $location->location_date_to,
					'urs.shift_date_to' => $location->location_date_from,
					'u.status_type' => PARAM_CONST_USER_STATUS_TYPE_ENABLE,
					'u.approve_type' => PARAM_CONST_USER_APPROVE_TYPE_PASSED,
				)
			);

			$user_request_shift_user_ids = null;
			foreach ((array)$user_request_shifts as $user_request_shift) {
				$user_request_shift_user_ids[] = $user_request_shift->user_id;
			}

			$user_request_shift_users = null;
			if ($user_request_shift_user_ids) {
				$model_users = new ModelUsers(array_unique($user_request_shift_user_ids));
				$user_request_shift_users = $model_users->gets();
			}

			$model_user_search = new ModelUserSearch();
			$model_user_search->set_status_type(PARAM_CONST_USER_STATUS_TYPE_ENABLE);
			$model_user_search->set_approve_type(PARAM_CONST_USER_APPROVE_TYPE_PASSED);
			$model_user_search->search();
			$users = $model_user_search->get();

			$location_shift_users = null;
			foreach ((array)$location->location_shifts as $location_shift) {

				$location_shift_users[$location_shift->id] = array();
				$location_shift_user_ids = array();
				foreach ((array)$user_request_shifts as $user_request_shift) {

					if ($user_request_shift->shift_date_from <= $location_shift->shift_date_from && 
						$user_request_shift->shift_date_to >= $location_shift->shift_date_to
					){
						$location_shift_user = clone $users[$user_request_shift->user_id];
						$location_shift_user->name = '★ ' . $location_shift_user->name;

						$location_shift_users[$location_shift->id][] = $location_shift_user;
						$location_shift_user_ids[] = $location_shift_user->id;
					}

				}
				foreach ((array)$users as $user) {

					if (!in_array($user->id, $location_shift_user_ids, true))
						$location_shift_users[$location_shift->id][] = $user;

				}

			}

// echo '<pre>';
// var_dump($location);exit;

			// 選択できる資格情報
			$dao_cert = new DaoCert();
			$certs = $dao_cert->select_where_allowances();

		}

		$model_session = new ModelSession();
		$model_session->set_dir(ADMIN_SESSION_DIR);
		$model_session->open();
		$csrf_token = bin2hex(random_bytes(32));
		$model_session->set('csrf_token', $csrf_token);

		$this->_view->assign('location', $location);
		$this->_view->assign('locations', $locations);
		// $this->_view->assign('location_shifts', $location_shifts);
		$this->_view->assign('location_shift_users', $location_shift_users);
		$this->_view->assign('certs', $certs);
		$this->_view->assign('assign_list_row_num', self::ASSIGN_LIST_ROW_NUM);
		$this->_view->assign('csrf_token', $csrf_token);

	}
	
	// public function assignListAction() {

	// 	$location_id = $this->_request->getQuery('id');

	// 	$sort_key = $this->_request->getQuery('sort_key');
	// 	$sort_type = $this->_request->getQuery('sort_type');

	// 	$sorts = null;
	// 	if ($sort_key && $sort_type) {

	// 		switch ($sort_type) {

	// 			case PARAM_CONST_LIST_SORT_DESC:
	// 				$sorts[$sort_key] = 'DESC';
	// 				break;

	// 			case PARAM_CONST_LIST_SORT_ASC:
	// 			default:
	// 				$sorts[$sort_key] = 'ASC';
	// 				break;

	// 		}

	// 	}

	// 	// 現場
	// 	$dao_location = new DaoLocation();
	// 	$locations = $dao_location->select_all(array('location_date' => 'DESC'));

	// 	$shift_users = array();
	// 	$location_assign_users = array();
	// 	$location = null;
	// 	if ($location_id) {

	// 		// 選択した現場情報
	// 		$model_location = new ModelLocation($location_id);
	// 		$location = $model_location->get();
	// 		if (!$location)
	// 			throw new Exception(ERR_MSG_PARAM, __LINE__);

	// 		// シフト登録済みチェック
	// 		if ($model_location->get_shift_date_from() && $model_location->get_shift_date_to()) {

	// 			// 現場日付のシフト希望従事者
	// 			$dao_user_request_shift = new DaoUserRequestShift();
	// 			$user_request_shifts = $dao_user_request_shift->select_where_with_user_for_range_check(
	// 				array(
	// 					'urs.shift_date_from' => $model_location->get_shift_date_to(), // 意図的にfromとtoを逆にする
	// 					'urs.shift_date_to' => $model_location->get_shift_date_from(), // 意図的にfromとtoを逆にする
	// 				),
	// 				$sorts,
	// 			);
	// 			$user_ids = array();
	// 			foreach ((array)$user_request_shifts as $user_request_shift) {
	// 				$user_ids[] = $user_request_shift->user_id;
	// 			}
	// 			$model_users = new ModelUsers($user_ids);

	// 			// アサイン済みの従事者
	// 			$dao_location_assign_user = new DaoLocationAssignUser();
	// 			$location_assign_users = $dao_location_assign_user->select_where_with_user(
	// 				array('ls.location_id' => $location_id),
	// 				array('ls.shift_date_from' => 'ASC')
	// 			);

	// 			// アサイン情報のデータ構造を生成する
	// 			foreach ((array)$user_request_shifts as $user_request_shift) {

	// 				if (!isset($shift_users[$user_request_shift->user_id])) {
	// 					$shift_user_class = new stdClass();
	// 					$shift_user_class->user = $model_users->get($user_request_shift->user_id);
	// 					$shift_user_class->shift_date_from = $user_request_shift->shift_date_from;
	// 					$shift_user_class->shift_date_to = $user_request_shift->shift_date_to;
	// 					$shift_user_class->shift_assign_types = null;
	// 					$shift_user_class->is_notified = false;
	// 				}
					
	// 				$shift_assign_types = null;
	// 				if (isset($shift_users[$user_request_shift->user_id]->shift_assign_types))
	// 					$shift_assign_types = $shift_users[$user_request_shift->user_id]->shift_assign_types;

	// 				foreach ((array)$location->location_shifts as $location_shift) {

	// 					if (isset($shift_assign_types[$location_shift->id]) && PARAM_CONST_SHIFT_ASSIGN_TYPE_IMPOSSIBLE != $shift_assign_types[$location_shift->id])
	// 						continue;

	// 					$shift_assign_types[$location_shift->id] = PARAM_CONST_SHIFT_ASSIGN_TYPE_IMPOSSIBLE;
	// 					foreach ((array)$location_assign_users as $location_assign_user) {
	// 						if (
	// 							$location_assign_user->location_shift_id == $location_shift->id && 
	// 							$location_assign_user->user_id == $user_request_shift->user_id
	// 						) {
	// 							$shift_assign_types[$location_shift->id] = PARAM_CONST_SHIFT_ASSIGN_TYPE_ASSIGNED;
	// 							$shift_user_class->is_notified = $location_assign_user->is_notified;
	// 							break;
	// 						}
	// 					}

	// 					if ($shift_assign_types[$location_shift->id] == PARAM_CONST_SHIFT_ASSIGN_TYPE_ASSIGNED)
	// 						continue;

	// 					if (
	// 						strtotime($location_shift->shift_date_from) >= strtotime($user_request_shift->shift_date_from) &&
	// 						strtotime($location_shift->shift_date_to) <= strtotime($user_request_shift->shift_date_to)
	// 					) {
	// 						$shift_assign_types[$location_shift->id] = PARAM_CONST_SHIFT_ASSIGN_TYPE_POSSIBLE;
	// 						continue;
	// 					}

	// 				}

	// 				$shift_user_class->shift_assign_types = $shift_assign_types;
	// 				$shift_users[$user_request_shift->user_id] = $shift_user_class;
	// 			}

	// 		}

	// 	}

	// 	$this->_view->assign('shift_users', $shift_users);
	// 	$this->_view->assign('location', $location);
	// 	$this->_view->assign('locations', $locations);

	// }

	/**
	 * アサイン確定アクション
	 *
	 */
	public function assignUpdFinishAction() {

		$id = $this->_request->getPost('location_id');

		// 選択した現場情報
		$dao_location = new DaoLocation();
		$location = $dao_location->select_by_key($id);
		if (!$location)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		// 現場のアサイン済みフラグを更新する
		if ($location->is_assigned) {

			// フラグを戻す
			$sets['is_assigned'] = false;
			$wheres['id'] = $id;
			if (false === $dao_location->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			$is_assigned = false;
		
		} else {

			// フラグを立てる
			$sets['is_assigned'] = true;
			$wheres['id'] = $id;
			if (false === $dao_location->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);		

			// アサインした従事者へ通知する
			$dao_location_assign_user = new DaoLocationAssignUser();
			$location_assign_users = $dao_location_assign_user->select_where_with_user(array('ls.location_id' => $id, 'laun.is_notified' => false));
			$notice_users = array();
			foreach ((array)$location_assign_users as $location_assign_user) {

				if (isset($notice_users[$location_assign_user->user_id])) {
					$shift['location_shift_name'] = $location_assign_user->location_shift_name;
					$shift['shift_date_from'] = $location_assign_user->shift_date_from;
					$shift['shift_date_to'] = $location_assign_user->shift_date_to;
					$notice_users[$location_assign_user->user_id]->shifts[] = $shift;
					$ary = array_column($notice_users[$location_assign_user->user_id]->shifts, 'shift_date_from');
					array_multisort($ary, SORT_ASC, $notice_users[$location_assign_user->user_id]->shifts);
					continue;
				}

				$notice_user = new stdClass();
				$notice_user->name = $location_assign_user->name;
				$notice_user->email = $location_assign_user->email;
				$notice_users[$location_assign_user->user_id] = $notice_user;
				$shift['location_shift_name'] = $location_assign_user->location_shift_name;
				$shift['shift_date_from'] = $location_assign_user->shift_date_from;
				$shift['shift_date_to'] = $location_assign_user->shift_date_to;
				$notice_user->shifts[] = $shift;
				// asort($notice_users[$location_assign_user->user_id]->shift_types);

			}
		
			foreach ((array)$notice_users as $notice_user) {

				$mail_params['name'] = $notice_user->name;
				$mail_params['location_date'] = $location->location_date;
				$mail_params['address'] = $location->address;
				$mail_params['shifts'] = $notice_user->shifts;
				$mail_params['detail'] = $location->detail;
				$model_mail = new ModelMail();
				$model_mail->set_from(NOTICE_EMAIL);
				$model_mail->set_replyto(NOTICE_EMAIL);
				$model_mail->set_subject(MAIL_SUBJECT_ASSIGN_NOTICE);
				$model_mail->create_body('assign_notice', $mail_params);
				$model_mail->set_address($notice_user->email);
				$model_mail->send();

			}
		
			unset($model_mail);

			// アサインした従事者の通知フラグを立てる
			$sets = null;
			$wheres = null;
			$sets['is_notified'] = true;
			$wheres['location_id'] = $id;
			$dao_location_assign_user_notified = new DaoLocationAssignUserNotified();
			if (false === $dao_location_assign_user_notified->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);	

			$is_assigned = true;

		}

		$this->_view->assign('id', $id);
		$this->_view->assign('is_assigned', $is_assigned);

	}

	/**
	 * アサインまとめリストアクション
	 *
	 */
	public function assignSummaryListAction() {

		$pg = $this->_request->getQuery('pg') ? $this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($request['pg'] - 1) * self::PAGER_PER_PAGE : 0;

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		// デフォルトは現場日付の降順
		$sorts['location_date'] = 'DESC';
		if ($sort_key && $sort_type) {

			$sorts = null;
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

		$dao_location = new DaoLocation();
		$locations = $dao_location->select_where(
			null,
			$sorts,
			self::PAGER_PER_PAGE,
			$offset
		);
		$location_ids = null;
		foreach ($locations as $location) {
			$location_ids[] = $location->id;
		}

		// $location_ids = array(6,7,8,9);

		// 資格証明書初期値
		$dao_cert = new DaoCert();
		$certs = $dao_cert->select_all();
		$cert_defaults = array();
		foreach ((array)$certs as $cert) {
			$cert_default['id'] = $cert->id;
			$cert_default['name'] = $cert->name;
			$cert_default['amount'] = 0;
			$cert_defaults[$cert->id] = $cert_default;
		}

		// 性別初期値
		$gender_defaults = array();
		foreach (PARAM_CONST_GENDER_TYPES as $gender_type => $gender_type_name) {
			$gender_default['type'] = $gender_type;
			$gender_default['name'] = $gender_type_name;
			$gender_default['amount'] = 0;
			$gender_defaults[$gender_type] = $gender_default;
		}
		
		$model_locations = new ModelLocations($location_ids, $sorts);

		// 現場日付のシフト希望従事者
		$dao_user_request_shift = new DaoUserRequestShift();
		$user_request_shifts = $dao_user_request_shift->select_where_with_user_for_range_check(
			array(
				'urs.shift_date_from' => $model_locations->get_shift_date_to(), // 意図的にfromとtoを逆にする
				'urs.shift_date_to' => $model_locations->get_shift_date_from(), // 意図的にfromとtoを逆にする
			),
		);

		$request_shift_user_ids = array();
		foreach ((array)$user_request_shifts as $user_request_shift) {
			$request_shift_user_ids[] = $user_request_shift->user_id;
		}
		$model_request_shift_users = new ModelUsers($request_shift_user_ids);

		$locations = $model_locations->gets();
		$summaries = array();
		$location_assign_user_ids = array();
		foreach ($locations as $location) {

			$summary_class = new stdClass();
			$summary_class->location_id = $location->id;
			$summary_class->location_name = $location->name;
			$summary_class->location_date = $location->location_date;
			$summary_class->assign_amount = 0;
			$summary_class->certs = $cert_defaults;
			$summary_class->genders = $gender_defaults;
			$summary_class->request_certs = $cert_defaults;
			$summary_class->request_genders = $gender_defaults;
			$summary_class->location_shifts = null;
			$location_shifts = null;
			foreach ((array)$location->location_shifts as $location_shift) {

				$location_shift_class = new stdClass();
				$location_shift_class->id = $location_shift->id;
				$location_shift_class->location_shift_name = $location_shift->name;
				$location_shift_class->shift_date_from = $location_shift->shift_date_from;
				$location_shift_class->shift_date_to = $location_shift->shift_date_to;
				$location_shift_class->certs = $cert_defaults;
				$location_shift_class->genders = $gender_defaults;
				$location_shift_class->request_certs = $cert_defaults;
				$location_shift_class->request_genders = $gender_defaults;
				$location_shift_class->assign_amount = 0;
				$location_shift_class->user_ids = array();
				$location_shift_class->location_assign_users = null;
				$location_assign_users = null;
				foreach ((array)$location_shift->location_assign_users as $location_assign_user) {

					$location_assign_user_class = new stdClass();
					$location_assign_user_class->user_id = $location_assign_user->user_id;
					$location_shift_class->user_ids[] = $location_assign_user->user_id;
					$location_shift_class->assign_amount++;
					$summary_class->assign_amount++;
					
					if (!in_array($location_assign_user->user_id, $location_assign_user_ids))
						$location_assign_user_ids[] = $location_assign_user->user_id;

					$location_assign_users[] = $location_assign_user_class;
				
				}
				$location_shift_class->location_assign_users = $location_assign_users;
				$location_shifts[] = $location_shift_class;
			}
			$summary_class->location_shifts = $location_shifts;

			$summaries[] = $summary_class;
		}

		$model_users = new ModelUsers($location_assign_user_ids);

		foreach ($summaries as $s_key => $summary) {

			foreach ((array)$summary->location_shifts as $ls_key => $location_shifts) {

				if ($location_shifts->location_assign_users) {

					foreach ($location_shifts->location_assign_users as $location_assign_user) {

						$user = $model_users->get($location_assign_user->user_id);
						// 臨時アサインは除外する
						if ($user->is_temporary) {
							$summaries[$s_key]->assign_amount--;
							$summaries[$s_key]->location_shifts[$ls_key]->assign_amount--;
							continue;
						}
						
						// 性別の種別カウント
						$summaries[$s_key]->location_shifts[$ls_key]->genders[$user->gender_type]['amount']++;
						$summaries[$s_key]->genders[$user->gender_type]['amount']++;						

						// 所有資格別の所有者のカウント
						if ($user->user_certs) {

							foreach ($user->user_certs as $user_cert) {

								$summaries[$s_key]->location_shifts[$ls_key]->certs[$user_cert->cert_id]['amount']++;
								$summaries[$s_key]->certs[$user_cert->cert_id]['amount']++;

							}

						}

					}

				}

				// 未アサイン
				if ($user_request_shifts) {

					foreach ($user_request_shifts as $user_request_shift) {

						if (
							strtotime($location_shifts->shift_date_from) < strtotime($user_request_shift->shift_date_from) ||
							strtotime($location_shifts->shift_date_to) > strtotime($user_request_shift->shift_date_to)
						) {
							continue;
						}

						// アサインの済みの配列に存在しない場合は、未アサイン
						if (!in_array($user_request_shift->user_id, $location_shifts->user_ids)) {
						
							$request_shift_user = $model_request_shift_users->get($user_request_shift->user_id);

							// 臨時アサインは除外する
							if ($request_shift_user->is_temporary)
								continue;

							// 性別の種別カウント
							$summaries[$s_key]->location_shifts[$ls_key]->request_genders[$request_shift_user->gender_type]['amount']++;
							$summaries[$s_key]->request_genders[$request_shift_user->gender_type]['amount']++;

							// 所有資格別の所有者のカウント
							if ($request_shift_user->user_certs) {

								foreach ($request_shift_user->user_certs as $request_shift_user_cert) {

									$summaries[$s_key]->location_shifts[$ls_key]->request_certs[$request_shift_user_cert->cert_id]['amount']++;
									$summaries[$s_key]->request_certs[$request_shift_user_cert->cert_id]['amount']++;

								}

							}

						}

					}

				}

			}

		}

		// 全件数
		$total_num = $dao_location->select_count();

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/location/assignSummaryList/');
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		$pager->create();

		$this->_view->assign('summaries', $summaries);
		$this->_view->assign('pager', $pager);

	}

}