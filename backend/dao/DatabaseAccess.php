<?php

/**
 * DBアクセッサ(基幹クラス)
 *
 * @author kanemiya
 *
 */

class DatabaseAccess {
	
	public static $_instance = null;
	
	// public static $_con = null;
	
	public static $_pdo = null;
	
	/**
	 * コンストラクタ ※未使用
	 *
	 */
	// public function __construct() {
		
	// }
	
	/**
	 * DBインスタンス取得
	 *
	 */
	public static function get_instance() {
		if(is_null(self::$_pdo)) {
			self::$_instance = new self();
			self::$_instance->connection();
		}
	
		return self::$_pdo;
	}
	
	/**
	 * DBコネクション
	 *
	 */
	public function connection() {
	
		$host = DBConst::$db_setting['db_host'];
		$db_name = DBConst::$db_setting['db_name'];
		$user = DBConst::$db_setting['db_user'];
		$passwd = DBConst::$db_setting['db_pass'];
		$port = DBConst::$db_setting['db_port'];
		// $ver = DBConst::$db_setting['db_ver'];

		try {

			$charset = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8mb4");
			$connect_str = "mysql:host=" . $host . ";dbname=". $db_name .';port='. $port . ';';
			$pdo = new PDO($connect_str, $user, $passwd, $charset);
			//PDO::ATTR_PERSISTENT => true コネクションプール使用
			$pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
		
			//MySQL ドライバはバッファ版の MySQL API を使用します
			$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY , false);
		
			//PDO::ATTR_EMULATE_PREPARES => PDOのプリペアドステートメントを普通に使うだけで、PDO内部で正しくエスケープされたSQLを構築してMySQLに投げてくれる
			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
			//TODO SQL文にエラーがあった場合などにexecute失敗でExceptionがThrowされてBacktraceが見られる
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
			//PDOオブジェクト自体に指定。レスポンスは常に連想配列形式で取得するようになる
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		
			//この値が FALSE の場合、PDO は接続がトランザクションを開始できるように オートコミットを無効にしようとします。
			$pdo->setAttribute(PDO::ATTR_AUTOCOMMIT , true);

			self::$_pdo = new FluentPDO($pdo);
				
		} catch (Exception $e) {

			return $e->getMessage();
			
		}

		return 0;

	}
	
	/**
	 * DBディスコネクション ※自動接続のため未使用
	 *
	 */
	// public function disconnection() {
		
	// }
	
	/**
	 * DBトランザクション開始
	 *
	 */
	public function begin() {
		return self::$_pdo->beginTransaction();
	}
	
	/**
	 * DBトランザクションコミット
	 *
	 */
	public function commit() {
		return self::$_pdo->commit();
	}
	
	/**
	 * DBトランザクションロールバック
	 *
	 */
	public function rollback() {
		return self::$_pdo->rollBack();
	}
	
	/**
	 * DBクエリ(抽出)
	 *
	 */
	public function select($sql) {
		return self::$_pdo->query($sql);
	}
	
	/**
	 * バインドによる検索クエリの実行
	 * @param String $sql SQLクエリ
	 * @param Array $params バインドパラメータ
	 * @param Array $types バインドパラメータ型
	 * @return Array $entrys 検索結果
	 */
	public function select_bind_param($sql, $params, $types = null) {
		
		// プリペアステイトメント作成
		$pstmt = self::$_pdo->prepare($sql);
		
		$i = 0;
		foreach($params as $key => $val) {
			$type = PDO::PARAM_STR;
			if(!is_null($types)) {
				$type = $types[$i];
			}
			$pstmt->bindValue($key, $val, $type);
			$i++;
		}

		// クエリ実行
		$pstmt->execute();
		
		$entrys = array();
		while($row = $pstmt->fetch()) {
			$entrys[] = $row;
		}
		return $entrys;
	}
	
	/**
	 * 渡されたSQLクエリを実行する
	 * ※insert, update, delete用
	 * @param string $sql SQLクエリ
	 * @param array $params パラメータ
	 * @param string $types パラメータ型
	 * @return boolean 処理結果
	 */
	public function execute_query($sql, $params = null, $types = null) {
		
		// プリペアステイトメント作成
		$pstmt = self::$_pdo->prepare($sql);
	
		$i = 0;
		if ($params && is_array($params)) {
			foreach($params as $key => $val) {
				$type = PDO::PARAM_STR;
				if(!is_null($types)) {
					$type = $types[$i];
				}
				$pstmt->bindValue($key, $val, $type);
				$i++;
			}
		}
		
		// クエリ実行
		return $pstmt->execute();
		
	}

	/**
	 * 渡されたSQLクエリを実行し、insertIdされたIDを返却する
	 * ※insert, update, delete用
	 * @param string $sql SQLクエリ
	 * @param array $params パラメータ
	 * @param string $types パラメータ型
	 * @return boolean 処理結果
	 */
	public function execute_query_to_id($sql, $params, $types = null) {
		
		// プリペアステイトメント作成
		$pstmt = self::$_pdo->prepare($sql);
	
		$i = 0;
		foreach($params as $key => $val) {
			$type = PDO::PARAM_STR;
			if(!is_null($types)) {
				$type = $types[$i];
			}
			$pstmt->bindValue($key, $val, $type);
			$i++;
		}
		
		// クエリ実行
		if (!$pstmt->execute()) {
			return false;
		}else{
			return self::$_pdo->lastInsertId();
		}
		
	}
}