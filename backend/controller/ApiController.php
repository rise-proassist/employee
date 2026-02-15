<?php

/**
 * CONTROLLER : Api
 *
 *　@author kanemiya
 */

class ApiController extends BaseController {

	protected $_is_view = false;

	protected $_logger;

	private $_auth_actions = array();

	private $_login_user;

	const ERROR_CODE_ERROR_PARAM = 900001;
	const ERROR_CODE_NOT_FOUND = 900002;
	const ERROR_CODE_BASE_DB_ERROR = 900006;

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		// $model_session = new ModelSession();
		// $model_session->set_dir(SESSION_DIR);
		// $model_session->open();

		// $user = $model_session->get('user');
		// $is_login = $model_session->is_login();

		// $model_session->close();

		// // ログイン種別チェック
		// if (!$is_login)
		// 	header('Location: ' . UtilCommon::get_base_url('login'));

		// // $this->_view->assign('login_user', $user);
		// $this->_login_user = $user;

	}

	/**
	 * セッション画像出力APIアクション
	 *
	 */
	public function generateImageAction() {

		$request = $this->_request->getQuery();
		$cert_id = isset($request['cert_id']) ? $request['cert_id'] : null;
		$cert_type = isset($request['cert_type']) ? $request['cert_type'] : null;

		if (!$cert_id)
			echo false;

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$upload_certs = $model_session->get('upload_certs');
		$data = null;
		foreach ($upload_certs as $upload_cert) {		

			if ($upload_cert['cert_id'] == $cert_id && $upload_cert['cert_type'] == $cert_type) {

				switch ($upload_cert['type']) {

					case IMAGETYPE_JPEG:
						header('content-type: image/jpeg');
						break;
					
					case IMAGETYPE_PNG:
						header('content-type: image/png');
						break;

					case IMAGETYPE_GIF:
						header('content-type: image/gif');
						break;

				}

				break;

			}

		}

		echo $upload_cert['data'];

	}

	/**
	 * QRコード画像出力APIアクション
	 *
	 */
	public function generateQrAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$user = $model_session->get('user');
		if (!$user)
			echo false;

		header('Content-Type: image/png');
		QRcode::png($user->code);

	}

	/**
	 * バーコード画像出力APIアクション
	 *
	 */
	public function generateBarAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$user = $model_session->get('user');
		if (!$user)
			echo false;

		UtilBarcode::create($user->code);

	}

	/**
	 * 郵便番号住所検索APIアクション
	 *
	 */
	public function searchAddressAction() {

		$request = $this->_request->getQuery();
		$zipcode = isset($request['zipcode']) ? preg_replace('/\D/u', '', $request['zipcode']) : '';

		$response = new DtoResponseApiSearchAddress();

		try {

			if (7 != strlen($zipcode)) {
				$response->success = false;
				$response->code = self::ERROR_CODE_ERROR_PARAM;
				$response->message = 'Invalid zipcode. Must be 7 digits.';
				$this->_response = $response;
				return;
			}

			$dba = DatabaseAccess::get_instance();
			$pdo = $dba->getPdo();

			$sql = 'SELECT `prefecture`, `city`, `town` FROM `postal_codes` WHERE LPAD(REPLACE(REPLACE(CAST(`zip_code` AS CHAR), "-", ""), " ", ""), 7, "0") = :zip_code ORDER BY `id` ASC';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':zip_code', $zipcode, PDO::PARAM_STR);
			$stmt->execute();

			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (!$rows) {
				$response->success = false;
				$response->code = self::ERROR_CODE_NOT_FOUND;
				$response->message = 'No address found.';
				$this->_response = $response;
				return;
			}

			$addresses = array();
			foreach ($rows as $row) {
				$addresses[] = array(
					'prefecture' => (string)$row['prefecture'],
					'city' => (string)$row['city'],
					'town' => (string)$row['town'],
				);
			}

			$response->success = true;
			$response->address = $addresses[0];
			$response->addresses = $addresses;

		} catch (Exception $e) {

			$response->success = false;
			$response->code = $e->getCode();
			$response->message = $e->getMessage();

		}

		$this->_response = $response;

	}

	/**
	 * 申し込み承認APIアクション
	 * 
	 * メール配信が正常に完了したら結果OK、失敗していたらNG
	 *
	 */
	public function entryAcceptAction() {

		$request = $this->_request->getPost();
		$person_id = isset($request['person_id']) ? $request['person_id'] : null;

		$response = new DtoResponseEntryAccept();

		try {

			// 作業者存在チェック
			$dao_um_person = new DaoUmPerson();
			$person = $dao_um_person->select_by_key($person_id);
			if (!$person)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '該当する作業者情報'), self::ERROR_CODE_NOT_FOUND);

			// 登録ステータスを更新
			$sets['ft_reg_st_id'] = PARAM_CONST_REG_ST_ID_ACCEPT_DUNE;
			$wheres['id'] = $person_id;
			if (false === $dao_um_person->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// 性別
			$dao_ft_gender = new DaoFtGender();
			$gender = $dao_ft_gender->select_by_key($person->ft_gender_id);

			// 国籍
			$dao_ft_country = new DaoFtCountry();
			$country = $dao_ft_country->select_by_key($person->ft_country_id);

			// 雇用形態
			$dao_ft_cont = new DaoFtCont();
			$cont = $dao_ft_cont->select_by_key($person->ft_cont_id);

			// 所属会社
			$dao_ft_company = new DaoFtCompany();
			$company = $dao_ft_company->select_by_key($person->ft_company_id);

			// メール配信
			$mail_params['name'] = $person->name;
			$mail_params['furigana'] = $person->furigana;
			$mail_params['tel'] = $person->tel;
			$mail_params['bday'] = $person->bday;
			$mail_params['email'] = $person->email;
			$mail_params['postcode'] = $person->postcode;
			$mail_params['addr'] = $person->addr;
			$mail_params['gender_name'] = $gender->name;
			$mail_params['cont_name'] = $cont->name;
			$mail_params['country_name'] = $country->name;
			$mail_params['company_name'] = $company->name;
			$mail_params['etc_company_name'] = $person->company_name;
			UtilMail::send(NOTICE_EMAIL, $person->email , MAIL_SUBJECT_ENTRY_ACCEPT, 'entry_accept', $mail_params);

		} catch (Exception $e) {

			$response->code = $e->getCode();
			$response->message = $e->getMessage();

		}

		$this->_response = $response;

	}

	/**
	 * 申し込み却下APIアクション
	 *
	 */
	public function entryDenyAction() {

		$request = $this->_request->getPost();
		$person_id = isset($request['person_id']) ? $request['person_id'] : null;

		$response = new DtoResponseEntryAccept();

		try {

			// 作業者存在チェック
			$dao_um_person = new DaoUmPerson();
			$person = $dao_um_person->select_by_key($person_id);
			if (!$person)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '該当する作業者情報'), self::ERROR_CODE_NOT_FOUND);

			// 登録ステータスを更新
			$sets['ft_reg_st_id'] = PARAM_CONST_REG_ST_ID_DENY_DUNE;
			$wheres['id'] = $person_id;
			if (false === $dao_um_person->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_BASE_DB_ERROR);

			// メール配信
			$mail_params['name'] = $person->name;
			UtilMail::send(NOTICE_EMAIL, $person->email , MAIL_SUBJECT_ENTRY_DENY, 'entry_deny', $mail_params);

		} catch (Exception $e) {

			$response->code = $e->getCode();
			$response->message = $e->getMessage();

		}

		$this->_response = $response;


	}

}