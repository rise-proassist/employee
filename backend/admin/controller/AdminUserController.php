<?php

/**
 * CONTROLLER : 管理者ユーザ
 *
 *　@author kanemiya
 */

class AdminUserController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	const PAGER_PER_PAGE = 10;

	const PAGER_DELTA = 5;

	const NAME_LENGTH = 32;

	const EMAIL_LENGTH = 64;

	const PASSWORD_LENGTH = 16;

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
		$this->changeOtherAction('list');

	}

	/**
	 * 一覧アクション
	 *
	 */
	public function listAction() {

		$pg = $this->_request->getQuery('pg') ? $this->_request->getQuery('pg') : 1;
		$offset = ($pg > 1) ? ($request['pg'] - 1) * self::PAGER_PER_PAGE : 0;

		$name = $this->_request->getPost('name');
		$email = $this->_request->getPost('email');
		$auth_level = $this->_request->getPost('auth_level');

		$sort_key = $this->_request->getQuery('sort_key');
		$sort_type = $this->_request->getQuery('sort_type');

		// $request = $this->_request->getQuery();
		// $pg = isset($request['pg']) ? $request['pg'] : 1;
		// $offset = ($pg > 1) ? ($request['pg'] - 1) * self::PAGER_PER_PAGE : 0;

		$wheres = null;
		if ($name)
			$wheres['name'] = $name;

		if ($email)
			$wheres['email'] = $email;

		if ($auth_level)
			$wheres['auth_level'] = $auth_level;

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

		$dao_admin_user = new DaoAdminUser();
		$admin_users = $dao_admin_user->select_where(
			$wheres,
			$sorts,
			self::PAGER_PER_PAGE,
			$offset
		);

		// 権限レベル名
		if ($admin_users) {

			foreach ($admin_users as $key => $admin_user) {

				if (!isset(PARAM_CONST_AUTH_LEVELS[$admin_user->auth_level]))
					$admin_user->auth_level_name = null;

				$admin_user->auth_level_name = PARAM_CONST_AUTH_LEVELS[$admin_user->auth_level];

			}
		
		}

		// 全件数
		$admin_user_total_num = $dao_admin_user->select_count();

		// ページャ
		$pager = new ModelPager();
		$pager->set_current_page($pg);
		$pager->set_page_rec(self::PAGER_PER_PAGE);
		$pager->set_total_rec($admin_user_total_num);
		$pager->set_show_nav(self::PAGER_DELTA);
		$pager->set_path('/adminUser/list/');
		if ($sort_key)
			$pager->set_query('sort_key', $sort_key);
		if ($sort_type)
			$pager->set_query('sort_type', $sort_type);
		$pager->create();

		$this->_view->assign('auth_levels', PARAM_CONST_AUTH_LEVELS);
		$this->_view->assign('admin_users', $admin_users);
		$this->_view->assign('pager', $pager);

	}

	/**
	 * 詳細アクション
	 *
	 */
	public function detailAction() {

		$request = $this->_request->getQuery();
		$id = (isset($request['id'])) ? $request['id'] : null;

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);				

		$dao_admin_user = new DaoAdminUser();
		$admin_user = $dao_admin_user->select_by_key($id);

		if (!$admin_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '管理者ユーザ情報'), self::ERROR_CODE_NOT_FOUND);				

		// 権限名
		$admin_user->auth_level_name = PARAM_CONST_AUTH_LEVELS[$admin_user->auth_level];

		$this->_view->assign('admin_user', $admin_user);

	}

	/**
	 * 登録フォームアクション
	 *
	 */
	public function addFormAction() {

		$this->_view->assign('auth_levels', PARAM_CONST_AUTH_LEVELS);

	}

	/**
	 * 登録完了アクション
	 *
	 */
	public function addFinishAction() {

		$request = $this->_request->getPost();
		$name = $this->_request->getPost('name');
		$email = $this->_request->getPost('email');
		$password = $this->_request->getPost('password');
		$auth_level = $this->_request->getPost('auth_level');

		try {

			$error_messages = array();

			// 未入力チェック : ユーザ名
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, 'ユーザ名');

			// 文字数チェック : ユーザ名
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, 'ユーザ名', self::NAME_LENGTH);

			// 未入力チェック : メールアドレス
			if (!$email)
				$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');

			// 文字数チェック : メールアドレス
			if (!UtilCommon::is_length($email, self::EMAIL_LENGTH))
				$error_messages['email'] = sprintf(ERR_MSG_LENTGTH, 'メールアドレス', self::EMAIL_LENGTH);

			// 登録済みチェック : メールアドレス
			$dao_admin_user = new DaoAdminUser();
			$admin_user = $dao_admin_user->select_where(array('email' => $email));
			if ($admin_user)
				$error_messages['email'] = sprintf(ERR_MSG_USED_YET, 'メールアドレス');

			// 未入力チェック : パスワード
			if (!$password)
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');

			// 文字数チェック : パスワード
			if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード', self::PASSWORD_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 登録
			$entity_admin_user = new EntityAdminUser();
			$entity_admin_user->id = null;
			$entity_admin_user->name = $name;
			$entity_admin_user->email = $email;
			$entity_admin_user->password = UtilCommon::to_hash_password($password, PASSWORD_DEFAULT);
			$entity_admin_user->auth_level = $auth_level;
			if (!$dao_admin_user->insert($entity_admin_user))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('auth_levels', PARAM_CONST_AUTH_LEVELS);
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('addForm');
			return;

		}

	}

	/**
	 * 更新フォームアクション
	 *
	 */
	public function updFormAction() {

		$request = $this->_request->getQuery();
		$id = (isset($request['id'])) ? $request['id'] : null;

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_admin_user = new DaoAdminUser();
		$admin_user = $dao_admin_user->select_by_key($id);

		if (!$admin_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '管理者ユーザ情報'), self::ERROR_CODE_NOT_FOUND);	

		$this->_view->assign('admin_user', $admin_user);
		$this->_view->assign('auth_levels', PARAM_CONST_AUTH_LEVELS);

	}

	/**
	 * 更新完了アクション
	 *
	 */
	public function updFinishAction() {

		$request = $this->_request->getPost();
		$name = $this->_request->getPost('name');
		$email = $this->_request->getPost('email');
		$password = $this->_request->getPost('password');
		$auth_level = $this->_request->getPost('auth_level');
		$id = $this->_request->getPost('id');

		try {

			$error_messages = array();

			// 未入力チェック : 通番
			if (!$id)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 存在チェック
			$dao_admin_user = new DaoAdminUser();
			$admin_user = $dao_admin_user->select_by_key($id);
			if (!$admin_user)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 未入力チェック : ユーザ名
			if (!$name)
				$error_messages['name'] = sprintf(ERR_MSG_EMPTY, 'ユーザ名');

			// 文字数チェック : ユーザ名
			if (!UtilCommon::is_length($name, self::NAME_LENGTH))
				$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, 'ユーザ名', self::NAME_LENGTH);

			// 未入力チェック : メールアドレス
			if (!$email)
				$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');

			// 文字数チェック : メールアドレス
			if (!UtilCommon::is_length($email, self::EMAIL_LENGTH))
				$error_messages['email'] = sprintf(ERR_MSG_LENTGTH, 'メールアドレス', self::EMAIL_LENGTH);

			// 登録済みチェック : メールアドレス
			$chk_admin_user = $dao_admin_user->select_where_one(array('email' => $email));
			if ($chk_admin_user && $chk_admin_user->id != $id)
				$error_messages['email'] = sprintf(ERR_MSG_USED_YET, 'メールアドレス');

			// 文字数チェック : パスワード
			if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード', self::PASSWORD_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 更新
			$sets['name'] = $name;
			$sets['email'] = $email;
			$sets['auth_level'] = $auth_level;
			$wheres['id'] = $id;
			if (!$dao_admin_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 更新情報がログイン中のユーザの場合は、セッションを書き換える
			if (UtilLogin::get_id() == $id) {

				UtilLogin::set_name($name);
				UtilLogin::set_auth_level($auth_level);

			}

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('auth_levels', PARAM_CONST_AUTH_LEVELS);
			$this->_view->assign('error_messages', $error_messages);
			$this->_view->assign('admin_user', $admin_user);
			parent::setAction('updForm');
			return;

		}

	}

	/**
	 * パスワード更新フォームアクション
	 *
	 */
	public function updPasswordFormAction() {

		$id = UtilLogin::get_id();

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);			

		$dao_admin_user = new DaoAdminUser();
		$admin_user = $dao_admin_user->select_by_key($id);

		if (!$admin_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, 'システムユーザ情報'), self::ERROR_CODE_NOT_FOUND);	

		$this->_view->assign('admin_user', $admin_user);

	}

	/**
	 * パスワード更新完了アクション
	 *
	 */
	public function updPasswordFinishAction() {

		$id = UtilLogin::get_id();
		$password = $this->_request->getPost('password');

		try {

			$error_messages = array();

			// 未入力チェック : 通番
			if (!$id)
				throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);

			// 未入力チェック : パスワード
			if (!$password)
				$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');

			// 文字数チェック : パスワード
			if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH))
				$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード', self::PASSWORD_LENGTH);

			// エラーが一つでもあれば、エラー表示
			if (count($error_messages))
				throw new ValidatiteException("Error Processing Request", 1);

			// 更新
			$sets['password'] = UtilCommon::to_hash_password($password);
			$wheres['id'] = $id;
			$dao_admin_user = new DaoAdminUser();
			if (!$dao_admin_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

		} catch (ValidatiteException $ve) {

			$this->_logger->fatal($ve->getMessage());
			$this->_view->assign('error_messages', $error_messages);
			parent::setAction('updPasswordForm');
			return;

		}

	}

	/**
	 * 削除確認アクション
	 *
	 */
	public function delConfirmAction() {

		$request = $this->_request->getQuery();
		$id = (isset($request['id'])) ? $request['id'] : null;

		if (is_null($id))
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);					

		$dao_admin_user = new DaoAdminUser();
		$admin_user = $dao_admin_user->select_by_key($id);
			
		if (!$admin_user)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '管理者ユーザ情報'), self::ERROR_CODE_NOT_FOUND);				

		// 権限名
		$admin_user->auth_level_name = PARAM_CONST_AUTH_LEVELS[$admin_user->auth_level];

		$this->_view->assign('admin_user', $admin_user);

	}

	/**
	 * 削除完了アクション
	 *
	 */
	public function delFinishAction() {

		$request = $this->_request->getPost();
		$id = (isset($request['id'])) ? $request['id'] : null;

		// 通番 : 未入力チェック
		if (!$id)
			throw new Exception(ERR_MSG_PARAM, self::ERROR_CODE_ERROR_PARAM);						

		// 削除
		$wheres['id'] = $id;
		$dao_admin_user = new DaoAdminUser();
		if (!$dao_admin_user->delete($wheres))
			throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);		

	}


}