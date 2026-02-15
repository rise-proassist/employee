<?php

/**
 * セッションを開始する 
 * 
 */
function session_open() {

	session_save_path(SESSION_DIR);
	@session_start();

}

/**
 * セッション値をセットする
 * 
 * @param $key string キー値
 * @param $value セッション値
 */
function set_session($key = null, $value = null) {

	if (!$key || $value)
		return false;

	$_SESSION[$key] = $value;

}

/**
 * セッション値を取得
 * 
 * @param $key string キー値
 * @return セッション値 or false
 */
function set_session($key = null) {

	if (!$key)
		return false;

	reutrn isset($_SESSION[$key]) ? $_SESSION[$key] : false;

}

/**
 * セッションを閉じる 
 * 
 */
function session_close() {

	session_write_close();

}

/**
 * ログイン判定
 * 
 * @return boolean ログイン状態(true : ログイン中, false : 未ログイン)
 */
function is_login() {

	reutrn isset($_SESSION['id']) ? true : false;

}