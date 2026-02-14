<?php

/**
 * MODEL システムパラメータ
 *
 * @author kanemiya
 *
 */

class ModelWithholdTax {

	private $_tax_amount = null;

	function __construct($amount = null) {

		if (!$amount)
			return false;

		$dao_withhold_tax = new DaoWithholdTax();
		$withhold_tax = $dao_withhold_tax->select_where_one_for_amount(
			array(
				'amount_range_from' => $amount,
				'amount_range_to' => $amount
			)
		);

		if (!$withhold_tax)
			return false;

		switch ($withhold_tax->calc_type) {

			case WITHHOLD_TAX_CALC_TYPE_RAGE:
				$this->_tax_amount = $withhold_tax->tax_amount;
				break;

			case WITHHOLD_TAX_CALC_TYPE_RATIO:
				// 例）24,000円の場合の税額に、その日の社会保険料等控除後の給与等の金額のうち24,000円を超える金額の20.42％に相当する金額を加算した金額
				$this->_tax_amount = $withhold_tax->ratio_base_tax_amount + (($amount - $withhold_tax->ratio_base_amount) * $withhold_tax->amount_ratio);
				break;
			
			default:
				return false;
				break;

		}

	}

	/**
	 * setter
	 * 
	 */
	public function get_tax() {

		return $this->_tax_amount;
	
	}

}