<?php

/**
 * セッションユーティリティ
 *
 * @author kanemiya
 *
 */
class UtilSession {

	/**
	 * セッションを取得する
	 * @return string ログインユーザ名
	 */
	public static function get($key) {
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
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null ;
	}

	/**
	 * セッションを変更する
	 * @return int ポイント
	 */
	public static function set($key, $value) {
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
		return $_SESSION[$key] = $value;
	}
}

?>