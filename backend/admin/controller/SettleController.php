<?php

/**
 * CONTROLLER : Top
 *
 *　@author kanemiya
 */

class SettleController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const PAGER_PER_PAGE = 10;

	const PAGER_DELTA = 5;

	const NAME_LENGTH = 30;

	const VALUE_LENGTH = 30;

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

		// 現場一覧アクションへ遷移
		$this->changeOtherAction('locationList');

	}

	/**
	 * 現場一覧アクション
	 *
	 */
	public function locationListAction() {

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

		$name = $this->_request->getQuery('name');

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

		// 現場
		$dao_location = new DaoLocation();
		$locations = $dao_location->select_where(
			$wheres,
			$sorts,
			// array('location_date' => 'DESC'),
			self::PAGER_PER_PAGE,
			$offset
		);

		// 全件数
		$total_num = $dao_location->select_count($wheres);

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/location/list/');
		if ($name)
			$pager->set_query('name', $name);
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		$pager->create();

		$this->_view->assign('locations', $locations);
		$this->_view->assign('pager', $pager);
		
	}

	/**
	 * 日当対象一覧アクション
	 *
	 */
	public function wageListAction() {

		$id = $this->_request->getQuery('id');

		$model_location = new ModelLocation($id);
		$location = $model_location->get();
		$user_ids = null;
		foreach ($location->location_shifts as $location_shift) {

			foreach ((array)$location_shift->location_assign_users as $location_assign_user) {
				$user_ids[] = $location_assign_user->user_id;
			}

		}
		$model_users = new ModelUsers($user_ids);

		$location_assign_uses = null;
		foreach ($location->location_shifts as $location_shift) {

			foreach ((array)$location_shift->location_assign_users as $location_assign_user) {

				$location_assign_user->location_shift_name = $location_shift->name;
				$location_assign_user->user = $model_users->get($location_assign_user->user_id);
				$location_assign_users[] = $location_assign_user;

			}

		}
// echo '<pre>';var_dump($model_location->get());exit;
		$this->_view->assign('location', $model_location->get());
		$this->_view->assign('location_assign_users', $location_assign_users);

	}

	/**
	 * 日当入力完了アクション
	 *
	 */
	public function updDailyWageFinishAction() {

		$daily_wage = $this->_request->getPost('daily_wage');
		$id = $this->_request->getPost('id');
		$location_id = $this->_request->getPost('location_id');
		$is_tax = $this->_request->getPost('is_tax');

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_by_key($id);
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者'), __LINE__);

		// 源泉徴収金額を算出
		$withhold_tax = 0;
		if ($is_tax) {
			$model_widthhold_tax = new ModelWithholdTax($daily_wage);
			$withhold_tax = $model_widthhold_tax->get_tax();
		}
		
		// 更新
		$sets['daily_wage'] = $daily_wage;
		$sets['withhold_tax'] = $withhold_tax;
		$sets['total_wage'] = $daily_wage - $withhold_tax;
		$wheres['id'] = $id;
		if (false === $dao_location_assign_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		$this->_view->assign('location_id', $location_id);
	
	}

	/**
	 * 日当確定済みフラグ更新完了アクション
	 *
	 */
	public function updisConfirmedFinishAction() {

		$id = $this->_request->getPost('id');
		$location_id = $this->_request->getPost('location_id');
		$mode = $this->_request->getPost('mode');

		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);

		if (PARAM_CONST_COMMON_MODE_1 != $mode && PARAM_CONST_COMMON_MODE_2 != $mode)
			throw new Exception(ERR_MSG_PARAM, __LINE__);		

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_by_key($id);
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者'), __LINE__);
		
		// モードによりフラグを変更する
		switch ($mode) {

			case PARAM_CONST_COMMON_MODE_1:
				$sets['is_confirmed'] = true;
				break;

			case PARAM_CONST_COMMON_MODE_2:
				$sets['is_confirmed'] = false;
				break;
			
			default:
				throw new Exception(ERR_MSG_PARAM, __LINE__);
				break;
		}

		// 更新
		$wheres['id'] = $id;
		if (false === $dao_location_assign_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		$this->_view->assign('location_id', $location_id);
		$this->_view->assign('mode', $mode);
	
	}

	/**
	 * 精算受付一覧アクション
	 *
	 */
	public function receptListAction() {

		$pg = $this->_request->getQuery('pg') ? (int)$this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($pg - 1) * self::PAGER_PER_PAGE : 0;

		$user_code = $this->_request->getQuery('user_code');
		$user_name = $this->_request->getQuery('user_name');

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		// 従事者情報を検索
		$model_location_assign_user_serach = new ModelLocationAssignUserSearch();

		if ($user_code)
			$model_location_assign_user_serach->set_code($user_code);

		if ($user_name)
			$model_location_assign_user_serach->set_name($user_name);

		if ($sort_key)
			$model_location_assign_user_serach->set_sort_key($sort_key);

		if ($sort_type)
			$model_location_assign_user_serach->set_sort_type($sort_type);


		// $model_location_assign_user_serach->set_sort_type(PARAM_CONST_LOCATION_ASSIGN_USER_SEARCH_SORT_TYPE_NEW_ENTRY);
		$model_location_assign_user_serach->set_limit(self::PAGER_PER_PAGE);
		$model_location_assign_user_serach->set_offset($offset);
		$model_location_assign_user_serach->search();
		$location_assign_users = $model_location_assign_user_serach->get();

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($model_location_assign_user_serach->get_num());
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/settle/receptList/');
		if ($user_code)
			$pager->set_query('user_code', $user_code);
		if ($user_name)
			$pager->set_query('user_name', $user_name);
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);

		$pager->create();

		$this->_view->assign('location_assign_users', $location_assign_users);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 支払いフラグ更新完了アクション
	 *
	 */
	public function updisPaidFinishAction() {

		$id = $this->_request->getPost('id');
		$user_code = $this->_request->getPost('user_code');
		$user_name = $this->_request->getPost('user_name');

		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);			

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_by_key($id);
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者'), __LINE__);

		// 日特確定チェック
		if (!$location_assign_user->is_confirmed)
			throw new Exception(sprintf(ERR_MSG_NOT_CONFIRM_WAGE, 'シフト'), __LINE__);
		
		// 更新
		$sets['is_paid'] = true;
		$sets['paid_date'] = date("Y-m-d H:i:s");
		$wheres['id'] = $id;
		if (false === $dao_location_assign_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		$this->_view->assign('user_code', $user_code);
		$this->_view->assign('user_name', $user_name);

	}

	/**
	 * 日当詳細をPDFで出力する
	 * 
	 */
	public function wageDetailAction() {

		$id = $this->_request->getQuery('id');

		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);	

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_where_with_user(array('lau.id' => $id));
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者'), __LINE__);

		$this->_view->assign('location_assign_user', $location_assign_user[0]);

	}

	/**
	 * 領収書をPDFで出力する
	 * 
	 */
	public function outputReceiptAction() {

		$id = $this->_request->getQuery('id');

		if (!$id)
			throw new Exception(ERR_MSG_PARAM, __LINE__);	

		// 存在チェック
		$dao_location_assign_user = new DaoLocationAssignUser();
		$location_assign_user = $dao_location_assign_user->select_by_key($id);
		if (!$location_assign_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者'), __LINE__);

		$params['receipt_user_name'] = $location_assign_user->receipt_user_name;
		$params['receipt_user_post_code'] = $location_assign_user->receipt_user_post_code;
		$params['receipt_user_address'] = $location_assign_user->receipt_user_address;
		$params['receipt_user_tel'] = $location_assign_user->receipt_user_tel;
		$params['receipt_user_birth_day'] = $location_assign_user->receipt_user_birth_day;
		$params['work_date_from'] = substr($location_assign_user->work_date_from, 0, 10);
		$params['work_date_to'] = substr($location_assign_user->work_date_to, 0, 10);
		$params['withhold_tax'] = $location_assign_user->withhold_tax;
		$params['total_wage'] = $location_assign_user->total_wage;
		UtilPdf::output('I', 'receipt', 'receipt', $params);

	}

}