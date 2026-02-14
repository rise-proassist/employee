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
			session_save_path(SESSION_DIR);
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

		@session_start();

		// $sid = session_id();
		// if (!$sid) {
		// 	// セッションを開始
		// 	session_save_path(SESSION_DIR);
		// 	session_name('sid');
		// 	session_start();
		// 	header('Expires: -1');
		// 	header('Cache-Control:');
		// 	header('Pragma:');
		// }

		// セッションが存在しない場合は、未ログイン
		if(!isset($_SESSION['id'])) {
			return false;
		}
		
		// ログイン中
		return true;
	}

	/**
	 * ログインID（通番）を取得する
	 * @return string ログインユーザ名
	 */
	public static function get_login_id() {
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
	public static function get_login_user_name() {
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
	public static function set_login_user_name($name) {
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
	 * ログインユーザ種別を取得する
	 * @return string ログインユーザ名
	 */
	public static function get_login_user_type() {

		@session_start();

		// $sid = session_id();
		// if (!$sid) {
		// 	// セッションを開始
		// 	session_save_path(SESSION_DIR);
		// 	session_name('sid');
		// 	session_start();
		// 	header('Expires: -1');
		// 	header('Cache-Control:');
		// 	header('Pragma:');
		// }

		return $_SESSION['type'];
	}

function require_unlogined_session () {
    // セッション開始
    @session_start();
    // ログインしていれば
    if (isset($_SESSION["username"])) {
        header('Location: ./index.php');
        exit;
    }
}

function require_logined_session() {
    // セッション開始
    @session_start();
    // ログインしていなければlogin.phpに遷移
    if (!isset($_SESSION["username"])) {
        header('Location: ./login.php');
        exit;
    }
}

	// CSRFトークンの生成
	public static function generate_token() {

		// セッションIDからハッシュを生成
		return hash ( 'sha256', session_id() );

	}

	// CSRFトークン
	public static function validate_token ($token) {

		return $token === self::generate_token();

	}

	// htmlspecialchars
	public static function hsc ($var) {

		if (is_array($var)) {
			return array_map(hsc, $var);

		} else {

		 	return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');

		}

	}

}

?>