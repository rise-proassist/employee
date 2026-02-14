<?php

/**
 * ログローテートバッチ
 * 
 * @author k.kanemiya
 */

const LOG_DIR = "../log/";
const TARGET_LOG = "../log/*.log*";

// ディレクトリ作成（実行日の2ヶ月前）
$dir_name = date('Ym', strtotime('-2 month'));
$backup_dir = LOG_DIR . $dir_name;
mkdir($backup_dir);

// 対象のログファイルをバックアップディレクトリへ移動
$log_files = glob(TARGET_LOG);
foreach ((array)$log_files as $log_file) {

	if (strpos($log_file, $dir_name)) {

		$backup_log_file = str_replace('app_', $dir_name . '/app_', $log_file);
		rename($log_file, $backup_log_file);

	}

}

// バックアップディレクトリごと圧縮
zip(LOG_DIR, $backup_dir);

// バックアップディレクトリを削除
rmdirAll($backup_dir);

/**
 * ディレクトリを圧縮する
 * 
 * @param string $path 圧縮先ディレクトリ
 * @param string $dir_name 圧縮ファイル名 
 */
function zip($path, $dir_name) {
	chdir($path);
	// exec("zip -r {$dir_name} .");
	exec("zip " . $dir_name . ".zip " . $dir_name);
}

/**
 * ディレクトリ内のファイルを一括削除する
 * 
 * @param string $dir 削除先ディレクトリ
 */
function rmdirAll($dir) {
	// 指定されたディレクトリ内の一覧を取得
	$res = glob($dir.'/*');

	// 一覧をループ
	foreach ($res as $f) {
		// is_file() を使ってファイルかどうかを判定
		if (is_file($f)) {
			// ファイルならそのまま出力
			unlink($f);
		} else {
			// ディレクトリの場合（ファイルでない場合）は再度rmdirAll()を実行
			rmdirAll($f);
		}
	}
	// 中身を削除した後、本体削除
	rmdir($dir);
}