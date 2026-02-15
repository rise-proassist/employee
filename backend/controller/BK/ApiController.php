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

	const ERROR_CODE_ERROR_INPUT_REQUIRED = 901;	// 入力値:未入力エラー
	const ERROR_CODE_ERROR_INPUT_PARAM = 902;		// 入力値:パラメータエラー
	const ERROR_CODE_ERROR_INPUT_RANGE = 903;		// 入力値:範囲エラー
	const ERROR_CODE_ERROR_BASE_SYSTEM = 998;		// 基盤:システムエラー
	const ERROR_CODE_ERROR_BASE_DB = 999;			// 基盤:DBエラー

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

		// ログイン種別チェック
		if (!$is_login)
			return false;

		$this->_login_user = $user;

	}

	/**
	 * セッション画像出力APIアクション
	 *
	 */
	public function generateImageAction() {

		$request = $this->_request->getQuery();
		$img_token = isset($request['img_token']) ? $request['img_token'] : null;

		if (!$img_token)
			echo false;

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$upload_certs = $model_session->get('upload_certs');
		foreach ($upload_certs as $upload_cert) {		

			if ($upload_cert['image_session_token'] == $img_token) {

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
	 * 該当年月の日付を取得する
	 *
	 */
	public function getDaysAction() {

		$year = $this->_request->getPost('year');
		$month = $this->_request->getPost('month');

		$response = new DtoResponseApiGetDays();

		try {

			// 年 : 未入力チェック
			if (UtilCommon::is_empty($year))
				throw new Exception(sprintf(ERR_MSG_EMPTY, '年'), self::ERROR_CODE_ERROR_INPUT_REQUIRED);

			// 年 : 数値チェック
			if (!UtilCommon::is_num($year))
				throw new Exception(sprintf(ERR_MSG_NUM, '年'), self::ERROR_CODE_ERROR_INPUT_PARAM);

			// 年 : 範囲チェック
			if (1900 <= $year && $year >= 2999)
				throw new Exception(sprintf(ERR_MSG_INPUT, '年'), self::ERROR_CODE_ERROR_INPUT_RANGE);

			// 月 : 未入力チェック
			if (UtilCommon::is_empty($month))
				throw new Exception(sprintf(ERR_MSG_EMPTY, '月'), self::ERROR_CODE_ERROR_INPUT_REQUIRED);

			// 月 : 数値チェック
			if (!UtilCommon::is_num($month))
				throw new Exception(sprintf(ERR_MSG_NUM, '月'), self::ERROR_CODE_ERROR_INPUT_PARAM);

			// 月 : 範囲チェック
			if (1 <= $month && $month >= 12)
				throw new Exception(sprintf(ERR_MSG_INPUT, '月'), self::ERROR_CODE_ERROR_INPUT_REQUIRED);				
			// 月末日
			$response->days = UtilCommon::get_week_days($year, $month);

		} catch (Exception $e) {

			$response->code = $e->getCode();
			$response->message = $e->getMessage();

		}

		$this->_response = $response;

	}

	/**
	 * 選択した都道府県に紐づく都市を取得する
	 *
	 */
	public function getCitiesAction() {

		$prefecture_id = $this->_request->getPost('prefecture_id');

		$response = new DtoResponseApiGetCities();

		try {

			// 都道府県ID : 未入力チェック
			if (UtilCommon::is_empty($prefecture_id))
				throw new Exception(sprintf(ERR_MSG_EMPTY, '都道府県ID'), self::ERROR_CODE_ERROR_INPUT_REQUIRED);

			// 都道府県ID : 数値チェック
			if (!UtilCommon::is_num($prefecture_id))
				throw new Exception(sprintf(ERR_MSG_NUM, '都道府県ID'), self::ERROR_CODE_ERROR_INPUT_PARAM);

			// 都市取得
			$dao_city = new DaoCity();
			$city_datas = $dao_city->select_join_prefecture(array('prefecture_id' => $prefecture_id));

			// 存在チェック : 都市情報
			if (!$city_datas)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '都市情報'), self::ERROR_CODE_ERROR_INPUT_PARAM);

			$cities = array();
			foreach ($city_datas as $key => $city_data) {
				$cities[$key]['id'] = $city_data->id;
				$cities[$key]['name'] = $city_data->name;
			}
			
			$response->cities = $cities;

		} catch (Exception $e) {

			$response->code = $e->getCode();
			$response->message = $e->getMessage();

		}

		$this->_response = $response;

	}

	/**
	 * プロフィール画像アップロード
	 *
	 */
	public function postProfileImageAction () {

		$image = $this->_request->getFiles('blob');

		try {

			// ユーザ画像IDがPOSTされた場合、自分の画像であるかをチェック
			$dao_user = new DaoUser();
			$user = $dao_user->select_by_key($this->_login_user->id);
			if (!$user->id)
				throw new Exception(ERR_MSG_RETRY, 600);

			// ユーザ用の画像ディレクトリ
			$assets_user_dir = ASSETS_USER_DIR . '/' . $user->code . '/';

			// 画像ファイルを削除（あれば）
			if ($user->profile_image_file)
				UtilFile::delete_file($user->profile_image_file, $assets_user_dir);

			// 画像ファイルをアップロード
			mt_srand(UtilCommon::make_seed());
			$image_prefix = date("YmdHis") . '_' . mt_rand();
			$profile_image_file = $image_prefix . '.' . UtilFile::get_extension($image['name']);
			// $profile_image_file = $image_prefix . '.jpg';
			if (!UtilFile::upload_file($image['tmp_name'], $profile_image_file, $assets_user_dir))
				throw new Exception(sprintf(ERR_MSG_FAILED, 'プロフィール画像のアップロード'), 1);

			$sets['profile_image_file'] = $profile_image_file;
			$wheres['id'] = $this->_login_user->id;
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_ERROR_BASE_DB);

			// セッションの画像を変更する
			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$user->profile_image_file = $profile_image_file . '?ts=' . date("YmdHis");
			$model_session->set('user', $user);

			$model_session->close();


		} catch (Exception $e) {

			$this->_response->code = $e->getCode();
			$this->_response->message = $e->getMessage();

		}

	}

	/**
	 * プロフィール画像削除
	 *
	 */
	public function deleteProfileImageAction () {

		try {

			$dao_user = new DaoUser();
			$user = $dao_user->select_by_key($this->_login_user->id);
			if ($user) {

				// 画像ファイルを削除
				UtilFile::delete_file($user->profile_image_file, ASSETS_USER_DIR . '/' . $user->code . '/');

				// 画像ファイルデータを削除
				$sets['profile_image_file'] = null;
				$wheres['id'] = $this->_login_user->id;
				if (!false === $dao_user->update($sets, $wheres))
					throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_ERROR_BASE_DB);

			}

			// セッションの画像を変更する
			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$user->profile_image_file = null;
			$model_session->set('user', $user);

			$model_session->close();


		} catch (Exception $e) {

			$this->_response->code = $e->getCode();
			$this->_response->message = $e->getMessage();

		}

	}

	/**
	 * 商品画像削除
	 *
	 */
	public function deleteProductImageAction () {

		$request = $this->_request->getPost();
		$product_image_id = $this->_request->getPost('id');
		$product_id = $this->_request->getPost('pid');

		try {

			// 出品者 : 一致チェック
			$dao_product = new DaoProduct();
			$product = $dao_product->select_by_key($product_id);
			if ($product->sell_user_id != $this->_login_user->id)
				throw new Exception(ERR_MSG_UNMATCH_USER_PRODUCT, self::ERROR_CODE_ERROR_INPUT_PARAM);

			// 出品画像 : 存在チェック
			$dao_product_image = new DaoProductImage();
			$product_image = $dao_product_image->select_where_one(
				array('id' => $product_image_id, 'product_id' => $product_id)
			);
			if(!$product_image)
				throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '出品画像'), self::ERROR_CODE_ERROR_INPUT_PARAM);

			// 画像ファイルを削除
			$assets_product_dir = ASSETS_PRODUCT_DIR . '/' . $product->code . '/';
			if (false === UtilFile::delete_file($product_image->file_name, $assets_product_dir))
				throw new Exception(ERR_MSG_RETRY, self::ERROR_CODE_ERROR_BASE_SYSTEM);

			// 画像ファイルデータを削除
			$wheres['id'] = $product_image_id;
			if (false === $dao_product_image->delete($wheres))
				throw new Exception(ERR_MSG_DB_ERROR, self::ERROR_CODE_ERROR_BASE_DB);

		} catch (Exception $e) {

			$this->_response->code = $e->getCode();
			$this->_response->message = $e->getMessage();

		}

	}

}