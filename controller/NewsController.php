<?php

/**
 * CONTROLLER : お知らせ
 *
 *　@author kanemiya
 */

class NewsController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const PAGER_PER_PAGE = 5;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		// ログインチェック
		// if (!UtilLogin::is_login())
		// 	parent::setView('login', 'form');

		// サイトタイトル（ヘッダ）
		$this->_view->assign('site_title', 'お知らせ');
		
	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {

		// getの場合
		$request = $this->_request->getQuery();
		$pg = isset($request['pg']) ? $request['pg'] : 1;
		// $offset = (isset($request['pg']) && $request['pg'] > 1) ? ($request['pg'] - 1) * self::PAGER_PER_PAGE : 0;
		$offset = ($pg > 1) ? ($request['pg'] - 1) * self::PAGER_PER_PAGE : 0;

		$now_date = date('Y-m-d');

		$dao_news = new DaoUmNews();
		$newss = $dao_news->select_by_release_date(null, $now_date, array('release_date' => 'DESC'), self::PAGER_PER_PAGE, $offset);

		$news_num = $dao_news->select_count_by_release_date(null, $now_date);

		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($news_num);
		$pager->set_show_nav(5);
		$pager->create();


		$this->_view->assign('newss', $newss);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * インデックスアクション
	 *
	 */
	public function detailAction() {

		// getの場合
		$request = $this->_request->getQuery();
		$id = isset($request['id']) ? $request['id'] : null;

		// お知らせ : ID無し
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, 1);

		$dao_news = new DaoUmNews();
		$news = $dao_news->select_by_key($id);

		// お知らせ : 該当無し
		if (!$news)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, "お知らせ"), 2);

		// お知らせ : 期日未達
		if (date('Y-m-d') < $news->release_date)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, "お知らせ"), 3);

		$this->_view->assign('news', $news);

	}


}