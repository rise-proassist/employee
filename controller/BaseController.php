<?php

/**
 * BASE CONTROLLER : 基底コントローラ
 *
 *　@author kanemiya
 */

abstract class BaseController {

	protected $_controller = 'index';
	
	protected $_action = 'index';

	protected $_error_code;

	protected $_error_message;
	
	protected $_view;
	
	protected $_request;

	protected $_response;
	
	protected $_template_path;

	protected $_api_name;

	const ERROR_CODE_BASE_DB_ERROR = 90000;

	/**
	 * コンストラクタ
	 *
	 */
	function __construct() {

		$this->_logger = Logger::getLogger(basename(__FILE__));
		$this->_logger->setLevel(LoggerLevel::getLevelInfo());

		// ヘッダ情報をロギング
		$this->put_request_log(getallheaders());

		// リクエスト
		$this->_request = new Request();

		if ($this->_request->getPost())
			$this->put_request_log($this->_request->getPost(), '[Request-POST]');

		if ($this->_request->getQuery())
			$this->put_request_log($this->_request->getQuery(), '[Request-GET]');

	}

	/**
	 * デストラクタ
	 *
	 */
	function __destruct() {

		// 終了ログ
		$this->_logger->info('[FINISH API] ' . $this->_api_name . ' ============== ');

	}

	/**
	 * コントローラーセッタ
	 *
	 * @param $controller コントローラー
	 */
	public function setController($controller) {

		$this->_controller = $controller;
	
	}

	/**
	 * アクションセッタ
	 *
	 * @param $action アクション
	 */
	public function setAction($action) {

		$this->_action = $action;
	
	}

	/**
	 * アクション名取得
	 *
	 * @return string アクション名
	 */
	public function getActionName() {

		return sprintf('%sAction', $this->_action);

	}

	/**
	 * 実行アクションを、同一コントローラー内の別のアクションに切り替える
	 *
	 * @return string アクション名
	 */
	public function changeOtherAction($action) {

		$this->setAction($action);
		$method_name = $this->getActionName();
		$this->$method_name();

	}

	/** 
	 * 処理実行
	 *
	 */
	public function run() {

		try {

			// 開始ログ
			$this->_api_name = 'request' . ucfirst($this->_controller) . ucfirst($this->_action);
			$this->_logger->info('[START API] ' . $this->_api_name . ' ============== ');

			// ビュー、レスポンスの初期化
			if ($this->_is_view) {

				$this->initView();
			
			} else {

				$this->_response = new DtoResponseBase();

			}

			// バリデーション
			if (!$this->requestValidate())
				throw new Exception(ERR_MSG_VALIDATE_ERROR, 1);

			// コントローラの前処理
			$this->preAction();

			// コントローラのメイン処理
			$method_name = $this->getActionName();

			$this->$method_name();            

		} catch (Exception $e) {

			// echo $e->getMessage();
			$this->_logger->fatal($e->getMessage());
	
			if ($this->_is_view) {

				$this->_controller = 'error';
				$this->_action = 'index';
				$this->_error_code = $e->getCode();
				$this->_error_message = $e->getMessage();

			} else {

				$this->_response->code = $e->getCode();
				$this->_response->message = $e->getMessage();

			}

		}

		// 表示
		if ($this->_is_view) {

			// テンプレートビューで返却
			$this->_view->assign('controller', $this->_controller);
			$this->_view->assign('action', $this->_action);

			if (isset($this->_error_code))
				$this->_view->assign('error_code', $this->_error_code);

			if (isset($this->_error_message))
				$this->_view->assign('error_message', $this->_error_message);

			$this->_view->display($this->_template_path);
		
		} else {

			$json = json_encode($this->_response);

			$this->_logger->info($json);

			// JSON形式で返却
			header('Content-type: application/json; charset=utf-8');
			echo $json;

		}

		return;
	}

	/**
	 * バリデーション
	 *
	 */
	protected function requestValidate() {

		$request = ($this->_request->getPost()) ? $this->_request->getPost() : $this->_request->getQuery();

		// リクエストDtoが存在する場合は、バリデーションチェックを行う
		$dto_class = 'DtoRequest' . ucfirst($this->_controller) . ucfirst($this->_action);
		$dto = (class_exists($dto_class)) ? new $dto_class : null;
		if (!$dto)
			return true;

		foreach ($dto as $dto_variable => $validates) {

			if(!is_array($validates))
				continue;

			foreach ($validates as $validate => $validate_value) {

				$req_variable = (isset($request[$dto_variable])) ? $request[$dto_variable] : null;

				switch ($validate) {

					case 'required': // 必須入力チェック
						if ($validate_value && is_null($req_variable))
							return false;
						
						break;

					case 'min':	// 数値範囲チェック（最小値）

						if ($validate_value > $req_variable)
							return false;

						break;

					case 'max':	// 数値範囲チェック（最大値）

						if ($validate_value < $req_variable)
							return false;
						
						break;


					case 'str_min':	// 文字数チェック（最小値）

						if ($validate_value > mb_strlen($req_variable))
							return false;
						
						break;


					case 'str_max':	// 文字数チェック（最大値）

						if ($validate_value < mb_strlen($req_variable))
							return false;
						
						break;

				}

			}

		}

		return true;

	}

	/**
	 * リクエスト情報をログに書き込む
	 *
	 */
	private function put_request_log($request, $prefix = '[Request]') {

		$params = $prefix . ' ';
		foreach ($request as $column => $value) {
			if (is_array($value)) {
				foreach ($value as $val) {

					// 配列の場合はロギングをスキップ
					if (is_array($val)) {
						$this->_logger->info('params is array...');
						continue;
					}

					$params = $prefix . ' ' . '[' . $column . ']:'. $val;
					$this->_logger->info($params);
				}
			} else {
				$params = $prefix . ' ' . '[' . $column . ']:'. $value;
				$this->_logger->info($params);
			}
		}

	}

	/** 
	 * ビューの初期化
	 *
	 */
	protected function initView() {

		$this->_view = new Smarty();
		$this->_view->template_dir = ROOT_DIR . '/view/';
		$this->_view->compile_dir = ROOT_DIR . '/tmp/';
		$this->_template_path = 'index.html';
		// $this->_template_path = sprintf('%s/%s.html', $this->_controller, $this->_action);

	}

	/**
	 * ビューを定義
	 *
	 */
	protected function setView($controller, $action = 'index') {

		$this->_template_path = $controller . '/' . $action . '.html';

	}

	/**
	 * 共通前処理
	 *
	 */
	protected function preAction() {}

	/**
	 * 共通デフォルト処理
	 *
	 */
	protected function indexAction() {}


	/**
	 * 404エラー画面へ遷移させる
	 *
	 */
	protected function to404() {

		header("HTTP/1.1 404 Not Found");
		print(file_get_contents(DEFAULT_404_REDIRECT_URL));
		exit;

	}

}