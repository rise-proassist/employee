<?php

/**
 * 共通ユーティリティ
 *
 * @author kanemiya
 *
 */

class UtilCommon {
	
	public static function get_base_url($controller = null, $action = null) {

		$url = ADMIN_WEB_SRC;

		if ($controller)
			$url .= '/' . $controller;

		if ($action)
			$url .= '/' . $action;

		return $url . '/';
	}

	public static function get_web_base_url($controller = null, $action = null) {

		$url = WEB_SRC;

		if ($controller)
			$url .= '/' . $controller;

		if ($action)
			$url .= '/' . $action;

		return $url . '/';
	}

	/**
	 * URL付きのソートボタンを出力する
	 * 
	 * 
	 */
	public static function gen_sort_button($column = null, $sort_key = null, $controller = null, $action = null) {

		if (!$column || !$sort_key)
			return false;

		if (!$controller) {
			$request_uris = explode('/', $_SERVER['REQUEST_URI']);
			$controller = isset($request_uris[1]) ? $request_uris[1] : null;
		}

		if (!$action) {
			$request_uris = explode('/', $_SERVER['REQUEST_URI']);
			$action = isset($request_uris[2]) ? $request_uris[2] : null;
		}

		$url = sprintf("/%s/%s/?",$controller ,$action);
		if (isset($_SERVER['QUERY_STRING'])) {

			$query_strings = explode('&', $_SERVER['QUERY_STRING']);
			foreach ($query_strings as $key => $query_string) {

				if (false !== strpos($query_string, 'sort_key')){
					unset($query_strings[$key]);
					continue;
				}

				if (false !== strpos($query_string, 'sort_type')){
					unset($query_strings[$key]);
					continue;
				}

			}

		}
		$url .= implode('&', $query_strings);

		$icon_sort_asc = "△";
		if (isset($_GET['sort_key']) && $sort_key == $_GET['sort_key']) {

			if (isset($_GET['sort_type']) && PARAM_CONST_LIST_SORT_ASC == $_GET['sort_type'])
				$icon_sort_asc = "▲";
		
		}

		$icon_sort_desc = "▽";
		if (isset($_GET['sort_key']) && $sort_key == $_GET['sort_key']) {

			if (isset($_GET['sort_type']) && PARAM_CONST_LIST_SORT_DESC == $_GET['sort_type'])
				$icon_sort_desc = "▼";
		
		}

		// ヒアドキュメント内で定数を展開する
		$_ = function($const) { return $const; };

echo  <<<EOM
<div class="sort_box">
	<div class="header_title">{$column}</div>
	<div class="sort_asc"><a href="{$url}&sort_key={$sort_key}&sort_type={$_(PARAM_CONST_LIST_SORT_ASC)}">{$icon_sort_asc}</a></div>
	<div class="sort_desc"><a href="{$url}&sort_key={$sort_key}&sort_type={$_(PARAM_CONST_LIST_SORT_DESC)}">{$icon_sort_desc}</a></div>
</div>
EOM;

	}
	
	/**
	 * 空文字チェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:空文字, false:空文字でない)
	 */
	public static function is_empty($str) {
		if(!strlen($str) || is_null($str) || empty($str))
			return true;
		return false;
	}
	
	/**
	 * 数字チェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:数字のみ, false:数字以外が含む)
	 */
	public static function is_num($str) {

		if (!$str)
			return true;

		return preg_match("/^[0-9]+$/", $str);
	}
	
	/**
	 * 英数字チェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:英数字のみ, false:英数字以外が含む)
	 */
	public static function is_alnum($str) {

		if (!$str)
			return true;

		return preg_match("/^[a-zA-Z0-9]+$/", $str);
	}
	
	/**
	 * 英数字記号チェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:英数字記号のみ, false:英数字記号以外が含む)
	 */
	public static function is_alnum_sgn($str) {

		if (!$str)
			return true;

		return preg_match("/^[!-~]+$/", $str);
	}
	
	/**
	 * 文字数チェック
	 * @param String $str チェック文字列
	 * @param int $len チェック文字数
	 * @return boolean チェック結果(true:文字数内, false:文字数オーバー)
	 */
	public static function is_length($str, $len) {
		if($len >= mb_strlen($str))
			return true;
		return false;
	}

