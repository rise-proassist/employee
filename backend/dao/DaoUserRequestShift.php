<?php

/**
 * DAO 資格証明書テーブル
 *
 * @author kanemiya
 *
 */
class DaoUserRequestShift extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'user_request_shift';

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

		$query = $this->_dba->from($this->_table_name . ' urs')
							->leftJoin('user u ON urs.user_id = u.id')
							->select(null)
							->select('
								urs.id,
								urs.user_id,
								urs.shift_date_from,
								urs.shift_date_to,
								urs.create_date,
								urs.update_date,
								u.code,
								u.status_type,
								u.approve_type,
								u.name,
								u.name_kana,
								u.tel,
								u.email,
								u.password,
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

				switch ($column) {

					case 'urs.shift_date_from':		
						$query->where("urs.shift_date_from <= '" . $value . "'");
						break;

					case 'urs.shift_date_to':
						$query->where("urs.shift_date_to >= '" . $value . "'");
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
			$data->user_id = $row['user_id'];
			$data->shift_date_from = $row['shift_date_from'];
			$data->shift_date_to = $row['shift_date_to'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$data->code = $row['code'];
			$data->status_type = $row['status_type'];
			$data->approve_type = $row['approve_type'];
			$data->name = $row['name'];
			$data->name_kana = $row['name_kana'];
			$data->tel = $row['tel'];
			$data->email = $row['email'];
			$data->password = $row['password'];
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

	/**
	 * 条件抽出(従事者情報付き)
	 *
	 * @param array $wheres 条件配列
	 * @param int $limit 上限件数
	 * @param int $offset 取得位置
	 * @return int $entrys 結果
	 *
	 */
	public function select_where_with_user_for_range_check($wheres = null, $sorts = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name . ' urs')
							->leftJoin('user u ON urs.user_id = u.id')
							->select(null)
							->select('
								urs.id,
								urs.user_id,
								urs.shift_date_from,
								urs.shift_date_to,
								urs.create_date,
								urs.update_date,
								u.code,
								u.status_type,
								u.approve_type,
								u.name,
								u.name_kana,
								u.tel,
								u.email,
								u.password,
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

				switch ($column) {

					case 'urs.shift_date_from':
						$query->where("urs.shift_date_from <= '" . $value . "'");
						break;

					case 'urs.shift_date_to':
						$query->where("urs.shift_date_to >= '" . $value . "'");
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
			$data->user_id = $row['user_id'];
			$data->shift_date_from = $row['shift_date_from'];
			$data->shift_date_to = $row['shift_date_to'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$data->code = $row['code'];
			$data->status_type = $row['status_type'];
			$data->approve_type = $row['approve_type'];
			$data->name = $row['name'];
			$data->name_kana = $row['name_kana'];
			$data->tel = $row['tel'];
			$data->email = $row['email'];
			$data->password = $row['password'];
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

	/**
	 * 条件抽出(従事者情報付き)
	 *
	 * @param array $wheres 条件配列
	 * @param int $limit 上限件数
	 * @param int $offset 取得位置
	 * @return int $entrys 結果
	 *
	 */
	public function select_for_duplicate_check($wheres = null, $sorts = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name)
							->select(null)
							->select('
								id,
								user_id,
								shift_date_from,
								shift_date_to,
								create_date,
								update_date
							');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				switch ($column) {

					case 'shift_date_from':
						$query->where("shift_date_from <= '" . $value . "'");
						break;

					case 'shift_date_to':
						$query->where("shift_date_to >= '" . $value . "'");
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
			$data->user_id = $row['user_id'];
			$data->shift_date_from = $row['shift_date_from'];
			$data->shift_date_to = $row['shift_date_to'];
			$data->create_date = $row['create_date'];
			$data->update_date = $row['update_date'];
			$datas[] = $data;
		}
		return $datas;

	}

	/**
	 * 削除（シフト日付）
	 * @see DaoBase::delete()
	 */
	public function delete_by_shift_date($wheres) {
		
		// クエリ
		$query = $this->_dba->deleteFrom($this->_table_name);

		if (is_array($wheres)) {
			foreach ($wheres as $column => $value) {

				switch ($column) {

					case 'shift_date_from':
						$query->where("shift_date >= '" . $value . "'");
						break;

					case 'shift_date_to':
						$query->where("shift_date <= '" . $value . "'");
						break;
					
					default:
						$query->where($column, $value);
						break;
				}
				
			}
		}

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		// 削除
		$result = $query->execute();
		$this->_logger->info('[SQL-Result] Delete Record Num : ' . $result);

		return $result;
		
	}

}