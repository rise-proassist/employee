<?php

/**
 * ログインユーティリティ
 *
 * @author kanemiya
 *
 */

class UtilLogin {

	/**
	 * ログアウトする
	 */
	public static function logout() {
		
		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(ADMIN_SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}

		// セッション変数を破棄(初期化)する
		$_SESSION = array();
		
		// セッションを開放する
		return session_destroy();
		
	}
	
	/**
	 * ログイン済み判定
	 * @return boolean ログイン判定(true:ログイン中, false:未ログイン)
	 */
	public static function is_login() {

		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(ADMIN_SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}

		// セッションが存在しない場合は、未ログイン
		if(!isset($_SESSION['sid'])) {
			return false;
		}
		
		// ログイン中
		return true;
	}

	/**
	 * 管理者判定
	 * @return boolean 管理者判定(true:管理者, false:非管理者)
	 */
	public static function is_root() {

		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}

		return ($_SESSION['is_root']) ? true : false;
	}

	/**
	 * ログインID（通番）を取得する
	 * @return string ログインユーザ名
	 */
	public static function get_id() {
		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}
		return $_SESSION['id'];
	}

	/**
	 * ログインユーザ名を取得する
	 * @return string ログインユーザ名
	 */
	public static function get_name() {
		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}

		return $_SESSION['name'];
	}

	/**
	 * ログインユーザ名を変更する
	 * @return string ログインユーザ名
	 */
	public static function set_name($name) {
		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}
		return $_SESSION['name'] = $name;
	}

	/**
	 * ログインユーザの権限を取得する
	 * @return int ログインユーザの権限
	 */
	public static function get_auth_level() {
		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}
		return $_SESSION['auth_level'];
	}

	/**
	 * ログインユーザの権限を変更する
	 * @return int ログインユーザの権限
	 */
	public static function set_auth_level($auth_level) {
		$sid = session_id();
		if (!$sid) {
			// セッションを開始
			session_save_path(SESSION_DIR);
			session_name('sid');
			session_start();
			header('Expires: -1');
			header('Cache-Control:');
			header('Pragma:');
		}
		return $_SESSION['auth_level'] = $auth_level;
	}

}

?>