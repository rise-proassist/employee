<?php

/**
 * MODEL メール
 * @author kanemiya
 *
 */

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// use PHPMailer\PHPMailer\SMTP;

class ModelMail {

	private $_mail = null;

	private $_from = null;

	private $_address = null;

	private $_subject = null;

	private $_body = null;

	/**
	 * コンストラクタ
	 * 
	 */
	function __construct() {

		// 文字エンコードを指定
		mb_language('uni');
		mb_internal_encoding('UTF-8');

		// インスタンスを生成（true指定で例外を有効化）
		$mail = new PHPMailer(true);

		// 文字エンコードを指定
		$mail->CharSet = PHPMailer::CHARSET_UTF8;

		// SMTPサーバの設定
		$mail->isSMTP();                         	// SMTPの使用宣言
		$mail->SMTPAuth   = true;                 	// SMTP authenticationを有効化
		
		$mail->Host = SMTP_HOST;					// SMTPサーバーを指定
		$mail->Username = SMTP_USER_NAME;			// SMTPサーバーのユーザ名
		$mail->Password = SMTP_PASSWORD;			// SMTPサーバーのパスワード
		$mail->Sender = SMTP_SENDER_EMAIL;			// Return-path
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // tls
		$mail->SMTPAutoTLS = 'tls';
		$mail->Port = 587;									// TCPポートを指定（tlsの場合は465や587）

		// $mail->Host = 'sv1061.xserver.jp';				// SMTPサーバーを指定
		// $mail->Username = 'kanri@proassist-east.com';	// SMTPサーバーのユーザ名
		// $mail->Password = 'boueki0410';           		// SMTPサーバーのパスワード
		// $mail->Sender = 'kanri@proassist-east.com';		// Return-path
		// $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;	// ssl
		// $mail->Port = 465; // TCPポートを指定（tlsの場合は465や587）
		
		// $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // tls
		
		// $mail->SMTPSecure = false;    // 無効
		// $mail->SMTPAutoTLS = false;


		$mail->SMTPOptions = array(
		    'ssl' => array(
		    'verify_peer' => false,
		    'verify_peer_name' => false,
		    'allow_self_signed' => true
		));
		// $mail->setFrom('k.kanemiya@gtuned.net', '差出人名');		// 送信者
		// $mail->addAddress('k.kanemiya@gtuned.net', '受信者名');   // 宛先
		// $mail->addReplyTo('replay@gtuned.net', 'お問い合わせ');	// 返信先
		// $mail->addCC('cc@example.com', '受信者名');				// CC宛先

		$this->_mail = $mail;		 

	}

	/**
	 * セッタ（送信者）
	 * 
	 * @param string $address 送信先 
	 */
	public function set_from($from = null) {

		// $this->_mail->setFrom($from);
		$this->_mail->setFrom($from, mb_encode_mimeheader(SITE_NAME));

	}

	/**
	 * セッタ（返信先）
	 * 
	 * @param string $replyto 返信先
	 */
	public function set_replyto($replyto = null) {

		// $this->_mail->addReplyTo($replyto);
		$this->_mail->addReplyTo($replyto, mb_encode_mimeheader(SITE_NAME)); // 返信先

	}

	/**
	 * セッタ（送信先）
	 * 
	 * @param string $address 送信先 
	 */
	public function set_address($address = array()) {

		$this->_mail->addAddress($address);
		// $this->_mail->addAddress($address, mb_encode_mimeheader('受信者名'));
		
	}

	/**
	 * セッタ（件名）
	 * 
	 * @param string $address 件名 
	 */
	public function set_subject($subject = null) {

		$this->_mail->Subject = $subject;

	}

	/**
	 * 本文生成
	 * 
	 * @param string $body_tpl メールテンプレート名
	 * @param string $params パラメータ配列 
	 */
	public function create_body($body_tpl = null, $params = array()) {

		$smarty = new Smarty();
		$smarty->compile_dir = ROOT_DIR . '/tmp/';
		if(count($params)) {
			foreach ($params as $column => $param) {
				$smarty->assign($column, $param);
			}
		}

		$tpl_name = VIEW_DIR . '/mail/text/' . $body_tpl . '.html';
		if (!file_exists($tpl_name))
			return false;

		$this->_mail->Body = $smarty->fetch($tpl_name);

	}

	/**
	 * メール送信
	 * 
	 */
	public function send() {

		$this->_mail->send();

	}
}

?>
