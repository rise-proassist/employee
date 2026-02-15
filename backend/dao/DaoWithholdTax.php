<?php

/**
 * DAO 源泉徴収金額マスタテーブル
 *
 * @author kanemiya
 *
 */
class DaoWithholdTax extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'withhold_tax';

	//　あいまい検索対象カラム
	const LIKE_COLUMNS = array(
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

	/**
	 * 条件抽出(各種マスタ付き)
	 *
	 * @param array $wheres 条件配列
	 * @param int $limit 上限件数
	 * @param int $offset 取得位置
	 * @return int $entrys 結果
	 *
	 */
	public function select_where_one_for_amount($wheres = null) {

		$query = $this->_dba->from($this->_table_name);

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				if (in_array($column, $this->_like_columns)) {

					$query->where($column  . " LIKE ?", sprintf('%%%s%%', addcslashes($value, '\_%')));

				} else if ('amount_range_from' == $column) {

					$query->where('amount_range_from <= ' . $value);

				} else if ('amount_range_to' == $column) {

					$query->where('amount_range_to >= ' . $value);

				} else {

					$query->where($column, $value);

				}

			}

		}
		
		$query->limit(1);

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$datas = null;
		foreach($select_list as $row) {
			$entity = new EntityWithholdTax();
			$data = $entity->getEntity($row);
		}
		return $data;

	}

}