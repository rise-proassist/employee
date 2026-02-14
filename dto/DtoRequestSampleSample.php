<?php

/**
 * DTO リクエストSampleSample
 *
 * @author kanemiya
 *
 */

class DtoRequestSampleSample {
	
	public $id = array(
		'required' => true,
		'min' => 1,
		);

	public $name = array(
		'required' => true,
		'str_min' => 4,
		'str_max' => 16,
		);

	public $create_date;

	public $update_date;

}