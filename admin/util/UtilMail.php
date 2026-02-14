<?php

/**
 * メールユーティリティ
 *
 * @author kanemiya
 *
 */
class UtilMail {



	/**
	 * システムログ書込み
	 * @param string $from 送信元メールアドレス
	 * @param string $to 送信先メールアドレス
	 * @param string $subject 件名
	 * @param string $body_tpl 本文テンプレートファイル名 ※拡張子は不要
	 * @param array $params 表示パラメータ
	 * @return boolean 結果(true:成功, false:失敗)
	 */
	public static function send($from, $to, $subject, $body_tpl, $params = array()) {

		// 引数のいずれかがnullの場合はエラー
		if (is_null($from) || is_null($to) || is_null($subject) || is_null($body_tpl) || !is_null($params) && !is_array($params))
			return false;

		// メール定義
		mb_language("ja");
		mb_internal_encoding("UTF-8");

		$from = mb_encode_mimeheader(mb_convert_encoding(SITE_NAME, 'iso-2022-jp', 'utf-8')) . ' <' . $from . '>' . "\n";
		// $from .= 'List-Unsubscribe: <https://gtuned.net/mail/cancel>' . "\n";
		// $from .= 'List-Unsubscribe-Post: List-Unsubscribe=One-Click' . "\n";

		$smarty = new Smarty();
		$smarty->compile_dir = ADMIN_TMP_DIR;
		
		if(count($params)) {
			foreach ($params as $column => $param) {
				$smarty->assign($column, $param);
			}
		}

		$tpl_name = ADMIN_VIEW_DIR . '/mail/' . $body_tpl . '.html';
		if (!file_exists($tpl_name))
			return false;

		$body = $smarty->fetch($tpl_name);

		// 送信送信
		$result = mb_send_mail($to, $subject, $body, "From:" . $from);
		//$result = mb_send_mail($to, $subject, $body, "From:" . $from, "-f " . $from);

		return $result;

	}

}

?>