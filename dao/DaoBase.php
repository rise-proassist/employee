<?php

/**
 * Dao 基底抽象クラス
 *
 * @author kanemiya
 *
 */

abstract class DaoBase {
	
	protected $_dba;

	protected $_entity;
	
	protected $_table_name;
	
	protected $_primary_key;

	protected $_like_column;

	protected $_logger;

	public function __construct() {

		$this->_dba = DatabaseAccess::get_instance();
		$this->_logger = Logger::getLogger(basename(__FILE__));
		$this->_logger->setLevel(LoggerLevel::getLevelInfo());

	}

	/**
	 * トランザクション
	 *
	 */
	public function begin() {
		
		$query = $this->_dba->getPdo()
							->exec('START TRANSACTION');

		// $this->_logger->info('[SQL] ' . $query->getQuery(false));
		
	}
	
	/**
	 * コミット
	 *
	 */
	public function commit() {
		
		$query = $this->_dba->getPdo()
							->exec('COMMIT');

		// $this->_logger->info('[SQL] ' . $query->getQuery(false));
		
	}
	
	/**
	 * ロールバック
	 *
	 */
	public function rollback() {
		
		$query = $this->_dba->getPdo()
							->exec('Rollback');

		// $this->_logger->info('[SQL] ' . $query->getQuery(false));
		
	}

	
	/**
	 * キー抽出
	 *
	 * @see DaoBase::select_by_key()
	 * @param $value 値(キー)
	 *
	 */
	public function select_by_key($value) {

		$query = $this->_dba->from($this->_table_name)
							->where($this->_primary_key, $value);

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$entrys = null;
		foreach($select_list as $row) {
			$entity = new $this->_entity();
			$entrys = $entity->getEntity($row);
		}

		return $entrys;

	}

	/**
	 * キー抽出（複数）
	 *
	 * @see DaoBase::select_by_keys()
	 * @param $value 値(キー)
	 *
	 */
	public function select_by_keys($values) {

		$query = $this->_dba->from($this->_table_name)
							->where($this->_primary_key, $values);

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$entrys = null;
		foreach($select_list as $row) {
			$entity = new $this->_entity();
			$entrys[] = $entity->getEntity($row);
		}

		return $entrys;

	}

	/**
	 * キー抽出(キーロック)
	 *
	 * @see DaoBase::select_by_key_for_update()
	 * @param $value 値(キー)
	 *
	 */
	public function select_by_key_for_update($value) {

		$query = $this->_dba->from($this->_table_name)
							->where($this->_primary_key, $value)
							->forUpdate();

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$entrys = null;
		foreach($select_list as $row) {
			$entity = new $this->_entity();
			$entrys = $entity->getEntity($row);
		}

		return $entrys;

	}

	/**
	 * 全件抽出
	 *
	 * @param array $sort ソート配列
	 * @return int $entrys 結果
	 *
	 */
	public function select_all($sort = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name);

		if ($limit)
			$query->limit($limit);

		if ($offset)
			$query->offset($offset);

		if ($sort) {
			
			foreach ($sort as $column => $order) {

				$query->orderBy('`' . $column . '` ' . $order);
			
			}
			
		}

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$entrys = null;
		foreach($select_list as $row) {
			$entity = new $this->_entity();
			$entrys[] = $entity->getEntity($row);
		}

