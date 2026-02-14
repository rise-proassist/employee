<?php

/**
 * CONTROLLER : Purchase
 *
 *　@author kanemiya
 */

class PurchaseController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	private $_login_account;

	const NAME_LENGTH = 16;
	const ADDRESS_LENGTH = 200;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$user = $model_session->get('user');
		$is_login = $model_session->is_login();

		// ログイン種別チェック
		if (!$is_login) {
			header('Location: ' . UtilCommon::get_base_url('login'));
			return;
		}

		$model_session->close();

		$this->_login_user = $user;	
		$this->_view->assign('login_user', $user);

		
	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {

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

		// 購入できない出品は、エラー

		// 自身の出品の場合は、エラー

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$model_session->set('order_product_id', $id);
		
		$this->_view->assign('product', $product);
		$this->_view->assign('user', $user);	

	}

	/**
	 * 決済実行アクション
	 *
	 */
	public function execAction() {

		$token = $this->_request->getPost('payjp-token');		

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$product_id = $model_session->get('order_product_id');

		if (!$product_id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 商品情報（ロック）
		$dao_product = new DaoProduct();
		$dao_product->begin();
		$product = $dao_product->select_by_key_for_update($product_id);
		if (!$product)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// ステータスチェック
		if (PARAM_CONST_PRODUCT_STATUS_TYPE_ON_SALE != $product->status_type)
			throw new Exception(ERR_MSG_PRODUCT_NOT_FOR_SALE, self::ERROR_CODE_BASE_DB_ERROR);

		// 購入者の配送先を取得
		$dao_user_address = new DaoUserAddress();
		$user_address = $dao_user_address->select_where_one(array('user_id' => $this->_login_user->id, 'is_main' => 1));
		if (!$user_address)
			throw new Exception(ERR_MSG_SHIPPING_NOT_FOUND, __LINE__);

		// 購入（支払いは未確定）
		$result = UtilPayJp::charge_create($token, (int)$product->price, "品番 : " . $product->code, false);
		if ($result['status'])
			throw new Exception(sprintf(ERR_MSG_FAILED, "購入手続き"), __LINE__);

		// 購入データ登録
		$entity_user_order = new EntityUserOrder();
		$entity_user_order->id = null;
		$entity_user_order->user_id = $this->_login_user->id;
		$entity_user_order->product_id = $product_id;
		$entity_user_order->price = $product->price;
		$entity_user_order->shipping_user_name = $user_address->name;
		$entity_user_order->shipping_post_code = $user_address->post_code;
		$entity_user_order->shipping_prefecture_id = $user_address->prefecture_id;
		$entity_user_order->shipping_address_1 = $user_address->address_1;
		$entity_user_order->shipping_address_2 = $user_address->address_2;
		$entity_user_order->shipping_address_3 = $user_address->address_3;
		$entity_user_order->status_type = PARAM_CONST_USER_ORDER_STATUS_TYPE_NOT_SHIPPED;
		$entity_user_order->api_create_response = serialize($result['detail']);
		$dao_user_order = new DaoUserOrder();
		if (!$last_user_order_id = $dao_user_order->insert($entity_user_order))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// 商品ステータスを「売り切れ」に更新する
		$sets['status_type'] = PARAM_CONST_PRODUCT_STATUS_TYPE_SOLD_OUT;
		$wheres['id'] = $product_id;
		if (false === $dao_product->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		$dao_product->commit();

		$model_session->unset('order_product_id');
		$model_session->close();

		// メールを配信する

		// 購入後、購入手続きへリダイレクトする
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Location: " . WEB_SRC . '/product/detail/?id=' . $product_id ); 
		exit;

	}

	/**
	 * 配送先一覧アクション
	 *
	 */
	public function addressListAction() {

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$order_product_id = $model_session->get('order_product_id');

		$this->_view->assign('user_addresses', $user->user_addresses);
		$this->_view->assign('order_product_id', $order_product_id);	
	
	}

	/**
	 * 配送先登録フォームアクション
	 *
	 */
	public function addressAddFormAction() {

		$this->_view->assign('prefectures', PARAM_CONST_PREFECTURES);
	
	}

	/**
	 * 配送先登録完了アクション
	 *
	 */
	public function addressAddFinishAction() {

		$name = $this->_request->getPost('name');
		$post_code = $this->_request->getPost('post_code');
		$prefecture_id = $this->_request->getPost('prefecture_id');
		$address_1 = $this->_request->getPost('address_1');
		$address_2 = $this->_request->getPost('address_2');
		$address_3 = $this->_request->getPost('address_3');

		$error_messages = array();

		try {

			// 宛名 : 未入力チェック
			if (UtilCommon::is_empty($name))
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '宛名');

			// 宛名 : 文字数チェック
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '宛名', self::NAME_LENGTH);

			// 郵便番号 : 未入力チェック
			if (UtilCommon::is_empty($post_code))
				$error_messages['post_code'] = sprintf(ERR_MSG_EMPTY, '郵便番号');	

			// 都道府県ID : 未入力チェック
			if (UtilCommon::is_empty($prefecture_id))
				$error_messages['prefecture_id'] = sprintf(ERR_MSG_EMPTY, '都道府県');	

			// 都道府県ID : 範囲外入力チェック
			if (!array_key_exists($prefecture_id, PARAM_CONST_PREFECTURES))
				$error_messages['prefecture_id'] = sprintf(ERR_MSG_INPUT, '都道府県');

			// お住まい（市区町村） : 未入力チェック
			if (UtilCommon::is_empty($address_1))
				$error_messages['address_1'] = sprintf(ERR_MSG_EMPTY, 'お住まい（市区町村）');	

			// お住まい（市区町村） : 文字数チェック
			if (!UtilCommon::is_length($address_1, self::ADDRESS_LENGTH))
				$error_messages['address_1'] = sprintf(ERR_MSG_LENTGTH, 'お住まい（市区町村）', self::ADDRESS_LENGTH);

			// お住まい（番地） : 未入力チェック
			if (UtilCommon::is_empty($address_2))
				$error_messages['address_2'] = sprintf(ERR_MSG_EMPTY, 'お住まい（番地）');

			// お住まい（番地） : 文字数チェック
			if (!UtilCommon::is_length($address_2, self::ADDRESS_LENGTH))
				$error_messages['address_2'] = sprintf(ERR_MSG_LENTGTH, 'お住まい（番地）', self::ADDRESS_LENGTH);

			// お住まい（マンション名・部屋番号等） : 文字数チェック
			if (!UtilCommon::is_length($address_3, self::ADDRESS_LENGTH))
				$error_messages['address_3'] = sprintf(ERR_MSG_LENTGTH, 'お住まい（マンション名・部屋番号等）', self::ADDRESS_LENGTH);

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception(ERR_MSG_RETRY, __LINE__);

			// 登録済みチェック
			$is_main = true;
			$dao_user_address = new DaoUserAddress();
			$user_addresses = $dao_user_address->select_where(array('user_id' => $this->_login_user->id));
			if ($user_addresses && count($user_addresses))
				$is_main = false;

			// 配送先を登録
			$entity_user_address = new EntityUserAddress();
			$entity_user_address->id = null;
			$entity_user_address->user_id = $this->_login_user->id;
			$entity_user_address->name = $name;
			$entity_user_address->post_code = $post_code;
			$entity_user_address->prefecture_id = $prefecture_id;
			$entity_user_address->address_1 = $address_1;
			$entity_user_address->address_2 = $address_2;
			$entity_user_address->address_3 = $address_3;
			$entity_user_address->is_main = $is_main;
			if(!$user_address_id = $dao_user_address->insert($entity_user_address))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 登録完了後、配送先一覧へリダイレクトする
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header( "Location: " . WEB_SRC . '/purchase/addressList/' ); 
			exit;

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('prefectures', PARAM_CONST_PREFECTURES);

			// フォームへ遷移
			$this->setAction('addressAddForm');

		}
	
	}

	/**
	 * 配送先変更アクション
	 *
	 */
	public function addressMainUpdFinishAction() {

		$user_address_id = $this->_request->getPost('user_address_id');

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();
		if (
			$user_address_id && 
			$user->user_addresses && 
			count($user->user_addresses)
		){
			// 配送先 : 存在チェック
			$dao_user_address = new DaoUserAddress();
			$user_address = $dao_user_address->select_by_key($user_address_id);
			if (!$user_address)
				throw new Exception(ERR_MSG_RETRY, __LINE__);

			// 配送先 : 自身の配送先チェック
			if ($user_address->user_id != $this->_login_user->id)
				throw new Exception(ERR_MSG_RETRY, __LINE__);

			// 配送先選択をすべて解除
			$sets['is_main'] = 0;
			$wheres['user_id'] = $this->_login_user->id;
			if (false === $dao_user_address->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 該当の配送先を選択する
			$sets['is_main'] = 1;
			$wheres['id'] = $user_address_id;
			if (false === $dao_user_address->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		}

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$order_product_id = $model_session->get('order_product_id');

		// 更新完了後、購入手続きへリダイレクトする
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Location: " . WEB_SRC . '/purchase/?id=' . $order_product_id ); 
		exit;	
	
	}

	/**
	 * 配送先更新フォームアクション
	 *
	 */
	public function addressUpdFormAction() {

		$id = $this->_request->getQuery('id');

		// 配送先 : 存在チェック
		$dao_user_address = new DaoUserAddress();
		$user_address = $dao_user_address->select_by_key($id);
		if (!$user_address)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先 : 自身の配送先チェック
		if ($user_address->user_id != $this->_login_user->id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$this->_view->assign('user_address', $user_address);
		$this->_view->assign('prefectures', PARAM_CONST_PREFECTURES);
	
	}

	/**
	 * 配送先更新完了アクション
	 *
	 */
	public function addressUpdFinishAction() {

		$name = $this->_request->getPost('name');
		$post_code = $this->_request->getPost('post_code');
		$prefecture_id = $this->_request->getPost('prefecture_id');
		$address_1 = $this->_request->getPost('address_1');
		$address_2 = $this->_request->getPost('address_2');
		$address_3 = $this->_request->getPost('address_3');

		$id = $this->_request->getPost('id');
		if (!$id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先 : 存在チェック
		$dao_user_address = new DaoUserAddress();
		$user_address = $dao_user_address->select_by_key($id);
		if (!$user_address)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先 : 自身の配送先チェック
		if ($user_address->user_id != $this->_login_user->id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$error_messages = array();

		try {

			// 宛名 : 未入力チェック
			if (UtilCommon::is_empty($name))
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '宛名');

			// 宛名 : 文字数チェック
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '宛名', self::NAME_LENGTH);

			// 郵便番号 : 未入力チェック
			if (UtilCommon::is_empty($post_code))
				$error_messages['post_code'] = sprintf(ERR_MSG_EMPTY, '郵便番号');	

			// 都道府県ID : 未入力チェック
			if (UtilCommon::is_empty($prefecture_id))
				$error_messages['prefecture_id'] = sprintf(ERR_MSG_EMPTY, '都道府県');	

			// 都道府県ID : 範囲外入力チェック
			if (!array_key_exists($prefecture_id, PARAM_CONST_PREFECTURES))
				$error_messages['prefecture_id'] = sprintf(ERR_MSG_INPUT, '都道府県');

			// お住まい（市区町村） : 未入力チェック
			if (UtilCommon::is_empty($address_1))
				$error_messages['address_1'] = sprintf(ERR_MSG_EMPTY, 'お住まい（市区町村）');	

			// お住まい（市区町村） : 文字数チェック
			if (!UtilCommon::is_length($address_1, self::ADDRESS_LENGTH))
				$error_messages['address_1'] = sprintf(ERR_MSG_LENTGTH, 'お住まい（市区町村）', self::ADDRESS_LENGTH);

			// お住まい（番地） : 未入力チェック
			if (UtilCommon::is_empty($address_2))
				$error_messages['address_2'] = sprintf(ERR_MSG_EMPTY, 'お住まい（番地）');

			// お住まい（番地） : 文字数チェック
			if (!UtilCommon::is_length($address_2, self::ADDRESS_LENGTH))
				$error_messages['address_2'] = sprintf(ERR_MSG_LENTGTH, 'お住まい（番地）', self::ADDRESS_LENGTH);

			// お住まい（マンション名・部屋番号等） : 文字数チェック
			if (!UtilCommon::is_length($address_3, self::ADDRESS_LENGTH))
				$error_messages['address_3'] = sprintf(ERR_MSG_LENTGTH, 'お住まい（マンション名・部屋番号等）', self::ADDRESS_LENGTH);

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception(ERR_MSG_RETRY, __LINE__);

			// 配送先を更新
			$sets['name'] = $name;
			$sets['post_code'] = $post_code;
			$sets['prefecture_id'] = $prefecture_id;
			$sets['address_1'] = $address_1;
			$sets['address_2'] = $address_2;
			$sets['address_3'] = $address_3;
			$wheres['id'] = $id;
			if(false === $dao_user_address->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 更新完了後、配送先一覧へリダイレクトする
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header( "Location: " . WEB_SRC . '/purchase/addressList/' ); 
			exit;

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('prefectures', PARAM_CONST_PREFECTURES);

			// フォームへ遷移
			$this->setAction('addressAddForm');

		}
	
	}

	/**
	 * 配送先削除確認アクション
	 *
	 */
	public function addressDelConfirmAction() {

		$id = $this->_request->getQuery('id');
		if (!$id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先 : 存在チェック
		$dao_user_address = new DaoUserAddress();
		$user_address = $dao_user_address->select_by_key($id);
		if (!$user_address)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先 : 自身の配送先チェック
		if ($user_address->user_id != $this->_login_user->id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		$this->_view->assign('user_address', $user_address);
		$this->_view->assign('prefecture_name', PARAM_CONST_PREFECTURES[$user_address->prefecture_id]);
	}

	/**
	 * 配送先削除完了アクション
	 *
	 */
	public function addressDelFinishAction() {
	
		$id = $this->_request->getPost('id');
		if (!$id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先 : 存在チェック
		$dao_user_address = new DaoUserAddress();
		$dao_user_address->begin();

		$del_user_address = $dao_user_address->select_by_key($id);
		if (!$del_user_address)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先 : 自身の配送先チェック
		if ($del_user_address->user_id != $this->_login_user->id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 配送先を削除
		$wheres['id'] = $id;
		if (!$dao_user_address->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		// 削除した配送先が選択されていた場合は、別の配送機を選択させる
		if ($del_user_address->is_main) {

			$user_addresses = $dao_user_address->select_where(array('user_id' => $this->_login_user->id));
			if ($user_addresses && is_array($user_addresses)) {

				$sets['is_main'] = 1;
				$wheres['id'] = $user_addresses[0]->id;
				if (false === $dao_user_address->update($sets, $wheres))
					throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			}

		}

		$dao_user_address->commit();

		// 削除完了後、配送先一覧へリダイレクトする
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header( "Location: " . WEB_SRC . '/purchase/addressList/' ); 
		exit;

	}

}