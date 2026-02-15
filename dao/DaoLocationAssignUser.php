<?php

/**
 * DAO 現場アサイン従事者テーブル
 *
 * @author kanemiya
 *
 */
class DaoLocationAssignUser extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'location_assign_user';

	//　あいまい検索対象カラム
	const LIKE_COLUMNS = array(
		'u.name'
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
							->leftJoin('location l ON ls.location_id = l.id')
							->leftJoin('location_assign_user_notified laun ON ls.location_id = laun.location_id AND lau.user_id = laun.user_id')
							->leftJoin('country ct ON u.country_id = ct.id')
							->leftJoin('company cp ON u.company_id = cp.id')
							->select(null)
							->select('
								lau.id,
								lau.location_shift_id,
								lau.location_shift_row,
								lau.user_id,
								lau.allocate_cert_ids,
								lau.work_date_from,
								lau.is_modify_from,
								lau.work_date_to,
								lau.is_modify_to,
								lau.comment,
								lau.receipt_user_name,
								lau.daily_wage,
								lau.withhold_tax,
								lau.total_wage,
								lau.is_confirmed,
								lau.is_paid,
								lau.paid_date,
								lau.create_date,
								lau.update_date,
								laun.is_notified,
								ls.location_id,
								ls.name AS location_shift_name,
								ls.shift_date_from,
								ls.shift_date_to,
								ls.note,
								l.name AS location_name,
								l.address AS location_address,
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
								ct.name AS country_name,
								u.employ_type,
								u.company_id,
								cp.name AS company_name,
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

		$this->log_sql($query->getQuery(false));
		$this->log_sql_params($query->getParameters());

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$datas = null;
		foreach($select_list as $row) {
			$data = new stdClass();
			$data->id = $row['id'];
			$data->location_shift_id = $row['location_shift_id'];
			$data->location_shift_row = $row['location_shift_row'];
			$data->user_id = $row['user_id'];
			$data->allocate_cert_ids = $row['allocate_cert_ids'];
			$data->work_date_from = $row['work_date_from'];
			$data->is_modify_from = $row['is_modify_from'];
			$data->work_date_to = $row['work_date_to'];
			$data->is_modify_to = $row['is_modify_to'];
			$data->comment = $row['comment'];
			$data->receipt_user_name = $row['receipt_user_name'];
			$data->daily_wage = $row['daily_wage'];
			$data->withhold_tax = $row['withhold_tax'];
			$data->total_wage = $row['total_wage'];
			$data->is_confirmed = $row['is_confirmed'];
			$data->is_paid = $row['is_paid'];
			$data->paid_date = $row['paid_date'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$data->is_notified = $row['is_notified'];
			$data->location_id = $row['location_id'];
			$data->location_shift_name = $row['location_shift_name'];
			$data->shift_date_from = $row['shift_date_from'];
			$data->shift_date_to = $row['shift_date_to'];
			$data->note = $row['note'];
			$data->location_name = $row['location_name'];
			$data->location_address = $row['location_address'];
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
			$data->country_name = $row['country_name'];
			$data->employ_type = $row['employ_type'];
			$data->company_id = $row['company_id'];
			$data->company_name = $row['company_name'];
			$data->etc_company_name = $row['etc_company_name'];
			$data->last_login_date = $row['last_login_date'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$datas[] = $data;
		}
		return $datas;

	}

	/**
	 * 条件抽出(検索用)
	 *
	 * @param array $wheres 条件配列
	 * @param int $limit 上限件数
	 * @param int $offset 取得位置
	 * @param boolean $is_all 全件抽出フラグ
	 * @return int $entrys 結果
	 *
	 */
	public function select_for_search($wheres = null, $sorts = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name . ' lau')
							->leftJoin('location_shift ls ON lau.location_shift_id = ls.id')
							->leftJoin('location l ON ls.location_id = l.id')
							->leftJoin('user u ON lau.user_id = u.id')
							->leftJoin('country ct ON u.country_id = ct.id')
							->leftJoin('company cp ON u.company_id = cp.id')
							->select(null)
							->select('
								lau.id,
								lau.location_shift_id,
								lau.location_shift_row,
								lau.user_id,
								lau.allocate_cert_ids,
								lau.work_date_from,
								lau.is_modify_from,
								lau.work_date_to,
								lau.is_modify_to,
								lau.comment,
								lau.receipt_user_name,
								lau.daily_wage,
								lau.withhold_tax,
								lau.total_wage,
								lau.is_confirmed,
								lau.is_paid,
								lau.paid_date,
								lau.create_date,
								lau.update_date,
								ls.name AS location_shift_name,
								ls.shift_date_from,
								ls.shift_date_to,
								ls.note,
								l.id AS location_id,
								l.name AS location_name,
								l.address AS location_address
							');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				switch ($column) {

					case 'ls.shift_date_from':
						$query->where('ls.shift_date_from >= ? ', $value);
						break;

					case 'ls.shift_date_to':
						$query->where('ls.shift_date_to <= ? ', $value);
						break;

					case 'ls.shift_year_from':
						$query->where('ls.shift_date_from >= ? ', $value);
						break;

					case 'ls.shift_year_to':
						$query->where('ls.shift_date_from <= ? ', $value);
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

		$this->log_sql($query->getQuery(false));
		$this->log_sql_params($query->getParameters());

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$datas = null;
		foreach($select_list as $row) {
			$data = new stdClass();
			$data->id = $row['id'];
			$data->location_shift_id = $row['location_shift_id'];
			$data->location_shift_row = $row['location_shift_row'];
			$data->user_id = $row['user_id'];
			$data->allocate_cert_ids = $row['allocate_cert_ids'];
			$data->work_date_from = $row['work_date_from'];
			$data->is_modify_from = $row['is_modify_from'];
			$data->work_date_to = $row['work_date_to'];
			$data->is_modify_to = $row['is_modify_to'];
			$data->comment = $row['comment'];
			$data->receipt_user_name = $row['receipt_user_name'];
			$data->daily_wage = $row['daily_wage'];
			$data->withhold_tax = $row['withhold_tax'];
			$data->total_wage = $row['total_wage'];
			$data->is_confirmed = $row['is_confirmed'];
			$data->is_paid = $row['is_paid'];
			$data->paid_date = $row['paid_date'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$data->location_shift_name = $row['location_shift_name'];
			$data->shift_date_from = $row['shift_date_from'];
			$data->shift_date_to = $row['shift_date_to'];
			$data->note = $row['note'];
			$data->location_id = $row['location_id'];
			$data->location_name = $row['location_name'];
			$data->location_address = $row['location_address'];
			$datas[] = $data;
		}

		return $datas;

	}

	/**
	 * 条件抽出(検索用)
	 *
	 * @param array $wheres 条件配列
	 * @param int $limit 上限件数
	 * @param int $offset 取得位置
	 * @return int $entrys 結果
	 *
	 */
	public function select_count_for_search($wheres = null) {

		$query = $this->_dba->from($this->_table_name . ' lau')
							->leftJoin('location_shift ls ON lau.location_shift_id = ls.id')
							->leftJoin('location l ON ls.location_id = l.id')
							->leftJoin('user u ON lau.user_id = u.id')
							->leftJoin('country ct ON u.country_id = ct.id')
							->leftJoin('company cp ON u.company_id = cp.id')
							->select(null)
							->select('COUNT(lau.id) AS num');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				switch ($column) {
					
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

		$this->log_sql($query->getQuery(false));
		$this->log_sql_params($query->getParameters());

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$entry = null;
		foreach($select_list as $row) {
			$data = new stdClass();
			$data->num = $row['num'];
		}
		
		return $data;

	}

}