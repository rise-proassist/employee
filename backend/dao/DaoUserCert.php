<?php

/**
 * DAO 従事者資格証明書テーブル
 *
 * @author kanemiya
 *
 */
class DaoUserCert extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'user_cert';

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
	public function select_where_with_cert($wheres = null, $sorts = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name . ' uc')
							->leftJoin('cert c ON uc.cert_id = c.id')
							->select(null)
							->select('
								uc.id,
								uc.user_id,
								uc.cert_id,
								c.name AS cert_name,
								c.short_name AS cert_short_name,
								uc.cert_type,
								uc.file_name,
								uc.is_approved,
								uc.comment,
								uc.create_date,
								uc.update_date
							');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				if (in_array($column, $this->_like_columns)) {

					$query->where($column  . " LIKE ?", sprintf('%%%s%%', addcslashes($value, '\_%')));

				} else {

					$query->where($column, $value);

				}

				break;

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
			$data->user_id = $row['user_id'];
			$data->cert_id = $row['cert_id'];
			$data->cert_name = $row['cert_name'];
			$data->cert_short_name = $row['cert_short_name'];
			$data->cert_type = $row['cert_type'];
			$data->file_name = $row['file_name'];
			$data->is_approved = $row['is_approved'];
			$data->comment = $row['comment'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$datas[] = $data;
		}
		return $datas;

	}

}