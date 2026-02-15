<?php

/**
 * ENTITY 管理者
 *
 * @author kanemiya
 *
 */

class EntityWithholdTax {
	
	public $id;

	public $calc_type;

	public $amount_range_from;

	public $amount_range_to;

	public $amount_ratio;

	public $ratio_base_amount;

	public $ratio_base_tax_amount;

	public $tax_amount;

	public $create_date;

	public $update_date;
	
	static public function getEntity($data) {
		$entity = new self();
		$entity->id = $data['id'];
		$entity->calc_type = $data['calc_type'];
		$entity->amount_range_from = $data['amount_range_from'];
		$entity->amount_range_to = $data['amount_range_to'];
		$entity->amount_ratio = $data['amount_ratio'];
		$entity->ratio_base_amount = $data['ratio_base_amount'];
		$entity->ratio_base_tax_amount = $data['ratio_base_tax_amount'];
		$entity->tax_amount = $data['tax_amount'];
		$entity->create_date = $data['create_date'];
		$entity->update_date = $data['update_date'];
		return $entity;
	}
}
?>