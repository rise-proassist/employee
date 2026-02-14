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
		return $_SESSION[$key];
	}

	/**
	 * セッションに値を格納する
	 * @return boolean 格納結果
	 */
	public static function set($key, $value) {
		return $_SESSION[$key] = $value;
	}
}

?>