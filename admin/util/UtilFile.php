<?php

/**
 * ファイルユーティリティ
 *
 * @author kanemiya
 *
 */

class UtilFile {

	/**
	 * ファイルをアップロードするする
	 * @param string $tmp_file_name 一時ファイル名
	 * @param string $file_name ファイル名
	 * @param string $file_dir アップロード先のディレクトリ
	 * @param string $parm パーミッション
	 * @return boolean 結果
	 */
	public static function upload_file($tmp_file_name, $file_name, $file_dir, $parm = 0644) {
		if (is_uploaded_file($tmp_file_name)) {
			if (move_uploaded_file($tmp_file_name,  $file_dir . '/' . $file_name)) {
				chmod( $file_dir . '/' . $file_name, $parm);
			}else{
				return false;
			}
		}else{
			return false;
		}
		return true;
	}

	/**
	 * ファイルを削除する
	 * @param string $file_name ファイル名
	 * @param string $file_dir 削除対象のディレクトリ
	 * @return boolean 結果
	 */
	public static function delete_file($file_name, $file_dir){
		if ($file_name && file_exists($file_dir . '/' . $file_name))
			return unlink($file_dir . '/' . $file_name);
		return false;
	}

	/**
	 * ファイルの存在有無をチェックする
	 * @param string $file_name ファイル名
	 * @param string $file_name チェック対象のディレクトリ
	 * @return boolean 結果
	 */
	public static function file_exists($file_name, $file_dir){
		return file_exists ($file_dir . '/' . $file_name);
	}

	/**
	 * ファイルの拡張子を取得する
	 * @param string $file_name ファイル名
	 * @return string 拡張子
	 */
	public static function get_extension($file_name){
		return end(explode('.', $file_name));
	}

	/**
	 * すでにファイルが読み込まれているかチェックする
	 *
	 *　@param string $filr チェックファイル
	 * @return boolean 結果
	 */
	public static function chk_include_file ($file_name) {

		$included_files = get_included_files();
		foreach ($included_files as $included_file) {
		    if ($file_name == $included_file)
		    	return true;
		}
		return false;
	}

	/**
	 * csvを取得する
	 *
	 */
	public static function get_csv ($file_name, $data, $header = null) {

		// ヘッダを付ける場合はarray(header1,header2)の形式で渡す
		if($header != null) {
		    array_unshift($data, $header);
		}

		// PHPのテンポラリに読み書きの準備をする
		$outputFile = 'php://temp';
		$fp = fopen($outputFile, 'r+');
		foreach($data as $v) {
		    //テンポラリにCSV形式で書き込む
		    fputcsv($fp,$v,',','"');
		}

		// ファイルポインタを一番先頭に戻す
		rewind($fp);
		
		// ファイルポインタの今の位置から全てを読み込み文字列へ代入
		$csv = stream_get_contents($fp);
		
		//SJISに変える
		$csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');

		// 改行バッファ
		$buf = str_replace("\n", "\r\n", stream_get_contents($fp));
		
		//ファイルポインタを閉じる
		fclose($fp);

		// 改行を追記
		$fp = fopen($outputFile, 'w');
		fwrite($fp, $buf);
		fclose($fp);
		
		//渡されたファイル名の拡張子やパスを切り落とす
		$file_name = basename($file_name);
		
		//ダウンロードヘッダ定義
		header('Content-Disposition:attachment; filename="' . $file_name . '.csv"');
		header('Content-Type:application/octet-stream');
		header('Content-Length:' . strlen($csv));
		echo $csv;
	}


	/**
	 * csv形式にパースする(fgetscsv拡張)	 
	 *
	 */
	public static function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"') {
		$eof = false;
		$d = preg_quote($d);
		$e = preg_quote($e);
		$_line = "";
		while (($eof != true)and(!feof($handle))) {
			$_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
			$itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
			if ($itemcnt % 2 == 0) $eof = true;
		}
		$_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
		$_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
		preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
		$_csv_data = $_csv_matches[1];
		for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
			$_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
			$_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
		}
		return empty($_line) ? false : $_csv_data;
	}

	/**
	 * blob画像表示用タグを表示する	 
	 *
	 */
	public static function src_blob_image ($blob) {

		if ($blob) {
			return 'data:images/jpeg;base64, ' . base64_encode($blob); 
		} else {
			return IMG_SRC . "/no_user.png";
		}

	}
	
}

?>