	/**
	 * 日付書式チェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:正常書式, false:異常書式)
	 */
	public static function is_date($str) {

		if (!$str)
			return true;

		if( strptime( $str, '%Y-%m-%d' ) ){
 			return true;
		}
		return false;
	}

	/**
	 * メールアドレス書式チェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:正常書式, false:異常書式)
	 */
	public static function is_mail($str) {

		if (!$str)
			return true;

		if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
			return true;
		}
		return false;
	}

	/**
	 * メールアドレス電話番号チェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:正常書式, false:異常書式)
	 */
	public static function is_tel($str) {

		if (!$str)
			return true;

		if (preg_match("/^[0-9\-]+$/", $str)) {
			return true;
		}
		return false;
	}

	/**
	 * カタカナチェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:カタカナのみ, false:カタカナ以外が含む)
	 */
	public static function is_kata($str) {

		if (!$str)
			return true;

		return preg_match("/^[ァ-ヶー]+$/u", $str);
	}

	/**
	 * ひらがな&カタカナ＆アルファベットチェック
	 * @param String $str チェック文字列
	 * @return boolean チェック結果(true:ひらがな or カタカナ or アルファベットのみ, false:ひらがな or カタカナ or アルファベット以外が含む)
	 */
	public static function is_hira_kata_alpha($str) {

		if (!$str)
			return true;

		return preg_match("/^[a-zA-Zぁ-ゞァ-ヶー. 　]+$/u", $str);
	}

	/**
	 *
	 * 入力された文字列が上限文字数に合うようにカットする
	 * @param string $str 確認する文字列
	 * @param string $limit 上限文字数
	 * @param string $encoding 文字エンコード
	 */
	public static function str_short_cut($str, $limit, $encoding = "utf-8") {

		$resStr = $str;
		if(mb_strlen($str, $encoding) > $limit){
			$resStr = mb_strcut($str, 0, $limit)."…";
		}
		return $resStr;
	}

	/**
	 * brタグを改行コードに変換する
	 * @param string $str 変換前の文字列
	 * @return string 変換後の文字列
	 */
	public static function br2nl($str) {
		// 大文字・小文字を区別しない
		return preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", $str);
	}

	/**
	 * 年齢を算出する
	 * @param string $year 年
	 * @param string $month 月
	 * @param string $day 日
	 * @return int $age 年齢
	 */
	public static function get_age($year, $month, $day) {

		if (!$year || !$month || !$day)
			return false;

		if ('0000' == $year || '00' == $month || '00' == $day)
			return false;
						
		$ymd = sprintf('%04d', $year) . sprintf('%02d', $month) . '01';
		$age = (int) ((int)(date('Ymd') - (int)$ymd) / 10000);
		return $age;
	}

	/**
	 * 誕生日の開始日を算出する
	 * @param int $age 年齢
	 * @return date 誕生日（開始日）
	 */
	public static function get_birth_day_from($age) {

		if (!$age)
			return false;

		return date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y') - $age - 1));

	}

	/**
	 * 誕生日の終了日を算出する
	 * @param int $age 年齢
	 * @return date 誕生日（終了日）
	 */
	public static function get_birth_day_to($age) {

		if (!$age)
			return false;

		return date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y') - $age));

	}

	/**
	 * 曜日を取得する
	 * @param string $date 日付(yyyy-mm-dd形式)
	 * @return string 曜日
	 */
	public static function get_yb ($date = null) {
		$datetime = new DateTime($date);
		$week = array("日", "月", "火", "水", "木", "金", "土");
		$w = (int)$datetime->format('w');
		return $week[$w];
	}

	/**
	 * スネーク式からキャメル式に変換
	 * @param string $str
	 */
	public static function to_camelize($str) {
		$str = ucwords($str, '_');
		return str_replace('_', '', $str);
	}

	/**
	 * キャメル式からスネーク式に変換
	 * @param string $str
	 */
	public static function to_snakize($str) {
		$str = preg_replace('/[a-z]+(?=[A-Z])|[A-Z]+(?=[A-Z][a-z])/', '\0_', $str);
		return strtolower($str);
	}

	/**
	 * URL用検索パラメータを生成する 
	 * 
	 * @param array $search_params 検索パラメータ
	 */
	public static function gen_search_param($search_params = null) {

		if (!$search_params)
			return null;

		$search_param = null;
		foreach ((array)$search_params as $column => $param) {
			$search_param .= sprintf("%s=%s&", $column, $param);
		}

		return $search_param;

	}

	/**
	 * X秒前、X分前、X時間前、X日前などといった表示に変換する。
	 * 一分未満は秒、一時間未満は分、一日未満は時間、
	 * 31日以内はX日前、それ以上はX月X日と返す。
	 * X月X日表記の時、年が異なる場合はyyyy年m月d日と、年も表示する
	 *
	 * @param   <String> $time_db       strtotime()で変換できる時間文字列 (例：yyyy/mm/dd H:i:s)
	 * @return  <String>                X日前,などといった文字列
	 **/
	public static function convert_to_fuzzy_time($time_db) {

		$unix = strtotime($time_db);
		$now = time();
		$diff_sec = $now - $unix;

		if ($diff_sec < 0) {
			
			$time = "数";
			$unit = "秒前";			

		} elseif ($diff_sec < 60) {
			
			$time = $diff_sec;
			$unit = "秒前";

		} elseif ($diff_sec < 3600) {

			$time = $diff_sec/60;
			$unit = "分前";
		
		} elseif ($diff_sec < 86400) {

			$time = $diff_sec/3600;
			$unit = "時間前";
		
		} elseif ($diff_sec < 2764800) {

			$time = $diff_sec/86400;
			$unit = "日前";
		
		} else {

		    if (date("Y") != date("Y", $unix)) {
			
				$time = date("Y年n月j日", $unix);
		    
		    } else {
			
				$time = date("n月j日", $unix);
		    
		    }

		    return $time;
		}

		return (int)$time . $unit;
	}

	/**
	 * パスワードを暗号化する
	 * @param string $password パスワード
	 * @return string 暗号化後のパスワード
	 */
	public static function to_hash_password ($password) {

		return password_hash($password, PASSWORD_DEFAULT);

	}

	/**
	 * 日付から年を取り出す
	 * 
	 * @param string $date 日付（yyyy-mm-dd形式）
	 * @return 年（4桁）
	 * 
	 */
	public static function date_to_year($date) {

		if ('0000-00-00' == $date)
			return false;

		return date('Y', strtotime($date));
	
	}

	/**
	 * 日付から月を取り出す
	 * 
	 * @param string $date 日付（yyyy-mm-dd形式）
	 * @return 月（2桁）
	 * 
	 */
	public static function date_to_month($date) {

		if ('0000-00-00' == $date)
			return false;

		return date('m', strtotime($date));
	
	}

	/**
	 * 日付から日を取り出す
	 * 
	 * @param string $date 日付（yyyy-mm-dd形式）
	 * @return 日（2桁）
	 * 
	 */
	public static function date_to_day($date) {

		if ('0000-00-00' == $date)
			return false;

		return date('d', strtotime($date));
	
	}

	/**
	 * 日時から時間を取り出す
	 * 
	 * @param string $date 日付（yyyy-mm-dd hh:ii:mm形式）
	 * @return 日（2桁）
	 * 
	 */
	public static function date_to_time($date) {

		return date('G:i', strtotime($date));
	
	}

	/**
	 * ユーザコードと画像名から、プロフィール画像のsrcを取得する
	 * 
	 * @param string $code ユーザコード
	 * @param string $profile_image_file 画像ファイル名
	 * @return プロフィール画像src
	 * 
	 */
	public static function get_profile_image_src($code, $profile_image_file) {

		if (!$code || !$profile_image_file)
			return RELATIVE_SRC . "/image/icon_user.png";

		return sprintf("%s/%s/%s", RELATIVE_ASSETS_USER_SRC, $code, $profile_image_file);

	}

	/**
	 * 指定文字以降を...に丸める
	 * 
	 */
	public static function to_strimwidth($str = null, $limit = 10) {

		if (!$str)
			return $str;

		return mb_strimwidth($str, 0, $limit, '…', 'utf8');
	}

	/**
	 * ランダム文字列を生成する
	 * 
	 * @param int $length 求める文字列の長さ（桁数）
	 * @param string $chars ランダム文字列に使用したい文字一覧
	 * @return string ランダム文字列
	 */
	public static function random_str($length, $chars) {

		$retstr = '';
		$data = openssl_random_pseudo_bytes($length);
		$num_chars = strlen($chars);
		for ($i = 0; $i < $length; $i++) {
			$retstr .= substr($chars, ord(substr($data, $i, 1)) % $num_chars, 1);
		}

		return $retstr;

	}

	/**
	 * ユーザコードを生成する（U + ランダム文字列（4文字） + 通番（6桁 ※0埋め））
	 * 
	 * @param string $user_id ユーザID
	 * @return ユーザコード
	 * 
	 */
	public static function gererate_user_code($user_id) {

		return 'UT' . self::random_str(3, '0123456789') . sprintf('%06d', $user_id);

	}

	/**
	 * 稼働時間を計算する
	 * 
	 * @param datetime $date_from 日時（開始）
	 * @param datetime $date_to 日時（終了）
	 * @return 稼働時間（例 : 8.0）
	 */
	public static function calc_work_time($date_from = null, $date_to = null) {

		if (!$date_from || !$date_to)
			return 0;

		if ('0000-00-00 00:00:00' == $date_from || '0000-00-00 00:00:00' == $date_to)
			return 0;

		if (strtotime($date_to) < strtotime($date_from))
			return 0;			

		return round((strtotime($date_to) - strtotime($date_from)) / 3600, 2); 

	}

	/**
	 * 指定した言語種別の性別名を返却する
	 * 
	 * @param int $lang_type 名称取得する言語種別
	 * @param int $type 名称の言語（日本語/英語）
	 * @return 雇用形態名
	 */
	public static function get_gender_name($gender_type, $lang_type = PARAM_CONST_LANG_TYPE_JP) {

		switch ($lang_type) {
			case PARAM_CONST_LANG_TYPE_EN:
				$gender_type_name = PARAM_CONST_EN_GENDER_TYPES[$gender_type];
				break;
			case PARAM_CONST_LANG_TYPE_JP:
			default:
				$gender_type_name = PARAM_CONST_GENDER_TYPES[$gender_type];
				break;
		}

		return $gender_type_name;

	}

	/**
	 * 指定した言語種別の言語名を返却する
	 * 
	 * @param int $lang_type 名称取得する言語種別
	 * @param int $type 名称の言語（日本語/英語）
	 * @return 言語名
	 */
	public static function get_lang_name($lang_type, $type = PARAM_CONST_LANG_TYPE_JP) {

		// 言語種別
		switch ($type) {
			case PARAM_CONST_LANG_TYPE_JP:
				$lang_type_name = PARAM_CONST_LANG_TYPES[$lang_type];
				break;
			
			case PARAM_CONST_LANG_TYPE_EN:
				$lang_type_name = PARAM_CONST_EN_LANG_TYPES[$lang_type];
				break;
			
			default:
				// $lang_type_name = PARAM_CONST_LANG_TYPES[PARAM_CONST_LANG_TYPE_JP];
				break;
		}

		return $lang_type_name;

	}

	/**
	 * 指定した言語種別の雇用形態名を返却する
	 * 
	 * @param int $lang_type 名称取得する言語種別
	 * @param int $type 名称の言語（日本語/英語）
	 * @return 雇用形態名
	 */
	public static function get_employ_name($employ_type, $lang_type = PARAM_CONST_LANG_TYPE_JP) {

		switch ($lang_type) {
			case PARAM_CONST_LANG_TYPE_EN:
				$lang_type_name = PARAM_CONST_EN_EMPLOY_TYPES[$employ_type];
				break;
			case PARAM_CONST_LANG_TYPE_JP:
			default:
				$lang_type_name = PARAM_CONST_EMPLOY_TYPES[$employ_type];
				break;
		}

		return $lang_type_name;

	}


}