		return $entrys;
		
	}

	/**
	 * 条件抽出(複数件)
	 *
	 * @param array $wheres 条件配列
	 * @param array $srots ソート配列
	 * @param int $limit 取得上限値
	 * @param array $offset 取得開始位置
	 * @return int $entrys 結果
	 *
	 */
	public function select_where($wheres = null, $sort = null, $limit = null, $offset = null) {

		$query = $this->_dba->from($this->_table_name);

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				if (in_array($column, $this->_like_columns)) {

					$query->where('`' . $column  . "` LIKE ?", sprintf('%%%s%%', addcslashes($value, '\_%')));

				} else {

					$query->where('`' . $column . '`', $value);

				}

			}
		}

		if ($limit)
			$query->limit($limit);

		if ($offset)
			$query->offset($offset);

		if ($sort) {
			
			foreach ($sort as $column => $order) {

				$query->orderBy('`' . $column . '` ' . $order);
			
			}
			
		}

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$entrys = null;
		foreach($select_list as $row) {
			$entity = new $this->_entity();
			$entrys[] = $entity->getEntity($row);
		}

		return $entrys;
	}

	/**
	 * 条件抽出(単数件)
	 *
	 * @param array $wheres 条件配列
	 * @param array $srots ソート配列
	 * @return int $entrys 結果
	 *
	 */
	public function select_where_one($wheres = null, $sort = null) {

		$query = $this->_dba->from($this->_table_name);

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				if (in_array($column, $this->_like_columns)) {

					$query->where('`' . $column  . "` LIKE ?", sprintf('%%%s%%', addcslashes($value, '\_%')));

				} else {

					$query->where('`' . $column . '`', $value);

				}

			}
		}

		if ($sort) {
			
			foreach ($sort as $column => $order) {

				$query->orderBy('`' . $column . '` ' . $order);
			
			}
			
		}

		$query->limit(1);

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		$select_list = $query->fetchAll();
		if (!$select_list)
			return null;

		$entry = null;
		foreach($select_list as $row) {
			$entity = new $this->_entity();
			$entry = $entity->getEntity($row);
		}

		return $entry;

	}

	/**
	 * 件数抽出
	 *
	 * @param array $wheres 条件配列
	 * @return int $num 件数 
	 *
	 */
	public function select_count($wheres = null) {

		$query = $this->_dba->from($this->_table_name)
							->select(null)
							->select('COUNT(' . $this->_primary_key . ') AS num');

		if (!is_null($wheres) && is_array($wheres)) {

			foreach($wheres as $column => $value) {

				if (in_array($column, $this->_like_columns)) {

					$query->where('`' . $column  . "` LIKE ?", sprintf('%%%s%%', addcslashes($value, '\_%')));

				} else {

					$query->where('`' . $column . '`', $value);

				}

			}
		}

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		return (int)$query->fetchAll()[0]['num'];

	}
	
	/**
	 * 登録
	 * @see DaoBase::insert()
	 * @param Array $entity エンティティ
	 */
	public function insert($entity = null) {

		$values = get_object_vars($entity);

		$now = date("Y-m-d H:i:s");

		if (array_key_exists('create_date', $values))
			$values['create_date'] = $now;

		if (array_key_exists('update_date', $values))
			$values['update_date'] = $now;

		// クエリ
		$query = $this->_dba->insertInto($this->_table_name)
							->values($values);

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		// 登録
		$last_id = $query->execute();
		$this->_logger->info('[SQL-Result] Last Insert Id : ' . $last_id);

		return $last_id;
		
	}
	
	/**
	 * 更新
	 * @see DaoBase::update()
	 * @param array $sets 更新パラメータ　 
	 * @param array $wheres キーパラメータ
	 * @param boolean $is_update_date 型タイプ
	 * @return int 更新件数
	 */
	public function update($sets, $wheres, $is_update_date = true) {

		if ($is_update_date)
			$sets['update_date'] = date("Y-m-d H:i:s");
		
		// クエリ
		$query = $this->_dba->update($this->_table_name)
							->set($sets);
							
		if (is_array($wheres)) {
			foreach ($wheres as $column => $value) {
				$query->where($column, $value);
			}
		}

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));
		
		// 更新
		$result = $query->execute();
		$this->_logger->info('[SQL-Result] Update Record Num : ' . $result);

		return $result;
		
	}
	
	/**
	 * 削除
	 * @see DaoBase::delete()
	 */
	public function delete($wheres) {
		
		// クエリ
		$query = $this->_dba->deleteFrom($this->_table_name);

		if (is_array($wheres)) {
			foreach ($wheres as $column => $value) {
				$query->where($column, $value);
			}
		}

		$this->_logger->info('[SQL] ' . $query->getQuery(false));
		$this->_logger->info('[SQL-Params] ' . implode(', ', $query->getParameters()));

		// 削除
		$result = $query->execute();
		$this->_logger->info('[SQL-Result] Delete Record Num : ' . $result);

		return $result;
		
	}

	/**
	 * 任意SELECT実行
	 *
	 * @param string $sql SQL
	 * @return array
	 */
	public function execute_raw_select($sql) {

		$this->_logger->info('[SQL] ' . $sql);
		$this->_logger->info('[SQL-Params]');

		$pdo = $this->_dba->getPdo();
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$this->_logger->info('[SQL-Result] Select Record Num : ' . count((array)$rows));

		return $rows;
	}

	/**
	 * 任意更新系SQL実行
	 *
	 * @param string $sql SQL
	 * @return int
	 */
	public function execute_raw_mutation($sql) {

		$this->_logger->info('[SQL] ' . $sql);
		$this->_logger->info('[SQL-Params]');

		$pdo = $this->_dba->getPdo();
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$affected = (int)$stmt->rowCount();

		$this->_logger->info('[SQL-Result] Affected Record Num : ' . $affected);

		return $affected;
	}

	/**
	 * 全削除
	 * @see DaoBase::truncate()
	 */
	public function truncate() {

		$query = $this->_dba->getPdo()
							->exec('TRUNCATE ' . $this->_table_name);

		$this->_logger->info('[SQL-Result] Truncated Table :' . $this->_table_name);
		
	}
	
}

?>