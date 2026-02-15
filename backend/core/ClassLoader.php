<?php

/**
* Classが定義されていない場合に、ファイルを探すクラス
*/
class ClassLoader
{
	// class ファイルがあるディレクトリのリスト
	private static $dirs;

	/**
	* クラスが見つからなかった場合呼び出されるメソッド
	* spl_autoload_register でこのメソッドを登録してください
	* @param  string $class 名前空間など含んだクラス名
	* @return bool 成功すればtrue
	*/
	public static function loadClass($class)
	{

		foreach (self::directories() as $directory) {

			// 名前空間や疑似名前空間をここでパースして
			// 適切なファイルパスにしてください
			$file_name = "{$directory}/{$class}.php";
			if (is_file($file_name)) {
				
				require $file_name;
				return true;

			}
		}
	}

	/**
	* ディレクトリリスト
	* @return array フルパスのリスト
	*/
	private static function directories()
	{

		if (empty(self::$dirs)) {
			// $base = '..';
			self::$dirs = array(
				// ここに読み込んでほしいディレクトリを足していきます
				CONTROLLER_DIR,
				DAO_DIR,
				ENTITY_DIR,
				DTO_DIR,
				MODEL_DIR,
				UTIL_DIR,
				LIB_DIR . '/barcode/class',
				LIB_DIR . '/log4php',
				LIB_DIR . '/fluentpdo/',
				LIB_DIR . '/smarty/',
				LIB_DIR . '/phpexcel/',
				LIB_DIR . '/pager/',
				LIB_DIR . '/ginq/',
				LIB_DIR . '/predis/',
				LIB_DIR . '/mpdf60',
				LIB_DIR . '/TCPDF',
				LIB_DIR . '/qrlib',
				LIB_DIR . '/payjp',
				LIB_DIR . '/PHPMailer',
				CORE_DIR . '/classes',
			);
		}

		return self::$dirs;
	}
}

// これを実行しないとオートローダーとして動かない
spl_autoload_register(array('ClassLoader', 'loadClass'));