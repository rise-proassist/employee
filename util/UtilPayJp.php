<?php

/**
 * PAY.JPユーティリティ
 *
 * @author kanemiya
 *
 */

// use Payjp\Payjp;
require(dirname(__FILE__) . '/../lib/payjp/Payjp.php');

// Utilities
require(dirname(__FILE__) . '/../lib/payjp/Util/RequestOptions.php');
require(dirname(__FILE__) . '/../lib/payjp/Util/Set.php');
require(dirname(__FILE__) . '/../lib/payjp/Util/Util.php');

// HttpClient
require(dirname(__FILE__) . '/../lib/payjp/HttpClient/ClientInterface.php');
require(dirname(__FILE__) . '/../lib/payjp/HttpClient/CurlClient.php');

// Errors
require(dirname(__FILE__) . '/../lib/payjp/Error/Base.php');
require(dirname(__FILE__) . '/../lib/payjp/Error/Api.php');
require(dirname(__FILE__) . '/../lib/payjp/Error/ApiConnection.php');
require(dirname(__FILE__) . '/../lib/payjp/Error/Authentication.php');
require(dirname(__FILE__) . '/../lib/payjp/Error/Card.php');
require(dirname(__FILE__) . '/../lib/payjp/Error/InvalidRequest.php');
require(dirname(__FILE__) . '/../lib/payjp/Error/RateLimit.php');

// Plumbing
require(dirname(__FILE__) . '/../lib/payjp/PayjpObject.php');
require(dirname(__FILE__) . '/../lib/payjp/ApiRequestor.php');
require(dirname(__FILE__) . '/../lib/payjp/ApiResource.php');
require(dirname(__FILE__) . '/../lib/payjp/AttachedObject.php');
require(dirname(__FILE__) . '/../lib/payjp/ExternalAccount.php');

// Payjp API Resources
require(dirname(__FILE__) . '/../lib/payjp/Account.php');
require(dirname(__FILE__) . '/../lib/payjp/Card.php');
require(dirname(__FILE__) . '/../lib/payjp/Charge.php');
require(dirname(__FILE__) . '/../lib/payjp/Collection.php');
require(dirname(__FILE__) . '/../lib/payjp/Customer.php');
require(dirname(__FILE__) . '/../lib/payjp/Event.php');
require(dirname(__FILE__) . '/../lib/payjp/Plan.php');
require(dirname(__FILE__) . '/../lib/payjp/Subscription.php');
require(dirname(__FILE__) . '/../lib/payjp/Token.php');
require(dirname(__FILE__) . '/../lib/payjp/Transfer.php');

class UtilPayJp {

	/**
	 * トークンで決済する
	 * @param string $token トークン
	 * @param int $amount 決済金額
	 * @param string $description 決済事由
	 * @param bool $capture 支払い処理を確定するかどうか
	 * @param string $cuurrency 通貨
	 * @return boolean 結果
	 */
	public static function charge_create($token, $amount, $description, $capture = true, $currency = 'jpy') {

		$status_code = 0;
		$code = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$created = Payjp\Charge::create(
				array(
					"amount" => $amount,
					"currency" => $currency,
					"card" => $token,
					"description" => $description,
					"capture" => $capture,
				)
			);
			$code = $created->id;
			$detail = $created;

		} catch (Exception $e) {

			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$detail = $err['message'];

		}

