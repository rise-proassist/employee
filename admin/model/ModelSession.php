<?php

/**
 * MODEL セッション
 * @author kanemiya
 *
 */

class ModelSession {

	private $_session_dir;

	/**
	 * コンストラクタ
	 *
	 */
	function __construct() {

	}

	/**
	 * セッションディレクトリを指定する
	 * 
	 * @param string $session_dir セッションディレクトリ
	 */
	public function set_dir($session_dir) {

		$this->_session_dir = $session_dir;

	}

	/**
	 * セッションを開始する 
	 * 
	 */
	function open() {

		if ($this->_session_dir)
			session_save_path($this->_session_dir);
		
		session_start([
			'gc_maxlifetime' => 60 * 60 * 24 * 7,
			'cookie_lifetime' => 60 * 60 * 24 * 7,
		]);

		header('Expires:-1');
		header('Cache-Control:');
		header('Pragma:');

	}

	/**
	 * セッションIDを再発行する 
	 * 
	 */
	function regenerate_id() {

		session_regenerate_id(true);

	}

	/**
	 * セッションを削除する 
	 * 
	 */
	function destroy() {

		session_destroy();

	}

	/**
	 * セッション値をセットする
	 * 
	 * @param $key string キー値
	 * @param $value セッション値
	 */
	function set($key = null, $value = null) {

		if (!$key || !$value)
			return false;

		$_SESSION[$key] = $value;

	}

	/**
	 * セッション値を取得
	 * 
	 * @param $key string キー値
	 * @return セッション値 or false
	 */
	function get($key = null) {

		if (!$key)
			return false;

		return isset($_SESSION[$key]) ? $_SESSION[$key] : false;

	}

	/**
	 * セッションを閉じる 
	 * 
	 */
	function close() {

		session_write_close();

	}

	/**
	 * ログイン判定
	 * 
	 * @return boolean ログイン状態(true : ログイン中, false : 未ログイン)
	 */
	function is_login() {

		return isset($_SESSION['id']) ? true : false;

	}

}