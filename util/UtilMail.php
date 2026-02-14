<?php

/**
 * メールユーティリティ
 *
 * @author kanemiya
 *
 */
class UtilMail {

	/**
	 * メールを送信する
	 * @param string $from 送信元メールアドレス
	 * @param string $to 送信先メールアドレス
	 * @param string $subject 件名
	 * @param string $body_tpl 本文テンプレートファイル名 ※拡張子は不要
	 * @param array $params 表示パラメータ
	 * @return boolean 結果(true:成功, false:失敗)
	 */
	public static function send($from, $to, $subject, $body_tpl, $params = null) {

		// 引数のいずれかがnullの場合はエラー
		if (is_null($from) || is_null($to) || is_null($subject) || is_null($body_tpl) || !is_null($params) && !is_array($params))
			return false;

		// メール定義
		mb_language("ja");
		mb_internal_encoding("UTF-8");

		$from = mb_encode_mimeheader(mb_convert_encoding(SITE_NAME, 'iso-2022-jp', 'utf-8')) . ' <' . $from . '>';

		$smarty = new Smarty();
		$smarty->compile_dir = TMP_DIR;
		
		if(count($params)) {
			foreach ($params as $column => $param) {
				$smarty->assign($column, $param);
			}
		}

		$tpl_name = VIEW_DIR . '/mail/text/' . $body_tpl . '.html';
		if (!file_exists($tpl_name))
			return false;

		$body = $smarty->fetch($tpl_name);
		
		// 送信送信
		$result = mb_send_mail($to, $subject, $body, "From:" . $from);
		//$result = mb_send_mail($to, $subject, $body, "From:" . $from, "-f " . $from);

		return $result;

	}

	/**
	 * HTMLメールを送信する
	 * @param string $from 送信元メールアドレス
	 * @param string $to 送信先メールアドレス
	 * @param string $subject 件名
	 * @param string $body_tpl 本文テンプレートファイル名 ※拡張子は不要
	 * @param array $params 表示パラメータ
	 * @return boolean 結果(true:成功, false:失敗)
	 */
	public static function send_html($from, $to, $subject, $body_tpl, $params = null) {

		// 引数のいずれかがnullの場合はエラー
		if (is_null($from) || is_null($to) || is_null($subject) || is_null($body_tpl) || !is_null($params) && !is_array($params))
			return false;

		// メール定義
		mb_language("ja");
		mb_internal_encoding("UTF-8");

		$crlf = "\r\n";

		//件名と差出人名をMIME形式へ変換
		$subject = mb_encode_mimeheader($subject, 'utf-8', 'B', $crlf);
		$from_name = mb_encode_mimeheader(mb_convert_encoding(SITE_NAME, 'iso-2022-jp', 'utf-8')) . ' <' . $from . '>' . $crlf;
		$boundary = "__BOUNDARY__" . uniqid(rand(), 1) . "__";

		// ヘッダー
		$header = "MIME-Version: 1.0" . $crlf;
		$header .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . $crlf;
		$header .= "From:{$from}<{$from_name}>" . $crlf;

		$smarty = new Smarty();
		$smarty->compile_dir = TMP_DIR;
		
		if(count($params)) {
			foreach ($params as $column => $param) {
				$smarty->assign($column, $param);
			}
		}

		$tpl_name = VIEW_DIR . '/mail/html/' . $body_tpl . '.html';
		if (!file_exists($tpl_name))
			return false;

		$mail_body = $smarty->fetch($tpl_name);
		$mail_body_text = 'メールソフトがHTMLメールに対応していない場合、このメッセージが表示されます。';

		// メール本文
		$message = '';
		$message .= '--' . $boundary . $crlf;
		$message .= "Content-Type: text/plain; charset=utf-8" . $crlf;
		$message .= "Content-Transfer-Encoding: base64" . $crlf;
		$message .= $crlf;
		$message .= chunk_split(base64_encode($mail_body_text), 76, $crlf);

		$message .= '--' . $boundary . $crlf;
		$message .= "Content-Type: text/html; charset=utf-8" . $crlf;
		$message .= "Content-Transfer-Encoding: base64" . $crlf;
		$message .= $crlf;
		$message .= chunk_split(base64_encode($mail_body), 76, $crlf);
		$message .= '--' . $boundary . $crlf;

		
		// 送信送信
		// $result = mb_send_mail($to, $subject, $body, "From:" . $from);
		//$result = mb_send_mail($to, $subject, $body, "From:" . $from, "-f " . $from);
		$result = mail($to, $subject, $message, $header, '-f' . RETURN_EMAIL);

		return $result;

	}

}

?>