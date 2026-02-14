<?php

/**
 * CONTROLLER : Product
 *
 *　@author kanemiya
 */

class ProductController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	private $_login_account;

	const TOP_PRODUCT_LIMIT = 8;

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

		$this->_login_user = $user;	
		$this->_view->assign('login_user', $user);

		
	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {

		// 出品検索
		$model_product_search = new ModelProductSearch();

		// 検索
		$model_product_search->set_sort_type(PARAM_PRODUCT_SEARCH_SORT_TYPE_NEW_ENTRY);
		$model_product_search->set_limit(self::TOP_PRODUCT_LIMIT);
		$model_product_search->search();
		$products = $model_product_search->get();

		// 全件数
		$product_num = $model_product_search->get_num();

		$this->_view->assign('products', $products);


	}

	/**
	 * 出品検索一覧アクション
	 *
	 */
	public function searchAction() {

	}

	/**
	 * 出品詳細アクション
	 *
	 */
	public function detailAction() {

		$id = $this->_request->getQuery('id');

		// 出品IDがなければ、トップへリダイレクト
		if (!$id) {
			header("Location:" . WEB_SRC, true, 301);
			exit();
		}

		// 存在しない出品の場合は、トップへリダイレクト
		$model_product = new ModelProduct($id);
		$product = $model_product->get();
		if (!$product) {
			header("Location:" . WEB_SRC, true, 301);
			exit();
		}

		$this->_view->assign('product', $product);

	}

}