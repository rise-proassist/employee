<?php

/**
 * CONTROLLER : Report
 *
 *　@author kanemiya
 */

class ReportController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	const PAGER_PER_PAGE = 10;

	const PAGER_DELTA = 5;

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
	 * 個人別支払い額一覧アクション
	 *
	 */
	public function paidUserListAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$code = $this->_request->getQuery('code');
		$name = $this->_request->getQuery('name');
		$name_kana = $this->_request->getQuery('name_kana');

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

		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);

		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);

		$pager->create();

		$this->_view->assign('users', $users);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 個人別支払い額詳細アクション
	 *
	 */
	public function paidUserDetailAction() {

		$user_id = $this->_request->getQuery('id');
		$shift_date_from = $this->_request->getQuery('shift_date_from');
		$shift_date_to = $this->_request->getQuery('shift_date_to');

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

		if (!$user_id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$model_user = new ModelUser($user_id);
		$user = $model_user->get();
		if (!$user)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$wheres['lau.user_id'] = $user_id;

		if ($shift_date_from)
			$wheres['ls.shift_date_from'] = $shift_date_from;

		if ($shift_date_to)
			$wheres['ls.shift_date_to'] = $shift_date_to;

		// 支払済合計
		$paid_total_wage = 0;

		// 未払金額合計
		$unpaid_total_wage = 0;

		// 日当合計
		$daily_wage_total = 0;

		// 源泉徴収金額合計
		$withhold_tax_total = 0;

		// 稼働時間合計
		$work_time_total = 0;

		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_users = $dao_location_assign_user->select_for_search($wheres, $sorts);
		foreach ((array)$location_assign_users as $location_assign_user) {

			if ($location_assign_user->is_paid) {
				$paid_total_wage += $location_assign_user->total_wage;
			} else {
				$unpaid_total_wage += $location_assign_user->total_wage;
			}
			$daily_wage_total += $location_assign_user->daily_wage;
			$withhold_tax_total += $location_assign_user->withhold_tax;
			$work_time_total += UtilCommon::calc_work_time($location_assign_user->work_date_from, $location_assign_user->work_date_to);

		}

		$this->_view->assign('user', $user);
		$this->_view->assign('location_assign_users', $location_assign_users);
		$this->_view->assign('daily_wage_total', $daily_wage_total);
		$this->_view->assign('withhold_tax_total', $withhold_tax_total);
		$this->_view->assign('paid_total_wage', $paid_total_wage);
		$this->_view->assign('unpaid_total_wage', $unpaid_total_wage);
		$this->_view->assign('work_time_total', $work_time_total);

	}

	/**
	 * 年月別支払い金額サマリアクション
	 *
	 */
	public function paidYmSumaryAction() {

		$shift_date_year = $this->_request->getQuery('year');

		// 支払済合計
		$paid_total_wage = 0;

		// 未払金額合計
		$unpaid_total_wage = 0;

		// 日当合計
		$daily_wage_total = 0;

		// 源泉徴収金額合計
		$withhold_tax_total = 0;

		// 稼働時間合計
		$work_time_total = 0;

		$wages = array();

		if ($shift_date_year) {

			$wheres['ls.shift_date_from'] = sprintf("%s-01-01 00:00:00", $shift_date_year);
			$wheres['ls.shift_date_to'] = sprintf("%s-01-01 23:59:59", $shift_date_year+1);

			// 年月初期値
			$default_wage = array(
				'daily_wage_total' => 0,
				'withhold_tax_total' => 0,
				'total_wage_total' => 0,
				'unpaid_wage_total' => 0,
				'work_time_total' => 0,
			);
			for ($month = 1; $month <= 12; $month++) {
				$wages[sprintf("%02d", $month)] = $default_wage;
			} 

			$dao_location_assign_user = new DaoLocationAssignUser();
			$location_assign_users = $dao_location_assign_user->select_for_search($wheres);
			foreach ((array)$location_assign_users as $location_assign_user) {

				foreach ($wages as $month => $wage) {

					if (substr($location_assign_user->shift_date_from, 5, 2) == $month) {

						$wages[$month]['work_time_total'] += UtilCommon::calc_work_time($location_assign_user->work_date_from, $location_assign_user->work_date_to);
						$wages[$month]['daily_wage_total'] +=  $location_assign_user->daily_wage;
						$wages[$month]['withhold_tax_total'] +=  $location_assign_user->withhold_tax;
						$wages[$month]['total_wage_total'] +=  $location_assign_user->total_wage;
						if (!$location_assign_user->is_paid)
							$wages[$month]['unpaid_wage_total'] +=  $location_assign_user->total_wage;

					}

				}

				if ($location_assign_user->is_paid) {
					$paid_total_wage += $location_assign_user->total_wage;
				} else {
					$unpaid_total_wage += $location_assign_user->total_wage;
				}
				$daily_wage_total += $location_assign_user->daily_wage;
				$withhold_tax_total += $location_assign_user->withhold_tax;
				$work_time_total += UtilCommon::calc_work_time($location_assign_user->work_date_from, $location_assign_user->work_date_to);

			}

		}
// echo '<pre>';var_dump($wages);exit;
		// 年度セレクタ
		$select_years = array();
		for ($y = 2025; $y <= date("Y")+1; $y++) {
			$select_years[] = $y;
		}

		$this->_view->assign('select_years', $select_years);
		$this->_view->assign('wages', $wages);
		$this->_view->assign('daily_wage_total', $daily_wage_total);
		$this->_view->assign('withhold_tax_total', $withhold_tax_total);
		$this->_view->assign('paid_total_wage', $paid_total_wage);
		$this->_view->assign('unpaid_total_wage', $unpaid_total_wage);
		$this->_view->assign('work_time_total', $work_time_total);

	}

	/**
	 * 年月別支払い金額サマリCSVエクスポートアクション
	 *
	 */
	public function paidYmSumaryCsvExportAction() {

		$shift_date_year = $this->_request->getPost('year');

		// 支払済合計
		$paid_total_wage = 0;

		// 未払金額合計
		$unpaid_total_wage = 0;

		// 日当合計
		$daily_wage_total = 0;

		// 源泉徴収金額合計
		$withhold_tax_total = 0;

		// 稼働時間合計
		$work_time_total = 0;

		$wages = array();

		if ($shift_date_year) {

			$wheres['ls.shift_date_from'] = sprintf("%s-01-01 00:00:00", $shift_date_year);
			$wheres['ls.shift_date_to'] = sprintf("%s-01-01 23:59:59", $shift_date_year+1);

			// 年月初期値
			$default_wage = array(
				'daily_wage_total' => 0,
				'withhold_tax_total' => 0,
				'total_wage_total' => 0,
				'unpaid_wage_total' => 0,
				'work_time_total' => 0,
			);
			for ($month = 1; $month <= 12; $month++) {
				$wages[sprintf("%02d", $month)] = $default_wage;
			} 

			$dao_location_assign_user = new DaoLocationAssignUser();
			$location_assign_users = $dao_location_assign_user->select_for_search($wheres);
			foreach ((array)$location_assign_users as $location_assign_user) {

				foreach ($wages as $month => $wage) {

					if (substr($location_assign_user->shift_date_from, 5, 2) == $month) {

						$wages[$month]['work_time_total'] += UtilCommon::calc_work_time($location_assign_user->work_date_from, $location_assign_user->work_date_to);
						$wages[$month]['daily_wage_total'] +=  $location_assign_user->daily_wage;
						$wages[$month]['withhold_tax_total'] +=  $location_assign_user->withhold_tax;
						$wages[$month]['total_wage_total'] +=  $location_assign_user->total_wage;
						if (!$location_assign_user->is_paid)
							$wages[$month]['unpaid_wage_total'] +=  $location_assign_user->total_wage;

					}

				}

				if ($location_assign_user->is_paid) {
					$paid_total_wage += $location_assign_user->total_wage;
				} else {
					$unpaid_total_wage += $location_assign_user->total_wage;
				}
				$daily_wage_total += $location_assign_user->daily_wage;
				$withhold_tax_total += $location_assign_user->withhold_tax;
				$work_time_total += UtilCommon::calc_work_time($location_assign_user->work_date_from, $location_assign_user->work_date_to);

			}

		}

		$headers = array(
			'年月',
			'稼働時間',
			'日当金額',
			'源泉徴収金額',
			'支払金額',
			'未払金額',
		);

		$datas = array();
		foreach ($wages as $month => $wage) {
			$data['ym'] = sprintf("%d年%d月", $shift_date_year, $month);
			$data['work_time_total'] = $wage['work_time_total'];
			$data['daily_wage_total'] = $wage['daily_wage_total'];
			$data['withhold_tax_total'] = $wage['withhold_tax_total'];
			$data['total_wage_total'] = $wage['total_wage_total'];
			$data['unpaid_wage_total'] = $wage['unpaid_wage_total'];
			$datas[] = $data;
		}

		UtilFile::get_csv(date("YmdHis") . '_paid_ym_sumary', $datas, $headers);
		exit;

	}

	/**
	 * 請求書をPDFで出力する
	 * 
	 */
	public function outputUserInvoiceExportAction() {

		$user_id = $this->_request->getPost('id');
		$shift_date_from = $this->_request->getPost('shift_date_from');
		$shift_date_to = $this->_request->getPost('shift_date_to');

		if (!$user_id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$model_user = new ModelUser($user_id);
		$user = $model_user->get();
		if (!$user)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$wheres['lau.user_id'] = $user_id;

		if ($shift_date_from)
			$wheres['ls.shift_date_from'] = $shift_date_from;

		if ($shift_date_to)
			$wheres['ls.shift_date_to'] = $shift_date_to;

		// 支払済合計
		$paid_total_wage = 0;

		// 未払金額合計
		$unpaid_total_wage = 0;

		// 日当合計
		$daily_wage_total = 0;

		// 源泉徴収金額合計
		$withhold_tax_total = 0;

		// 稼働時間合計
		$work_time_total = 0;

		// 支給額合計
		$total_wage_total = 0;

		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_users = $dao_location_assign_user->select_for_search($wheres);
		foreach ((array)$location_assign_users as $location_assign_user) {

			if ($location_assign_user->is_paid) {
				$paid_total_wage += $location_assign_user->total_wage;
			} else {
				$unpaid_total_wage += $location_assign_user->total_wage;
			}
			$daily_wage_total += $location_assign_user->daily_wage;
			$total_wage_total += $location_assign_user->total_wage;
			$withhold_tax_total += $location_assign_user->withhold_tax;
			$work_time_total += UtilCommon::calc_work_time($location_assign_user->work_date_from, $location_assign_user->work_date_to);

		}	

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_where(array('user_id' => $user_id), array('id' => 'DESC'));
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者'), __LINE__);

		$params['receipt_user_name'] = $location_assign_user[0]->receipt_user_name;
		$params['receipt_user_post_code'] = $location_assign_user[0]->receipt_user_post_code;
		$params['receipt_user_address'] = $location_assign_user[0]->receipt_user_address;
		$params['receipt_user_tel'] = $location_assign_user[0]->receipt_user_tel;
		$params['receipt_user_birth_day'] = $location_assign_user[0]->receipt_user_birth_day;
		$params['location_assign_users'] = $location_assign_users;
		$params['daily_wage_total'] = $daily_wage_total;
		$params['withhold_tax_total'] = $withhold_tax_total;
		$params['total_wage_total'] = $total_wage_total;
		
		UtilPdf::output('I', 'user_invoice', 'user_invoice', $params);

	}


	/**
	 * 領収書をPDFで出力する
	 * 
	 */
	public function outputUserReceiptExportAction() {

		$user_id = $this->_request->getPost('id');
		$shift_date_from = $this->_request->getPost('shift_date_from');
		$shift_date_to = $this->_request->getPost('shift_date_to');

		if (!$user_id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$model_user = new ModelUser($user_id);
		$user = $model_user->get();
		if (!$user)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$wheres['lau.user_id'] = $user_id;

		if ($shift_date_from)
			$wheres['ls.shift_date_from'] = $shift_date_from;

		if ($shift_date_to)
			$wheres['ls.shift_date_to'] = $shift_date_to;

		// 支払済合計
		$paid_total_wage = 0;

		// 未払金額合計
		$unpaid_total_wage = 0;

		// 日当合計
		$daily_wage_total = 0;

		// 源泉徴収金額合計
		$withhold_tax_total = 0;

		// 稼働時間合計
		$work_time_total = 0;

		// 支給額合計
		$total_wage_total = 0;

		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_users = $dao_location_assign_user->select_for_search($wheres);
		foreach ((array)$location_assign_users as $location_assign_user) {

			if ($location_assign_user->is_paid) {
				$paid_total_wage += $location_assign_user->total_wage;
			} else {
				$unpaid_total_wage += $location_assign_user->total_wage;
			}
			$daily_wage_total += $location_assign_user->daily_wage;
			$total_wage_total += $location_assign_user->total_wage;
			$withhold_tax_total += $location_assign_user->withhold_tax;
			$work_time_total += UtilCommon::calc_work_time($location_assign_user->work_date_from, $location_assign_user->work_date_to);

		}	

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_where(array('user_id' => $user_id), array('id' => 'DESC'));
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者'), __LINE__);

		$params['receipt_user_name'] = $location_assign_user[0]->receipt_user_name;
		$params['receipt_user_post_code'] = $location_assign_user[0]->receipt_user_post_code;
		$params['receipt_user_address'] = $location_assign_user[0]->receipt_user_address;
		$params['receipt_user_tel'] = $location_assign_user[0]->receipt_user_tel;
		$params['receipt_user_birth_day'] = $location_assign_user[0]->receipt_user_birth_day;
		$params['location_assign_users'] = $location_assign_users;
		$params['daily_wage_total'] = $daily_wage_total;
		$params['withhold_tax_total'] = $withhold_tax_total;
		$params['total_wage_total'] = $total_wage_total;
		$params['shift_date_from'] = $shift_date_from;
		$params['shift_date_to'] = $shift_date_to;
		
		UtilPdf::output('I', 'user_receipt', 'user_receipt', $params);
	}

}