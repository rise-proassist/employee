<?php

/**
 * CONTROLLER : API
 *
 *　@author kanemiya
 */

class ApiController extends BaseController {

	protected $_is_view = false;

	protected $_logger;

	private $_auth_actions = array();

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		// // ログインチェック
		// if (!UtilLogin::is_login())
		// 	parent::setView('login', 'form');
		
	}

	public function indexAction() {

	}

	/**
	 * 従事者アサインセットアクション
	 *
	 */
	public function setLocationAssginUserAction() {

		$location_shift_id = $this->_request->getPost('location_shift_id');
		$location_shift_row = $this->_request->getPost('location_shift_row');
		$user_id = $this->_request->getPost('user_id');
		$csrf_token = $this->_request->getPost('csrf_token');

		try {

			// csrfトークンチェック
			$model_session = new ModelSession();
			$model_session->set_dir(ADMIN_SESSION_DIR);
			$model_session->open();
			$session_token = $model_session->get('csrf_token');
			
			if (!$csrf_token || !$session_token || !hash_equals($session_token, $csrf_token))
				throw new Exception(ERR_MSG_BAD_REQUEST, __LINE__);

			// 未入力チェック : 現場シフトID
			if (!$location_shift_id)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフト'), __LINE__);

			// 未入力チェック : 現場シフト枠番号
			if (!$location_shift_row)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフト枠番号'), __LINE__);

			// 未入力チェック : 従事者ID
			if (!$user_id)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '従事者ID'), __LINE__);

			// 存在チェック : 現場シフトID
			$dao_location_shift = new DaoLocationShift();
			$location_shift = $dao_location_shift->select_by_key($location_shift_id);
			if (!$location_shift)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, 'シフト'), __LINE__);

			// 存在チェック : 従事者ID
			$dao_user = new DaoUser();
			$user = $dao_user->select_by_key($user_id);
			if (!$user)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '従事者'), __LINE__);

			// アサイン有無
			$dao_location_assign_user = new DaoLocationAssignUser();
			$location_assign_user = $dao_location_assign_user->select_where_one(
				array(
					'location_shift_id' => $location_shift_id,
					'location_shift_row' => $location_shift_row,
					'user_id' => $user_id
				)
			);

			if ($location_assign_user) {

				// アサイン済みの場合は、選択された従事者をセットする
				$sets['user_id'] = $user_id;
				$wheres['id'] = $location_assign_user->id; 
				if (false === $dao_location_assign_user->update($sets, $wheres))
					throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			} else {

				// 未アサインの場合は、アサインをセット
				$entity_location_assign_user = new EntityLocationAssignUser();
				$entity_location_assign_user->id = null;
				$entity_location_assign_user->location_shift_id = $location_shift_id;
				$entity_location_assign_user->location_shift_row = $location_shift_row;
				$entity_location_assign_user->user_id = $user_id;
				if (!$dao_location_assign_user->insert($entity_location_assign_user))
					throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			}

			$this->_response = new DtoResponseSetLocationAssginUser();
			$this->_response->code = 200;

		} catch (Exception $e) {

			$this->_response->code = $e->getCode();
			$this->_response->message = $e->getMessage();
			
		}

	}

	/**
	 * 担当資格セットアクション
	 *
	 */
	public function setAllocateCertsAction() {

		$location_shift_id = $this->_request->getPost('location_shift_id');
		$location_shift_row = $this->_request->getPost('location_shift_row');
		$allocate_cert_ids = $this->_request->getPost('allocate_cert_ids');
		$csrf_token = $this->_request->getPost('csrf_token');

		try {

			// csrfトークンチェック
			$model_session = new ModelSession();
			$model_session->set_dir(ADMIN_SESSION_DIR);
			$model_session->open();
			$session_token = $model_session->get('csrf_token');
			
			if (!$csrf_token || !$session_token || !hash_equals($session_token, $csrf_token))
				throw new Exception(ERR_MSG_BAD_REQUEST, __LINE__);

			// 未入力チェック : 現場シフトID
			if (!$location_shift_id)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフト'), __LINE__);

			// 未入力チェック : 現場シフト枠番号
			if (!$location_shift_row)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフト枠番号'), __LINE__);

			// アサイン有無
			$dao_location_assign_user = new DaoLocationAssignUser();
			$location_assign_user = $dao_location_assign_user->select_where_one(
				array(
					'location_shift_id' => $location_shift_id,
					'location_shift_row' => $location_shift_row
				)
			);

			if ($location_assign_user) {

				$sets['allocate_cert_ids'] = $allocate_cert_ids ? implode(',', $allocate_cert_ids) : null;
				$wheres['id'] = $location_assign_user->id; 
				if (false === $dao_location_assign_user->update($sets, $wheres))
					throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			}

			$this->_response = new DtoResponseSetLocationAssginUser();
			$this->_response->code = 200;

		} catch (Exception $e) {

			$this->_response->code = $e->getCode();
			$this->_response->message = $e->getMessage();
			
		}

	}

	// public function setLocationAssginUserAction() {

	// 	$location_shift_id = $this->_request->getPost('location_shift_id');
	// 	$user_id = $this->_request->getPost('user_id');

	// 	try {

	// 		// 未入力チェック : 現場シフトID
	// 		if (!$location_shift_id)
	// 			throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフト'), __LINE__);

	// 		// 未入力チェック : 従事者ID
	// 		if (!$user_id)
	// 			throw new Exception(sprintf(ERR_MSG_EMPTY, '従事者ID'), __LINE__);

	// 		// 存在チェック : シフト種別
	// 		$dao_location_shift = new DaoLocationShift();
	// 		$location_shift = $dao_location_shift->select_by_key($location_shift_id);
	// 		if (!$location_shift)
	// 			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, 'シフト'), __LINE__);

	// 		// 存在チェック : 従事者ID
	// 		$dao_user = new DaoUser();
	// 		$user = $dao_user->select_by_key($user_id);
	// 		if (!$user)
	// 			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '従事者'), __LINE__);

	// 		// アサイン有無
	// 		$dao_location_assign_user = new DaoLocationAssignUser();
	// 		$location_assign_user = $dao_location_assign_user->select_where_one(
	// 			array(
	// 				'location_shift_id' => $location_shift_id,
	// 				'user_id' => $user_id
	// 			)
	// 		);

	// 		if ($location_assign_user) {

	// 			// アサイン済みの場合は、アサインの削除
	// 			if (!$dao_location_assign_user->delete(
	// 				array(
	// 					'location_shift_id' => $location_shift_id,
	// 					'user_id' => $user_id
	// 				)
	// 			)) {
	// 				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);
	// 			}

	// 		} else {

	// 			// 未アサインの場合は、アサインをセット
	// 			$entity_location_assign_user = new EntityLocationAssignUser();
	// 			$entity_location_assign_user->id = null;
	// 			$entity_location_assign_user->location_shift_id = $location_shift_id;
	// 			$entity_location_assign_user->user_id = $user_id;
	// 			if (!$dao_location_assign_user->insert($entity_location_assign_user))
	// 				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

	// 			$dao_location_assign_user_notified = new DaoLocationAssignUserNotified();
	// 			$location_assign_user_notified = $dao_location_assign_user_notified->select_where_one(array('location_id' => $location_shift->location_id, 'user_id' => $user_id));
	// 			if (!$location_assign_user_notified) {
	// 				// アサイン通知を未通知でセット
	// 				$entity_location_assign_user_notified = new EntityLocationAssignUserNotified();
	// 				$entity_location_assign_user_notified->id = null;
	// 				$entity_location_assign_user_notified->location_id = $location_shift->location_id;
	// 				$entity_location_assign_user_notified->user_id = $user_id;
	// 				$entity_location_assign_user_notified->is_notified = false;
	// 				if (!$dao_location_assign_user_notified->insert($entity_location_assign_user_notified))
	// 					throw new Exception(ERR_MSG_DB_ERROR, __LINE__);
	// 			}

	// 		}

	// 		$this->_response = new DtoResponseSetLocationAssginUser();
	// 		$this->_response->code = 200;

	// 	} catch (Exception $e) {

	// 		$this->_response->code = $e->getCode();
	// 		$this->_response->message = $e->getMessage();
			
	// 	}

	// }

	/**
	 * 現場アサインメール通知フラグ更新アクション
	 *
	 */
	public function updLocationAssginUserIsNotifiedAction() {

		$location_id = $this->_request->getPost('location_id');
		$user_id = $this->_request->getPost('user_id');

		try {

			// 未入力チェック : 現場アサイン従事者ID
			if (!$location_id)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '現場アサイン従事者ID'), __LINE__);

			// 未入力チェック : 従事者ID
			if (!$user_id)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '従事者ID'), __LINE__);

			// 存在チェック : 通知
			$dao_location_assign_user_notified = new DaoLocationAssignUserNotified();
			$location_assign_user_notified = $dao_location_assign_user_notified->select_where_one(array('location_id' => $location_id, 'user_id' => $user_id));
			if (!$location_assign_user_notified)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場アサイン従事者通知情報'), __LINE__);

			if ($location_assign_user_notified->is_notified) {
				$sets['is_notified'] = false;
			} else {
				$sets['is_notified'] = true;
			}
			$wheres['location_id'] = $location_id;
			$wheres['user_id'] = $user_id;
			if (false === $dao_location_assign_user_notified->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			$this->_response = new DtoResponseUpdLocationAssginUserIsNotified();
			$this->_response->code = 200;

		} catch (Exception $e) {

			$this->_response->code = $e->getCode();
			$this->_response->message = $e->getMessage();
			
		}

	}

	/**
	 * 業務時間記録アクション
	 *
	 */
	public function workRecordAction() {

		$location_shift_id = $this->_request->getPost('location_shift_id');
		$type = $this->_request->getPost('type');
		$code = $this->_request->getPost('code');

		try {

			// 未入力チェック : 現場シフトID
			if (!$location_shift_id)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフト'), __LINE__);

			// 未入力チェック : 記録種別
			if (!$type)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '記録種別'), __LINE__);

			// 未入力チェック : 従事者コード
			if (!$code)
				throw new Exception(sprintf(ERR_MSG_EMPTY, '従事者コード'), __LINE__);

			// 存在チェック : 従事者コード
			$dao_user = new DaoUser();
			$user = $dao_user->select_where_one(array('code' => $code));
			if (!$user)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '従事者'), __LINE__);

			// 存在チェック : 現場シフト
			$dao_location_shift = new DaoLocationShift();
			$location_shift = $dao_location_shift->select_where_with_location(array('ls.id' => $location_shift_id));
			if (!$location_shift)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, 'シフト'), __LINE__);

			// 入力値チェック : 業務種別
			if (!array_key_exists($type, PARAM_WORK_TYPES))
				throw new Exception(sprintf(ERR_MSG_INPUT, '打刻種別'), __LINE__);

			// アサイン済みチェック
			if (!$location_shift[0]->is_assigned)
				throw new Exception(sprintf(ERR_MSG_NOT_CONFIRM_ASSIGIN, 'シフト'), __LINE__);

			// アサイン有無チェック
			$dao_location_assign_user = new DaoLocationAssignUser();
			$location_assign_user = $dao_location_assign_user->select_where_one(
				array(
					'location_shift_id' => $location_shift_id,
					'user_id' => $user->id
				)
			);
			if (!$location_assign_user)
				throw new Exception(sprintf(ERR_MSG_NOT_ASSIGIN, '従事者'), __LINE__);

			switch ($type) {

				case PARAM_WORK_TYPE_BEGIN: // 始業

						// 打刻済みチェック
						if ($location_assign_user->work_date_from)
							throw new Exception(sprintf(ERR_MSG_RECORDED_YET, '始業'), __LINE__);

						// 始業で打刻
						$sets['work_date_from'] = date("Y-m-d H:i:s");

					break;

				case PARAM_WORK_TYPE_END: // 終業

						// 打刻済みチェック
						if (!$location_assign_user->work_date_from)
							throw new Exception(sprintf(ERR_MSG_NOT_FOUND, 'このシフトの始業打刻'), __LINE__);

						if ($location_assign_user->work_date_to)
							throw new Exception(sprintf(ERR_MSG_RECORDED_YET, '終業'), __LINE__);

						// 終業で打刻
						$sets['work_date_to'] = date("Y-m-d H:i:s");

					break;
				
				default:
					throw new Exception(ERR_MSG_RETRY, __LINE__);
					break;

			}

			// 打刻情報を更新
			$wheres['id'] = $location_assign_user->id;
			if (false === $dao_location_assign_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			$this->_response = new DtoResponseWorkRecord();
			$this->_response->code = 200;
			$this->_response->message = MSG_RECORD_SUCCESS;

		} catch (Exception $e) {

			$this->_response->code = $e->getCode();
			$this->_response->message = $e->getMessage();
			
		}

	}

	/**
	 * 打刻実行アクション
	 *
	 */
	// public function recprdExecAction() {

	// 	$code = $this->_request->getPost('code');
	// 	$location_shift_id = $this->_request->getPost('location_shift_id');
	// 	$type = $this->_request->getPost('type');

	// 	try {

	// 		// 未入力チェック : 従事者コード
	// 		if (!$code)
	// 			throw new Exception(sprintf(ERR_MSG_EMPTY, '従事者コード'), __LINE__);

	// 		// 未入力チェック : 現場シフトID
	// 		if (!$location_shift_id)
	// 			throw new Exception(sprintf(ERR_MSG_EMPTY, '現場シフトID'), __LINE__);

	// 		// 未入力チェック : 業務種別
	// 		if (!$type)
	// 			throw new Exception(sprintf(ERR_MSG_EMPTY, '業務種別'), __LINE__);

	// 		// 存在チェック : 従事者コード
	// 		$dao_user = new DaoUser();
	// 		$user = $dao_user->select_where_one(array('code' => $code));
	// 		if (!$user)
	// 			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '業務種別'), __LINE__);

	// 		// 存在チェック : 現場シフト
	// 		$dao_location_shift = new DaoLocationShift();
	// 		$location_shift = $dao_location_shift->select_where_with_location(array('id' => $location_shift_id));
	// 		if (!$location_shift)
	// 			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '現場シフト'), __LINE__);

	// 		// 入力値チェック : 業務種別
	// 		if (!array_key_exists($type, PARAM_WORK_TYPES))
	// 			throw new Exception(sprintf(ERR_MSG_INPUT, '打刻種別'), __LINE__);

	// 		// アサイン済みチェック : 現場
	// 		if (!$location_shift[0]->is_assigned)


	// 		// 締め処理済みチェック : 現場

	// 		// アサイン済みチェック : 現場シフト

	// 		switch ($type) {

	// 			case PARAM_WORK_TYPE_BEGIN:

	// 				// 始業打刻済みチェック : 従事者業務記録

	// 				// 始業打刻で業務記録を作成

	// 				break;

	// 			case PARAM_WORK_TYPE_END:

	// 				// 存在チェック : 従事者業務記録

	// 				// 終業打刻済みチェック : 従事者業務記録

	// 				// 終業打刻で業務記録を更新

	// 				break;

	// 		}


	// 		$this->_response = new DtoResponseUpdLocationAssginUserIsNotified();
	// 		$this->_response->code = 200;

	// 	} catch (Exception $e) {

	// 		$this->_response->code = $e->getCode();
	// 		$this->_response->message = $e->getMessage();
			
	// 	}

	// }


}