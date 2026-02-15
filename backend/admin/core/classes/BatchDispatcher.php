<?php

/**
 * バッチ用振り分けクラス
 *
 * @author kanemiya
 */

class BatchDispatcher {

	protected $_logger;

	function __construct() {

		// ログ設定ファイル
		Logger::configure(LIB_DIR . '/log4php/src/log4php.admin.properties');

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
	public function dispatch($argv) {

		// 1番目のパラメーターをコントローラーとして取得
		$controller = null;
		if (1 < count($argv))
			$controller = $argv[1];

		// 1番目のパラメーターをもとにコントローラークラスインスタンス取得
		$controller_instance = $this->getControllerInstance($controller);
		if (is_null($controller_instance)) {

			// 存在しない場合は、エラー
			echo sprintf(ERR_MSG_NOT_FOUND . '[%s]', 'コントローラー', $controller);
			exit;
			
		}

		// 2番目のパラメーターをコントローラーとして取得
		$action = 'index';
		if (2 < count($argv))
			$action = $argv[2];

		// アクションメソッドの存在確認
		if (!method_exists($controller_instance, $action . 'Action')) {

			// 存在しない場合は、エラー
			echo sprintf(ERR_MSG_NOT_FOUND . '[%s]', 'アクション', $action);
			exit;

		}

		// コントローラー初期設定
		$controller_instance->setController($controller);

		// アクション初期設定
		$controller_instance->setAction($action);

		// 引数セット
		$controller_instance->setArgv($argv);
		
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