<?php

/**
 * DAO 現場シフトテーブル
 *
 * @author kanemiya
 *
 */
class DaoLocationShift extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'location_shift';

	//　あいまい検索対象カラム
	const LIKE_COLUMNS = array(
		'name',
		'l.name',
		'ls.name',
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
	 * 条件抽出(現場付き)
	 *
	 * @param array $wheres 条件配列
	 * @param int $limit 上限件数
	 * @param int $offset 取得位置
	 * @return int $entrys 結果
	 *
	 */
	public function select_where_with_location($wheres = null, $sorts = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name . ' ls')
							->leftJoin('location l ON ls.location_id = l.id')
							->select(null)
							->select('
								ls.id,
								ls.location_id,
								ls.name AS shift_name,
								ls.shift_date_from,
								ls.shift_date_to,
								ls.request_num,
								ls.note,
								ls.create_date,
								ls.update_date,
								l.name AS location_name,
								l.location_date,
								l.address,
								l.detail,
								l.is_assigned,
								l.is_closed
							');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				switch ($column) {

					case 'ls.shift_date_from':
						$query->where("ls.shift_date_from >= ?", $value);
						break;

					case 'ls.shift_date_to':
						$query->where("ls.shift_date_to <= ?", $value);
						break;
					
					default:
						if (in_array($column, $this->_like_columns)) {

							$query->where($column  . " LIKE ?", sprintf('%%%s%%', addcslashes($value, '\_%')));

						} else {

							$query->where($column, $value);

						}
						break;

				}

			}

		}

		if ($sorts) {
			
			foreach ($sorts as $column => $order) {

				$query->orderBy($column . ' ' . $order);
			
			}
			
		}

		if ($limit)
			$query->limit($limit);

		if ($offset)
			$query->offset($offset);

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$datas = null;
		foreach($select_list as $row) {
			$data = new stdClass();
			$data->id = $row['id'];
			$data->location_id = $row['location_id'];
			$data->shift_name = $row['shift_name'];
			$data->shift_date_from = $row['shift_date_from'];
			$data->shift_date_to = $row['shift_date_to'];
			$data->request_num = $row['request_num'];
			$data->note = $row['note'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$data->location_name = $row['location_name'];
			$data->location_date = $row['location_date'];
			$data->address = $row['address'];
			$data->detail = $row['detail'];
			$data->is_assigned = $row['is_assigned'];
			$data->is_closed = $row['is_closed'];
			$datas[] = $data;
		}
		return $datas;

	}

}