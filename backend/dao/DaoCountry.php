<?php

/**
 * DAO 国籍テーブル
 *
 * @author kanemiya
 *
 */
class DaoCountry extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'country';

	//　あいまい検索対象カラム
	const LIKE_COLUMNS = array(
		'name',
	);
	
	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {

		parent::__construct();

		// テーブル名
		$this->_table_name = '`' . self::TABLE_NAME . '`';

		// エンティティ
		$this->_entity = 'Entity' . UtilCommon::to_camelize(str_replace('`', '', $this->_table_name));

		// PK
		$this->_primary_key = '`id`';

		// あいまい検索対象カラム
		$this->_like_columns = self::LIKE_COLUMNS;
		
	}

}