		$results['status'] = $status_code;
		$results['code'] = $code;
		$results['detail'] = $detail;
		return $results;
	}

	/**
	 * 払い戻す
	 * @param int $payment_code 決済コード
	 * @return boolean 結果
	 */
	public static function charge_refund($payment_code) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			Payjp\Charge::retrieve($payment_code)->refund();

		} catch (Exception $e) {
			
			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = (is_null($status_code)) ? 1 : $status_code;
		$results['code'] = $result;
		
		return $results;
	}

	/**
	 * トークンで顧客情報を作成
	 * @param string $token トークン
	 * @return boolean 結果
	 */
	public static function customer_create($token) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$customer = Payjp\Customer::create(
				array(
					"card" => $token,
				)
			);
			$result = $customer->id;

		} catch (Exception $e) {

			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}

		$results['status'] = $status_code;
		$results['code'] = $result;
		
		return $results;
	}

	/**
	 * 顧客カード情報の取得
	 * @param string $cus_code 顧客コード
	 * @param string $car_code カードコード
	 * @return boolean 結果(オブジェクト)
	 *	{
		  "address_city": null,
		  "address_line1": null,
		  "address_line2": null,
		  "address_state": null,
		  "address_zip": null,
		  "address_zip_check": "unchecked",
		  "brand": "Visa",
		  "country": null,
		  "created": 1433127983,
		  "customer": null,
		  "cvc_check": "unchecked",
		  "exp_month": 2,
		  "exp_year": 2020,
		  "fingerprint": "e1d8225886e3a7211127df751c86787f",
		  "id": "car_f7d9fa98594dc7c2e42bfcd641ff",
		  "last4": "4242",
		  "livemode": false,
		  "metadata": null,
		  "name": null,
		  "object": "card"
		}
	 */
	public static function customer_card_info($cus_code, $car_code) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$cu = Payjp\Customer::retrieve($cus_code);
			$result = $cu->cards->retrieve($car_code);

		} catch (Exception $e) {

			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = $status_code;
		$results['code'] = $result;
		return $results;
	}

	/**
	 * 顧客情報の更新
	 * @param string $cus_code 顧客コード
	 * @param string $token トークン
	 * @return boolean 結果
	 */
	public static function customer_save($cus_code, $token) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			// クレジットカード情報を直接パラメータに設定する方法は廃止
			// $card = null;
			// if ($number)
			// 	$card['number'] = $number;
			// if ($month)
			// 	$card['exp_month'] = (integer)$month;
			// if ($year)
			// 	$card['exp_year'] = (integer)$year;
			// if ($cvc)
			// 	$card['cvc'] = (integer)$cvc;
			// if ($name)
			// 	$card['name'] = $name;

			$cu = Payjp\Customer::retrieve($cus_code);
			// $cu->default_card = $car_code;
			$cu->card = $token;
			$cu->save();

		} catch (Exception $e) {
			
			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}

		$results['status'] = (is_null($status_code)) ? 0 : $status_code;
		$results['code'] = $result;

		return $results;
	}

	/**
	 * 顧客のカードリストを取得
	 * @param string $cus_code 顧客コード
	 * @return boolean 結果
	 */
	public static function customer_cards($cus_code) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$result = Payjp\Customer::retrieve($cus_code)
						->cards
						->all();

		} catch (Exception $e) {

			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = $status_code;
		$results['code'] = $result;
		return $results;
	}

	/**
	 * 顧客情報の削除
	 * @param string $cus_code 顧客コード
	 * @return boolean 結果
	 */
	public static function customer_card_code_delete($cus_code, $car_code) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$card = Payjp\Customer::retrieve($cus_code)
					->cards
					->retrieve($car_code)
					->delete();

		} catch (Exception $e) {

			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = $status_code;
		$results['code'] = $result;
		return $results;
	}

	/**
	 * 定額決済
	 * @param string $cus_code 顧客コード
	 * @param string $pla_code プランコード
	 * @return boolean 結果
	 */
	public static function subscription_create($cus_code, $pla_code) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$fixed = Payjp\Subscription::create(
				array(
					"customer" => $cus_code,
					"plan" => $pla_code
				)
			);

			$result = $fixed->id;

		} catch (Exception $e) {

			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}

		$results['status'] = $status_code;
		$results['code'] = $result;

		return $results;

	}

	/**
	 * 定額決済を停止する（一時停止）
	 * @param string $sub_code 定額課金コード
	 * @return boolean 結果
	 */
	public static function subscription_pause($sub_code) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			Payjp\Subscription::retrieve($sub_code)->pause();

		} catch (Exception $e) {
			
			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = (is_null($status_code)) ? 0 : $status_code;
		$results['code'] = $result;
		
		return $results;	
	}

	/**
	 * 定額決済を終了する（最終周期の決済を持って終了となる）
	 * @param string $sub_code 定額課金コード
	 * @return boolean 結果
	 */
	public static function subscription_cancel($sub_code) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			Payjp\Subscription::retrieve($sub_code)->cancel();

		} catch (Exception $e) {
			
			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = (is_null($status_code)) ? 0 : $status_code;
		$results['code'] = $result;
		
		return $results;	
	}

	/**
	 * トークン情報を取得
	 * @param string $token トークン
	 * @return boolean 結果
	 */
	public static function get_token_info($token) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$result = Payjp\Token::retrieve($token);

		} catch (Exception $e) {
			
			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = (is_null($status_code)) ? 0 : $status_code;
		$results['code'] = $result;
		
		return $results;	
	}

	/**
	 * プラン情報を取得
	 * @param string $plan_id プランID
	 * @return boolean 結果
	 */
	public static function get_plan_info($plan_id) {

		$status_code = 0;
		$result = 0;

		Payjp\Payjp::setApiKey(PAYJP_SECRET_KEY);

		try {

			$result = Payjp\Plan::retrieve($plan_id);

		} catch (Exception $e) {
			
			$json_body = $e->getJsonBody();
			$err = $json_body['error'];
			$status_code = $err['code'];
			$result = $err['message'];

		}
		$results['status'] = (is_null($status_code)) ? 0 : $status_code;
		$results['code'] = $result;
		
		return $results;	
	}

}


?>