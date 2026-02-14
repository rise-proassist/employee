<?php

/**
 * DAO 従事者テーブル
 *
 * @author kanemiya
 *
 */
class DaoUser extends DaoBase {
	
	// 各テーブル名
	const TABLE_NAME = 'user';

	//　あいまい検索対象カラム
	const LIKE_COLUMNS = array(
		'u.name',
		'u.email',
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
	public function select_where_with_master($wheres = null, $sorts = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name . ' u')
							->leftJoin('country ct ON u.country_id = ct.id')
							->leftJoin('company cp ON u.company_id = cp.id')
							->select(null)
							->select('
								u.id,
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
								ct.name AS country_name,
								u.employ_type,
								u.company_id,
								cp.name AS company_name,
								u.etc_company_name,
								u.lang_type,
								u.last_login_date,
								u.note,
								u.create_date,
								u.update_date
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
			$data->country_name = $row['country_name'];
			$data->employ_type = $row['employ_type'];
			$data->company_id = $row['company_id'];
			$data->company_name = $row['company_name'];
			$data->etc_company_name = $row['etc_company_name'];
			$data->lang_type = $row['lang_type'];
			$data->last_login_date = $row['last_login_date'];
			$data->note = $row['note'];
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
	public function select_for_search($wheres = null, $sorts = null, $limit = null, $offset = null, $is_all = false) {

		$query = $this->_dba->from($this->_table_name . ' u')
							->leftJoin('country ct ON u.country_id = ct.id')
							->leftJoin('company cp ON u.company_id = cp.id')
							->select(null)
							->select('
								u.id,
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
								ct.name AS country_name,
								u.employ_type,
								u.company_id,
								cp.name AS company_name,
								u.etc_company_name,
								u.last_login_date,
								u.create_date,
								u.update_date
							');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				if (!$value)
					continue;

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

		if ($sorts) {
		
			foreach ($sorts as $column => $order) {

				$query->orderBy(' ' . $column . ' ' . $order);
			
			}
			
		}

		// 全会員抽出出ない場合は、有効会員のみ
		if (!$is_all)
			$query->where('status_type', PARAM_CONST_USER_STATUS_TYPE_ENABLE);

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
	public function select_count_for_search($wheres = null, $is_all = false) {

		$query = $this->_dba->from($this->_table_name . ' u')
							->select(null)
							->select('COUNT(id) AS num');

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

		// 全会員抽出出ない場合は、有効会員のみ
		if (!$is_all)
			$query->where('status_type', PARAM_CONST_USER_STATUS_TYPE_ENABLE);

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

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