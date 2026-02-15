<?php

/**
 * 振り分けクラス
 *
 * @author kanemiya
 */

class Dispatcher {

	protected $_logger;

	function __construct() {

		// ログ設定ファイル
		$log4php_config = LIB_DIR . '/log4php/src/log4php.admin.local.properties';
		if (!file_exists($log4php_config)) {
			$log4php_config = LIB_DIR . '/log4php/src/log4php.admin.properties';
		}
		Logger::configure($log4php_config);

		// ホスト情報をロギング
		LoggerMDC::put('ADDR', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-');
		LoggerMDC::put('HOST', isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : '-');

		$this->_logger = Logger::getLogger(basename(__FILE__));
		$this->_logger->setLevel(LoggerLevel::getLevelInfo());

	}


	/**
	 * 振り分け処理実行
	 *
	 */
	public function dispatch() {

		// パラメーター取得（末尾の / は削除）
		$param = preg_replace('/\/$/', '', $_SERVER['REQUEST_URI']);

		// パラメーターを / で分割
		$params = array();
		if ('' != $param)
			$params = explode('/', $param);

		// 1番目のパラメーターをコントローラーとして取得
		$controller = ADMIN_DEFAULT_CONTROLLER;
		if (1 < count($params))
			$controller = $params[1];

		// 1番目のパラメーターをもとにコントローラークラスインスタンス取得
		$controller_instance = $this->getControllerInstance($controller);
		if (is_null($controller_instance)) {

			// 存在しない場合は、404エラー
			header("HTTP/1.1 404 Not Found");
			exit;
			
		}

		// 2番目のパラメーターをコントローラーとして取得
		$action = 'index';
		if (2 < count($params))
			$action= $params[2];

		// アクションメソッドの存在確認
		if (!method_exists($controller_instance, $action . 'Action')) {

			// 存在しない場合は、404エラー
			header("HTTP/1.1 404 Not Found");
			exit;

		}

		// コントローラー初期設定
		$controller_instance->setController($controller);

		// アクション初期設定
		$controller_instance->setAction($action);
		
		// 処理実行
		$controller_instance->run();
	
	}

	/**
	 * コントローラークラスのインスタンスを取得
	 *
	 */
	private function getControllerInstance($controller) {

		// 一文字目のみ大文字に変換＋"Controller" Ex.XxxxController
		$class_name = ucfirst($controller) . 'Controller';
		
		// クラスインスタンス生成
		$controller_instance = null;
		if (class_exists($class_name))
			$controller_instance = new $class_name();

		return $controller_instance;

	}

}