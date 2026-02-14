<?php

/**
 * DAO 現場アサイン従事者実績テーブル
 *
 * @author kanemiya
 *
 */
class DaoLocationAssignUserResult extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'location_assign_user_result';

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
	 * 条件抽出(従事者情報付き)
	 *
	 * @param array $wheres 条件配列
	 * @param int $limit 上限件数
	 * @param int $offset 取得位置
	 * @return int $entrys 結果
	 *
	 */
	public function select_where_with_user($wheres = null, $sorts = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name . ' lau')
							->leftJoin('user u ON lau.user_id = u.id')
							->leftJoin('location_shift ls ON ls.id = lau.location_shift_id')
							->leftJoin('location_assign_user_notified laun ON ls.location_id = laun.location_id AND lau.user_id = laun.user_id')
							->select(null)
							->select('
								lau.id,
								lau.location_shift_id,
								lau.user_id,
								lau.work_date_from,
								lau.is_modify_from,
								lau.work_date_to,
								lau.is_modify_to,
								lau.comment,
								lau.create_date,
								lau.update_date,
								laun.is_notified,
								ls.id AS location_shift_id,
								ls.location_id,
								ls.name AS location_shift_name,
								ls.shift_date_from,
								ls.shift_date_to,
								ls.note,
								u.code,
								u.status_type,
								u.approve_type,
								u.name,
								u.name_kana,
								u.tel,
								u.email,
								u.post_code,
								u.address,
								u.gender_type,
								u.birth_day,
								u.country_id,
								u.employ_type,
								u.company_id,
								u.etc_company_name,
								u.last_login_date
							');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				if (in_array($column, $this->_like_columns)) {

					$query->where($column  . " LIKE ?", sprintf('%%%s%%', addcslashes($value, '\_%')));

				} else {

					$query->where($column, $value);

				}

			}

		}

		if ($sorts) {
			
			foreach ($sorts as $column => $order) {

				$query->orderBy($column . ' ' . $order);
			
			}
			
		}

		$query->groupBy('lau.id');

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
			$data->location_shift_id = $row['location_shift_id'];
			$data->user_id = $row['user_id'];
			$data->work_date_from = $row['work_date_from'];
			$data->is_modify_from = $row['is_modify_from'];
			$data->work_date_to = $row['work_date_to'];
			$data->is_modify_to = $row['is_modify_to'];
			$data->comment = $row['comment'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$data->is_notified = $row['is_notified'];
			$data->location_shift_id = $row['location_shift_id'];
			$data->location_id = $row['location_id'];
			$data->location_shift_name = $row['location_shift_name'];
			$data->shift_date_from = $row['shift_date_from'];
			$data->shift_date_to = $row['shift_date_to'];
			$data->note = $row['note'];
			$data->code = $row['code'];
			$data->status_type = $row['status_type'];
			$data->approve_type = $row['approve_type'];
			$data->name = $row['name'];
			$data->name_kana = $row['name_kana'];
			$data->tel = $row['tel'];
			$data->email = $row['email'];
			$data->post_code = $row['post_code'];
			$data->address = $row['address'];
			$data->gender_type = $row['gender_type'];
			$data->birth_day = $row['birth_day'];
			$data->country_id = $row['country_id'];
			$data->employ_type = $row['employ_type'];
			$data->company_id = $row['company_id'];
			$data->etc_company_name = $row['etc_company_name'];
			$data->last_login_date = $row['last_login_date'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$datas[] = $data;
		}
		return $datas;

	}

}