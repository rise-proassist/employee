<?php

/**
 * CONTROLLER : MyPage
 *
 *　@author kanemiya
 */

class MypageController extends BaseController {

	protected $_is_view = true;

	protected $_logger;

	private $_auth_actions = array();

	private $_login_user;

	const NAME_LENGTH = 30;

	const NAME_KANA_LENGTH = 30;

	const TEL_LENGTH = 16;

	const EMAIL_LENGTH = 64;

	const PASSWORD_LENGTH = 16;

	const RESULT_LIST_LIMIT = 6;
	const DEBUG_HISTORY_MAX_BYTES = 262144;
	const DEBUG_HISTORY_MAX_ENTRY_BYTES = 32768;

	private function to_sql_literal($value) {

		$trimmed = trim((string)$value);
		if (strtoupper($trimmed) === 'NULL') {
			return 'NULL';
		}

		if (preg_match('/^-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?$/', $trimmed)) {
			return $trimmed;
		}

		return "'" . str_replace("'", "''", $trimmed) . "'";
	}

	private function parse_log_params($param_text) {

		$param_text = trim((string)$param_text);
		if ($param_text === '') {
			return array();
		}

		$parts = preg_split('/\s*,\s*/', $param_text);
		if (!is_array($parts)) {
			return array();
		}

		$params = array();
		foreach ($parts as $part) {
			$params[] = $part;
		}

		return $params;
	}

	private function expand_sql_with_params($sql, $params) {

		$expanded = $sql;
		foreach ((array)$params as $param) {
			$expanded = preg_replace('/\?/', $this->to_sql_literal($param), $expanded, 1);
		}

		return $expanded;
	}

	private function get_formatted_query_logs($limit = 100) {

		$query_logs = array();
		$log_file = LOG_DIR . '/app_' . date('Ymd') . '.log';
		if (!is_readable($log_file))
			return $query_logs;

		$lines = @file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (!is_array($lines))
			return $query_logs;

		$current = null;
		foreach ($lines as $line) {
			if (false !== strpos($line, '[SQL] ')) {
				if ($current && !empty($current)) {
					$query_logs[] = $current;
				}

				$sql = trim(substr($line, strpos($line, '[SQL] ') + 6));
				if (preg_match('/^SELECT\b/i', ltrim($sql))) {
					$current = null;
					continue;
				}

				$current = array(
					'sql' => $sql,
					'params' => array(),
				);
				continue;
			}

			if (!$current)
				continue;

			if (false !== strpos($line, '[SQL-Params] ')) {
				$current['params'] = $this->parse_log_params(substr($line, strpos($line, '[SQL-Params] ') + 13));
				continue;
			}
		}

		if ($current && !empty($current)) {
			$query_logs[] = $current;
		}

		if (count($query_logs) > $limit) {
			$query_logs = array_slice($query_logs, -$limit);
		}

		$formatted_query_logs = array();
		foreach ($query_logs as $index => $query_log) {
			$raw_sql = isset($query_log['sql']) ? $query_log['sql'] : '';
			$params = isset($query_log['params']) ? $query_log['params'] : array();
			$sql = $this->expand_sql_with_params($raw_sql, $params);
			$sql = preg_replace('/\s+/', ' ', trim($sql));
			$sql = wordwrap($sql, 110, "\n      ", false);
			$formatted_query_logs[] = '-- Query ' . str_pad((string)($index + 1), 3, '0', STR_PAD_LEFT) . "\n" . '   ' . $sql;
		}

		return array_reverse($formatted_query_logs);
	}

	private function get_debug_history_file_path() {

		return TMP_DIR . '/debug_query_history.json';
	}

	private function get_app_started_at() {

		$started_at = @filectime('/proc/1');
		if (!$started_at) {
			$started_at = time();
		}

		return (int)$started_at;
	}

	private function load_debug_history_data() {

		$app_started_at = $this->get_app_started_at();
		$data = array(
			'app_started_at' => $app_started_at,
			'entries' => array(),
		);

		$file = $this->get_debug_history_file_path();
		if (!is_readable($file)) {
			return $data;
		}

		$json = @file_get_contents($file);
		$loaded = json_decode((string)$json, true);
		if (!is_array($loaded)) {
			return $data;
		}

		$loaded_started_at = isset($loaded['app_started_at']) ? (int)$loaded['app_started_at'] : 0;
		if ($loaded_started_at !== $app_started_at) {
			return $data;
		}

		$entries = isset($loaded['entries']) && is_array($loaded['entries']) ? $loaded['entries'] : array();
		$data['entries'] = $entries;

		return $data;
	}

	private function save_debug_history_data($data) {

		$file = $this->get_debug_history_file_path();
		$dir = dirname($file);
		if (!is_dir($dir)) {
			@mkdir($dir, 0777, true);
		}

		@file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
	}

	private function append_debug_history_entry($command, $output, $executed_at) {

		$data = $this->load_debug_history_data();
		$entry = array(
			'executed_at' => (string)$executed_at,
			'command' => $this->trim_debug_text((string)$command, self::DEBUG_HISTORY_MAX_ENTRY_BYTES),
			'output' => $this->trim_debug_text((string)$output, self::DEBUG_HISTORY_MAX_ENTRY_BYTES),
		);

		$data['entries'][] = $entry;
		$data['entries'] = $this->trim_debug_history_entries_by_size($data['entries'], self::DEBUG_HISTORY_MAX_BYTES);

		$this->save_debug_history_data($data);
	}

	private function trim_debug_text($text, $max_bytes) {

		$text = (string)$text;
		if (strlen($text) <= $max_bytes) {
			return $text;
		}

		return substr($text, -$max_bytes);
	}

	private function format_debug_history_entry_text($entry) {

		$executed_at = isset($entry['executed_at']) ? $entry['executed_at'] : '';
		$command = isset($entry['command']) ? $entry['command'] : '';
		$output = isset($entry['output']) ? $entry['output'] : '';

		return '[' . $executed_at . '] > ' . $command . "\n" . $output;
	}

	private function trim_debug_history_entries_by_size($entries, $max_bytes) {

		$entries = is_array($entries) ? $entries : array();
		while ($entries) {
			$text = $this->get_debug_history_text_from_entries($entries);
			if (strlen($text) <= $max_bytes) {
				break;
			}
			array_shift($entries);
		}

		return $entries;
	}

	private function get_debug_history_text_from_entries($entries) {

		$lines = array();
		foreach ((array)$entries as $entry) {
			$lines[] = $this->format_debug_history_entry_text($entry);
		}

		return implode("\n\n", $lines);
	}

	private function get_debug_history_text() {

		$log_file = TMP_DIR . '/' . DaoBase::DEBUG_FOOTER_LOG_FILE;
		if (!is_readable($log_file)) {
			return '';
		}

		$text = (string)@file_get_contents($log_file);
		if ($text === '') {
			return '';
		}

		return rtrim($text, "\n");
	}

	private function normalize_debug_query($query) {

		$query = trim((string)$query);
		$query = preg_replace('/;+\s*$/', '', $query);

		return trim($query);
	}

	private function get_debug_query_type($query) {

		if (preg_match('/^SELECT\b/i', $query)) {
			return 'select';
		}

		if (preg_match('/^SHOW\s+TABLES\b/i', $query)) {
			return 'select';
		}

		if (preg_match('/^(?:INSERT|UPDATE|DELETE)\b/i', $query)) {
			return 'mutation';
		}

		return '';
	}

	private function to_ascii_cell($value) {

		if (is_null($value)) {
			return 'NULL';
		}

		if (is_bool($value)) {
			return $value ? '1' : '0';
		}

		if (is_scalar($value)) {
			return (string)$value;
		}

		return json_encode($value, JSON_UNESCAPED_UNICODE);
	}

	private function format_ascii_table($rows) {

		if (!$rows) {
			return "(0 rows)";
		}

		$columns = array_keys($rows[0]);
		$widths = array();
		foreach ($columns as $column) {
			$widths[$column] = strlen((string)$column);
		}

		$render_rows = array();
		foreach ((array)$rows as $row) {
			$render_row = array();
			foreach ($columns as $column) {
				$cell = $this->to_ascii_cell(isset($row[$column]) ? $row[$column] : null);
				$render_row[$column] = $cell;
				$cell_len = strlen($cell);
				if ($cell_len > $widths[$column]) {
					$widths[$column] = $cell_len;
				}
			}
			$render_rows[] = $render_row;
		}

		$border_parts = array();
		foreach ($columns as $column) {
			$border_parts[] = str_repeat('-', $widths[$column] + 2);
		}
		$border = '+' . implode('+', $border_parts) . '+';

		$header_cells = array();
		foreach ($columns as $column) {
			$header_cells[] = ' ' . str_pad($column, $widths[$column], ' ', STR_PAD_RIGHT) . ' ';
		}

		$lines = array();
		$lines[] = $border;
		$lines[] = '|' . implode('|', $header_cells) . '|';
		$lines[] = $border;

		foreach ($render_rows as $render_row) {
			$cells = array();
			foreach ($columns as $column) {
				$cells[] = ' ' . str_pad($render_row[$column], $widths[$column], ' ', STR_PAD_RIGHT) . ' ';
			}
			$lines[] = '|' . implode('|', $cells) . '|';
		}

		$lines[] = $border;
		$lines[] = '(' . count($render_rows) . ' rows)';

		return implode("\n", $lines);
	}

	function __construct() {
	
		parent::__construct();

	}

	protected function preAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$user = $model_session->get('user');
		$is_login = $model_session->is_login();

		$model_session->close();

		// ログイン種別チェック
		if (!$is_login)
			header('Location: ' . UtilCommon::get_base_url('login'));

		$this->_view->assign('login_user', $user);
		$this->_login_user = $user;
		$this->_view->assign('debug_query_log_text', $this->get_debug_history_text());

		// サイトタイトル（ヘッダ）
		switch ($this->_action) {
			case 'index':
			case 'detail':
				$this->_view->assign('site_title', '会員情報');
				break;
			case 'forcastForm':
			case 'forcastFinishAction':
				$this->_view->assign('site_title', '作業希望予定日');
				break;
			case 'resultList':
				$this->_view->assign('site_title', '確定作業一覧');
				break;
			case 'editAccountForm':
			case 'editAccountConfirm':
			case 'editAccountFinish':	
				$this->_view->assign('site_title', '会員情報変更');
				break;	
			case 'editEmailForm':
			case 'editEmailConfirm':
			case 'editEmailFinish':	
				$this->_view->assign('site_title', 'メールアドレスの変更');
				break;
			case 'editPasswordForm':
			case 'editPasswordConfirm':
			case 'editPasswordFinish':	
				$this->_view->assign('site_title', 'パスワードの変更');
				break;
			case 'resignForm':
			case 'resignFinish':
				$this->_view->assign('site_title', '退会の手続き');
				break;
		}
		
	}

	/**
	 * インデックスアクション
	 *
	 */
	public function indexAction() {

		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();

		$user = $model_session->get('user');
		if (!$user)
			echo false;

		$formatted_query_logs = $this->get_formatted_query_logs(100);

		$this->_view->assign('user', $user);
		$this->_view->assign('query_logs', $formatted_query_logs);
	}

	/**
	 * 希望シフトリストアクション
	 *
	 */
	public function requestShiftListAction() {
		$calendar_range_from = date("Y-m-d 00:00:00");
		$calendar_range_to = date("Y-m-t 23:59:59", strtotime('+2 month', strtotime(date('Y-m-01'))));

		// 作業者の希望シフト（本日以降）
		$dao_user_request_shift = new DaoUserRequestShift();
		$user_request_shifts = $dao_user_request_shift->select_where_with_user(
			array(
				'urs.user_id' => $this->_login_user->id,
				'urs.shift_date_to' => $calendar_range_from,
			),
			array('shift_date_from' => 'ASC')
		);

		$model_location_assign_user_search = new ModelLocationAssignUserSearch();
		$model_location_assign_user_search->set_code($this->_login_user->code);
		$model_location_assign_user_search->set_shift_date_from($calendar_range_from);
		$model_location_assign_user_search->set_shift_date_to($calendar_range_to);
		$model_location_assign_user_search->set_sort_key("ls.shift_date_from");
		$model_location_assign_user_search->set_sort_type(PARAM_CONST_LIST_SORT_ASC);
		$model_location_assign_user_search->search();
		$confirmed_shifts = $model_location_assign_user_search->get();

		$shift_calendar_map = array();
		foreach ((array)$user_request_shifts as $user_request_shift) {
			$from = strtotime($user_request_shift->shift_date_from);
			$to = strtotime($user_request_shift->shift_date_to);
			$date_key = date('Y-m-d', $from);
			$time_label = date('H', $from) . ' - ' . date('H', $to);

			$shift_calendar_map[$date_key][] = array(
				'id' => $user_request_shift->id,
				'label' => $time_label,
				'start_hour' => (int)date('G', $from),
				'end_hour' => (int)date('G', $to),
				'type' => 'request',
			);
		}

		foreach ((array)$confirmed_shifts as $confirmed_shift) {
			$from = strtotime($confirmed_shift->shift_date_from);
			$to = strtotime($confirmed_shift->shift_date_to);
			$date_key = date('Y-m-d', $from);
			$time_label = date('H', $from) . ' - ' . date('H', $to);

			$shift_calendar_map[$date_key][] = array(
				'id' => null,
				'label' => $time_label,
				'start_hour' => (int)date('G', $from),
				'end_hour' => (int)date('G', $to),
				'type' => 'confirmed',
			);
		}

		$calendar_months = array();
		$base_month = strtotime(date('Y-m-01'));
		for ($month_index = 0; $month_index < 3; $month_index++) {

			$month_start = strtotime(date('Y-m-01', strtotime('+' . $month_index . ' month', $base_month)));
			$month_end = strtotime(date('Y-m-t', $month_start));

			$start_week = (int)date('w', $month_start);
			$end_week = (int)date('w', $month_end);

			$grid_start = strtotime('-' . $start_week . ' day', $month_start);
			$grid_end = strtotime('+' . (6 - $end_week) . ' day', $month_end);

			$weeks = array();
			$week = array();
			for ($cursor = $grid_start; $cursor <= $grid_end; $cursor = strtotime('+1 day', $cursor)) {

				$date_key = date('Y-m-d', $cursor);
				$cell_week = (int)date('w', $cursor);

				$week[] = array(
					'date_key' => $date_key,
					'day' => date('j', $cursor),
					'in_month' => date('Ym', $cursor) == date('Ym', $month_start),
					'is_sunday' => $cell_week === 0,
					'is_saturday' => $cell_week === 6,
					'shifts' => isset($shift_calendar_map[$date_key]) ? $shift_calendar_map[$date_key] : array(),
				);

				if (count($week) === 7) {
					$weeks[] = $week;
					$week = array();
				}
			}

			$calendar_months[] = array(
				'month_label' => date('Y年n月', $month_start),
				'weeks' => $weeks,
			);
		}

		$this->_view->assign('user_request_shifts', $user_request_shifts);
		$this->_view->assign('request_shift_calendar_months', $calendar_months);

	}

	/**
	 * 希望シフト登録フォームアクション
	 *
	 */
	public function requestShiftAddFormAction() {

	}

	/**
	 * 作業予定登録完了アクション
	 *
	 */
	public function requestShiftAddFinishAction() {

		$shift_date_from = $this->_request->getPost('shift_date_from');
		$shift_time_from = $this->_request->getPost('shift_time_from');
		$shift_date_to = $this->_request->getPost('shift_date_to');
		$shift_time_to = $this->_request->getPost('shift_time_to');

		$error_messages = array();

		try {

			// 希望日（開始） : 未入力チェック
			if (UtilCommon::is_empty($shift_date_from)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_EMPTY_EN, 'Desired date (start)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_EMPTY, '希望日時（開始）');
						break;
				}
			}

			// 希望日（開始） : フォーマットチェック
			if (!UtilCommon::is_date($shift_date_from)){
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_INPUT_EN, 'Desired date (start)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_INPUT, '希望日時（開始）');
						break;
				}
			}

			// 希望時間（開始） : 未入力チェック
			if (UtilCommon::is_empty($shift_time_from)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_EMPTY_EN, 'Desired time (start)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_EMPTY, '希望時間（開始）');
						break;
				}
			}

			// 希望日（終了） : 未入力チェック
			if (UtilCommon::is_empty($shift_date_to)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_EMPTY_EN, 'Desired date (end)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_EMPTY, '希望日時（終了）');
						break;
				}
			}

			// 希望日（終了） : フォーマットチェック
			if (!UtilCommon::is_date($shift_date_to)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_INPUT_EN, 'Desired date (end)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_from'] = sprintf(ERR_MSG_INPUT, '希望日時（終了）');
						break;
				}
			}

			// 希望時間（終了） : 未入力チェック
			if (UtilCommon::is_empty($shift_time_from)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_EMPTY_EN, 'Desired time (end)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_EMPTY, '希望日時（終了）');
						break;
				}
			}

			// 希望日時 : 日時逆転チェック
			$shift_date_from = $shift_date_from . ' ' . $shift_time_from . ':00';
			$shift_date_to = $shift_date_to . ' ' . $shift_time_to . ':00';
			if (strtotime($shift_date_from) > strtotime($shift_date_to)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_DATE_REVERSE_EN, 'desired start date', 'desired end date');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_DATE_REVERSE, '希望日時（開始）', '希望日時（終了）');
						break;
				}				
			}

			// 希望日 : 登録済みチェック
			$dao_user_request_shift	= new DaoUserRequestShift();
			$user_request_shift = $dao_user_request_shift->select_for_duplicate_check(
				array(
					'user_id' => $this->_login_user->id,
					'shift_date_from' => $shift_date_to,  // 意図的にfromとtoを逆にする
					'shift_date_to' => $shift_date_from  // 意図的にfromとtoを逆にする
				)
			);

			if ($user_request_shift) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_DUPLICATE_EN, 'Desired date');
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['shift_date_to'] = sprintf(ERR_MSG_DUPLICATE, '希望日時');
						break;
				}	
				
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$entity_user_request_shift = new EntityUserRequestShift();
			unset($entity_user_request_shift->id);
			$entity_user_request_shift->user_id = $this->_login_user->id;
			$entity_user_request_shift->shift_date_from = $shift_date_from;
			$entity_user_request_shift->shift_date_to = $shift_date_to;
			if (!$dao_user_request_shift->insert($entity_user_request_shift))
				throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('requestShiftAddForm');

		}	

	}

	/**
	 * 作業予定削除完了アクション
	 *
	 */
	public function requestShiftDelFinishAction() {

		$id = $this->_request->getQuery('id');

		// ID : 必須入力チェック
		if (!$id)
			throw new Exception(ERR_MSG_RETRY, __LINE__);

		// 存在チェック & 自身の希望シフトチェック
		$dao_user_request_shift	= new DaoUserRequestShift();
		$user_request_shift = $dao_user_request_shift->select_by_key($id);
		if (!$user_request_shift || $user_request_shift->user_id != $this->_login_user->id)
			throw new Exception(sprintf(ERR_MSG_NOT_FOUND, '希望シフト'), __LINE__);

		// 削除
		if (!$dao_user_request_shift->delete(array('id' => $id)))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

	}

	public function executeDebugQueryAction() {

		$executed_at = date('Y-m-d H:i:s');
		$result = array(
			'ok' => false,
			'executedAt' => $executed_at,
			'output' => '',
		);

		$query = '';

		try {
			$query = $this->normalize_debug_query($this->_request->getPost('query'));
			if ($query === '') {
				throw new Exception('Query is empty.');
			}

			if (false !== strpos($query, ';')) {
				throw new Exception('Only one query can be executed at a time.');
			}

			$query_type = $this->get_debug_query_type($query);
			if ($query_type === '') {
				throw new Exception('Only SELECT/SHOW TABLES/INSERT/UPDATE/DELETE queries are supported.');
			}

			$dao_debug_query = new DaoDebugQuery();

			if ($query_type === 'select') {
				$rows = $dao_debug_query->execute_raw_select($query);
				$result['output'] = $this->format_ascii_table($rows);
			} else {
				$affected_rows = $dao_debug_query->execute_raw_mutation($query);
				$result['output'] = $affected_rows . ' rows affected.';
			}

			$result['ok'] = true;
		} catch (Exception $e) {
			$result['output'] = $e->getMessage();
		}

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
		exit;
	}

	/**
	 * 確定作業一覧アクション
	 *
	 */
	public function resultListAction() {

		$model_location_assign_user_search = new ModelLocationAssignUserSearch();
		$model_location_assign_user_search->set_code($this->_login_user->code);
		$model_location_assign_user_search->set_shift_date_from(date("Y-m-d 00:00:00"));
		$model_location_assign_user_search->set_limit(self::RESULT_LIST_LIMIT);
		$model_location_assign_user_search->set_sort_key("ls.shift_date_from");
		$model_location_assign_user_search->set_sort_type(PARAM_CONST_LIST_SORT_ASC);
		$model_location_assign_user_search->search();
		$location_assign_users = $model_location_assign_user_search->get();

		$this->_view->assign('location_assign_users', $location_assign_users);
		$this->_view->assign('result_list_limit', self::RESULT_LIST_LIMIT);
	}

	/**
	 * 会員情報詳細アクション
	 *
	 */
	public function detailAction() {

		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get($this->_login_user->id);

		$this->_view->assign('user', $user);
	}

	/**
	 * メールアドレス変更フォームアクション
	 *
	 */
	public function editEmailFormAction() {}

	/**
	 * メールアドレス変更確認アクション
	 *
	 */
	public function editEmailConfirmAction() {

		$request = $this->_request->getPost();
		$email = (isset($request['email'])) ? $request['email'] : null;
		$re_email = (isset($request['re_email'])) ? $request['re_email'] : null;

		$error_messages = array();

		try {

			// メールアドレス : 未入力チェック
			if (UtilCommon::is_empty($email)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['email'] = sprintf(ERR_MSG_EMPTY_EN, 'Email');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス');
						break;
				}
			}

			// メールアドレス : 文字数チェック
			if (!UtilCommon::is_length($email, self::EMAIL_LENGTH)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['email'] = sprintf(ERR_MSG_LENTGTH_EN, 'Email', self::EMAIL_LENGTH);
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['email'] = sprintf(ERR_MSG_LENTGTH, 'メールアドレス', self::EMAIL_LENGTH);
						break;
				}
			}

			// メールアドレス : 重複チェック
			$dao_user = new DaoUser();
			$user = $dao_user->select_where_one(array('email' => $email));
			if ($email && $user) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['email'] = sprintf(ERR_MSG_USED_YET_EN, 'メールアドレス');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['email'] = sprintf(ERR_MSG_USED_YET, 'メールアドレス');
						break;
				}
			}

			// メールアドレス（確認用） : 未入力チェック
			if (UtilCommon::is_empty($re_email)){
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_email'] = sprintf(ERR_MSG_EMPTY_EN, 'Re-enter Email');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_email'] = sprintf(ERR_MSG_EMPTY, 'メールアドレス（確認用）');	
						break;
				}
			}

			// メールアドレス（確認用） : 文字数チェック
			if (!UtilCommon::is_length($re_email, self::EMAIL_LENGTH)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_email'] = sprintf(ERR_MSG_LENTGTH_EN, 'Re-enter Email', self::EMAIL_LENGTH);
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_email'] = sprintf(ERR_MSG_LENTGTH, 'メールアドレス（確認用）', self::EMAIL_LENGTH);
						break;
				}
			}

			// メールアドレス（確認用） : 不一致チェック
			if ($email && $email != $re_email) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_email'] = sprintf(ERR_MSG_NO_SAME_EN, 'Email', 'Re-enter Email');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_email'] = sprintf(ERR_MSG_NO_SAME, 'メールアドレス', 'メールアドレス（確認用）');
						break;
				}	
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$input_data['email'] = $email;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

			$this->_view->assign('input_data', $input_data);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('editEmailForm');

		}	

	}

	/**
	 * メールアドレス変更完了アクション
	 *
	 */
	public function editEmailFinishAction() {

		// セッションから更新値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		// メールアドレスを更新
		$sets['email'] = $input_data['email'];
		$wheres['id'] = $this->_login_user->id;
		$dao_user = new DaoUser();
		if (!$dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		// セッションを書き換える
		$this->_login_user->email = $input_data['email'];
		$model_session->set('user', $this->_login_user);

		$model_session->close();

	}

	/**
	 * パスワード変更フォームアクション
	 *
	 */
	public function editPasswordFormAction() {}

	/**
	 * パスワード変更確認アクション
	 *
	 */
	public function editPasswordConfirmAction() {

		$request = $this->_request->getPost();
		$password = (isset($request['password'])) ? $request['password'] : null;
		$re_password = (isset($request['re_password'])) ? $request['re_password'] : null;

		$error_messages = array();

		try {

			// パスワード : 未入力チェック
			if (UtilCommon::is_empty($password)){
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['password'] = sprintf(ERR_MSG_EMPTY_EN, 'Password');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['password'] = sprintf(ERR_MSG_EMPTY, 'パスワード');
						break;
				}		
			}

			// パスワード : 文字数チェック
			if (!UtilCommon::is_length($password, self::PASSWORD_LENGTH)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['password'] = sprintf(ERR_MSG_LENTGTH_EN, 'Password', self::PASSWORD_LENGTH);
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード', self::PASSWORD_LENGTH);
						break;
				}		
			}

			// パスワード（確認用） : 未入力チェック
			if (UtilCommon::is_empty($re_password)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY_EN, 'Re-enter Password');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_password'] = sprintf(ERR_MSG_EMPTY, 'パスワード（確認用）');	
						break;
				}		
			}

			// パスワード（確認用） : 文字数チェック
			if (!UtilCommon::is_length($re_password, self::PASSWORD_LENGTH)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH_EN, 'Re-enter Password', self::PASSWORD_LENGTH);
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_password'] = sprintf(ERR_MSG_LENTGTH, 'パスワード（確認用）', self::PASSWORD_LENGTH);
						break;
				}	
			}

			// パスワード（確認用） : 不一致チェック
			if ($password != $re_password) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME_EN, 'Password', 'Re-enter Password');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['re_password'] = sprintf(ERR_MSG_NO_SAME, 'パスワード', 'パスワード（確認用）');
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$input_data['password'] = $password;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

		} catch (Exception $e) {

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('editPasswordForm');

		}	

	}

	/**
	 * パスワード変更完了アクション
	 *
	 */
	public function editPasswordFinishAction() {

		// セッションから更新値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');
		$model_session->close();

		// パスワード更新
		$sets['password'] = UtilCommon::to_hash_password($input_data['password']);
		$wheres['id'] = $this->_login_user->id;
		$dao_user = new DaoUser();
		if (!$dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

	}

	/**
	 * 従事者情報変更フォームアクション
	 *
	 */
	public function editAccountFormAction() {

		// 作業者情報
		$model_user = new ModelUser($this->_login_user->id);
		$user = $model_user->get();

		// 性別種別
		$gender_types = UtilCommon::get_gender_types($this->_login_user->lang_type);

		// 雇用形態種別
		$employ_types = UtilCommon::get_employ_types($this->_login_user->lang_type);

		// 国籍マスタ
		$dao_country = new DaoCountry();
		$countries = $dao_country->select_all();

		// 所属会社マスタ
		$dao_company = new DaoCompany();
		$companies = $dao_company->select_all();

		// 言語種別
		$lang_types = UtilCommon::get_lang_types($this->_login_user->lang_type);

		$this->_view->assign('user', $user);
		$this->_view->assign('gender_types', $gender_types);
		$this->_view->assign('employ_types', $employ_types);
		$this->_view->assign('countries', $countries);
		$this->_view->assign('companies', $companies);
		$this->_view->assign('lang_types', $lang_types);

	}

	/**
	 * 従事者情報変更確認アクション
	 *
	 */
	public function editAccountConfirmAction() {

		$name = $this->_request->getPost('name');
		$name_kana = $this->_request->getPost('name_kana');
		$tel = $this->_request->getPost('tel');
		$post_code = $this->_request->getPost('post_code');
		$address = $this->_request->getPost('address');
		$gender_type = $this->_request->getPost('gender_type');
		$birth_day = $this->_request->getPost('birth_day');
		$country_id = $this->_request->getPost('country_id');
		$employ_type = $this->_request->getPost('employ_type');
		$company_id = $this->_request->getPost('company_id');
		$etc_company_name = $this->_request->getPost('etc_company_name');
		$lang_type = $this->_request->getPost('lang_type');

		$error_messages = array();

		try {

			// 氏名 : 未入力チェック
			if (UtilCommon::is_empty($name)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name'] = sprintf(ERR_MSG_EMPTY_EN, 'Name');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name'] = sprintf(ERR_MSG_EMPTY, '氏名');
						break;
				}
			}

			// 氏名 : 文字数チェック
			if (!UtilCommon::is_length($name, self::NAME_LENGTH)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name'] = sprintf(ERR_MSG_LENTGTH_EN, 'Name', self::NAME_LENGTH);
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name'] = sprintf(ERR_MSG_LENTGTH, '氏名', self::NAME_LENGTH);
						break;
				}
			}

			// 氏名（ふりがな） : 未入力チェック
			// if (UtilCommon::is_empty($name_kana)) {
			// 	switch ($this->_login_user->lang_type) {
			// 		case PARAM_CONST_LANG_TYPE_EN:
			// 			$error_messages['name_kana'] = sprintf(ERR_MSG_EMPTY_EN, 'Frigana');
			// 			break;
			// 		case PARAM_CONST_LANG_TYPE_JP:
			// 		default:
			// 			$error_messages['name_kana'] = sprintf(ERR_MSG_EMPTY, '氏名（ふりがな）');
			// 			break;
			// 	}
			// }

			// 氏名（ふりがな） : 文字数チェック
			if ($name_kana && !UtilCommon::is_length($name_kana, self::NAME_KANA_LENGTH)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name_kana'] = sprintf(ERR_MSG_LENTGTH_EN, 'Frigana', self::NAME_KANA_LENGTH);
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name_kana'] = sprintf(ERR_MSG_LENTGTH, '氏名（ふりがな）', self::NAME_KANA_LENGTH);
						break;
				}
			}

			// 氏名（ふりがな） : ひらがな&カタカナ&アルファベットチェック
			if ($name_kana && !UtilCommon::is_hira_kata_alpha($name_kana)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['name_kana'] = sprintf(ERR_MSG_HIRAGANA_EN, 'Frigana');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['name_kana'] = sprintf(ERR_MSG_HIRAGANA, '氏名（ふりがな）');
						break;
				}
			}

			// 電話番号 : 未入力チェック
			if (UtilCommon::is_empty($tel)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['tel'] = sprintf(ERR_MSG_EMPTY_EN, 'Telephone number');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['tel'] = sprintf(ERR_MSG_EMPTY, '電話番号');
						break;
				}
			}

			// 電話番号 : 文字数チェック
			if (!UtilCommon::is_length($tel, self::TEL_LENGTH)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['tel'] = sprintf(ERR_MSG_LENTGTH_EN, 'Telephone number', self::TEL_LENGTH);
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['tel'] = sprintf(ERR_MSG_LENTGTH, '電話番号', self::TEL_LENGTH);
						break;
				}
			}

			// 電話番号 : 数値チェック
			if (!UtilCommon::is_num($tel)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['tel'] = sprintf(ERR_MSG_NUM_EN, 'Telephone number');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['tel'] = sprintf(ERR_MSG_NUM, '電話番号');
						break;
				}
			}

			// 郵便番号
			if (UtilCommon::is_empty($post_code)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['post_code'] = sprintf(ERR_MSG_EMPTY_EN, 'Post code');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['post_code'] = sprintf(ERR_MSG_EMPTY, '郵便番号');	
						break;
				}
			}

			// 住所
			if (UtilCommon::is_empty($address)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['address'] = sprintf(ERR_MSG_EMPTY_EN, 'Address');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['address'] = sprintf(ERR_MSG_EMPTY, '住所');		
						break;
				}
			}

			// 性別 : 未入力チェック
			if (UtilCommon::is_empty($gender_type)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['gender_type'] = sprintf(ERR_MSG_EMPTY_EN, 'Sex');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['gender_type'] = sprintf(ERR_MSG_EMPTY, '性別');		
						break;
				}
			}

			// 性別 : 規定値外入力チェック
			if (!isset(PARAM_CONST_GENDER_TYPES[$gender_type])) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['gender_type'] = sprintf(ERR_MSG_INPUT_EN, 'Sex');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['gender_type'] = sprintf(ERR_MSG_INPUT, '性別');
						break;
				}
			}

			// 生年月日 : 未入力チェック
			if (UtilCommon::is_empty($birth_day)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['birth_day'] = sprintf(ERR_MSG_EMPTY_EN, 'Birthday');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['birth_day'] = sprintf(ERR_MSG_EMPTY, '生年月日');
						break;
				}
			}

			// 生年月日 : 未来日チェック
			if ($birth_day > date('Y-m-d')) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['birth_day'] = sprintf(ERR_MSG_INVALID_EN, 'Birthday');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['birth_day'] = sprintf(ERR_MSG_INVALID, '生年月日');
						break;
				}
			}

			// 国籍 : 未入力チェック
			if (!strlen($country_id)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['country_id'] = sprintf(ERR_MSG_EMPTY_EN, 'Nationality');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['country_id'] = sprintf(ERR_MSG_EMPTY, '国籍');
						break;
				}
			}

			// 国籍 : 規定値外入力チェック
			$dao_country = new DaoCountry();
			$country = $dao_country->select_by_key($country_id);
			if (!$country) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['country_id'] = sprintf(ERR_MSG_INPUT_EN, 'Nationality');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['country_id'] = sprintf(ERR_MSG_INPUT, '国籍');	
						break;
				}
			}

			// 雇用形態 : 未入力チェック
			if (!strlen($employ_type)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['employ_type'] = sprintf(ERR_MSG_EMPTY_EN, 'Employment type');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['employ_type'] = sprintf(ERR_MSG_EMPTY, '雇用形態');
						break;
				}
			}

			// 雇用形態 : 規定値外入力チェック
			if (!isset(PARAM_CONST_EMPLOY_TYPES[$employ_type])) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['employ_type'] = sprintf(ERR_MSG_INPUT_EN, '雇用形態');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['employ_type'] = sprintf(ERR_MSG_INPUT, '雇用形態');
						break;
				}
			}

			// 所属会社
			if (!mb_strlen($company_id)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['company_id'] = sprintf(ERR_MSG_EMPTY_EN, 'Affiliated company');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['company_id'] = sprintf(ERR_MSG_EMPTY, '所属会社');	
						break;
				}
			}

			// 所属会社 : 規定値外入力チェック
			$dao_company = new DaoCompany();
			$company = $dao_company->select_by_key($company_id);
			if (!$company_id && !$company && UtilCommon::is_empty($etc_company_name)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['company_id'] = sprintf(ERR_MSG_INPUT_EN, 'Affiliated company');	
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['company_id'] = sprintf(ERR_MSG_INPUT, '所属会社');	
						break;
				}
			}

			// 所属会社（その他）
			if (!$company_id && UtilCommon::is_empty($etc_company_name)) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['etc_company_name'] = sprintf(ERR_MSG_EMPTY_EN, 'Affiliated company (other)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['etc_company_name'] = sprintf(ERR_MSG_EMPTY, '所属会社（その他）');
						break;
				}
			}

			// 言語 : 規定値外入力チェック
			if (!isset(PARAM_CONST_LANG_TYPES[$lang_type])) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['lang_type'] = sprintf(ERR_MSG_INPUT, 'Language');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['lang_type'] = sprintf(ERR_MSG_INPUT, '言語');
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$input_data['name'] = $name;
			$input_data['name_kana'] = $name_kana;
			$input_data['tel'] = $tel;
			$input_data['post_code'] = $post_code;
			$input_data['address'] = $address;
			$input_data['gender_type'] = $gender_type;
			$input_data['birth_day'] = $birth_day;
			$input_data['country_id'] = $country_id;
			$input_data['employ_type'] = $employ_type;
			$input_data['company_id'] = $company_id;
			$input_data['gender_type_name'] = PARAM_CONST_GENDER_TYPES[$gender_type];
			$input_data['country_name'] = $country->name;
			$input_data['employ_type_name'] = PARAM_CONST_EMPLOY_TYPES[$employ_type];
			$input_data['company_name'] = isset($company) ? $company->name : null;
			$input_data['etc_company_name'] = $etc_company_name;
			$input_data['lang_type'] = $lang_type;

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->set('input_data', $input_data);

			// $model_session->close();

			$this->_view->assign('input_data', $input_data);
			$this->_view->assign('gender_type_name', UtilCommon::get_gender_name($gender_type, $this->_login_user->lang_type));
			$this->_view->assign('employ_type_name', UtilCommon::get_gender_name($employ_type, $this->_login_user->lang_type));
			$this->_view->assign('country', $country);
			$this->_view->assign('company', $company);
			$this->_view->assign('lang_type_name', UtilCommon::get_lang_name($lang_type, $this->_login_user->lang_type));

		} catch (Exception $e) {

			// 作業者情報
			if (!isset($model_user))
				$model_user = new ModelUser($this->_login_user->id);

			$this->_view->assign('user', $model_user->get());

			// 国籍マスタ
			if (isset($dao_country))
				$dao_country = new DaoCountry();
			
			$this->_view->assign('countries', $dao_country->select_all());

			// 所属会社マスタ
			if (isset($dao_company))
				$dao_company = new DaoCompany();

			$this->_view->assign('companies', $dao_company->select_all());

			
			$this->_view->assign('gender_types', PARAM_CONST_GENDER_TYPES);
			$this->_view->assign('employ_types', PARAM_CONST_EMPLOY_TYPES);
			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('editAccountForm');

		}	

	}

	/**
	 * 従事者情報変更完了アクション
	 *
	 */
	public function editAccountFinishAction() {

		// セッションから更新値を取得
		$model_session = new ModelSession();
		$model_session->set_dir(SESSION_DIR);
		$model_session->open();
		$input_data = $model_session->get('input_data');

		// 会員情報を更新
		$sets['name'] = $input_data['name'];
		$sets['name_kana'] = $input_data['name_kana'];
		$sets['tel'] = $input_data['tel'];
		$sets['post_code'] = $input_data['post_code'];
		$sets['address'] = $input_data['address'];
		$sets['gender_type'] = $input_data['gender_type'];
		$sets['birth_day'] = $input_data['birth_day'];
		$sets['country_id'] = $input_data['country_id'];
		$sets['employ_type'] = $input_data['employ_type'];
		$sets['company_id'] = $input_data['company_id'];
		$sets['etc_company_name'] = $input_data['etc_company_name'];
		$sets['lang_type'] = $input_data['lang_type'];
		$wheres['id'] = $this->_login_user->id;
		$dao_user = new DaoUser();
		if (!$dao_user->update($sets, $wheres))
			throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

		// セッションを書き換える
		$this->_login_user->name = $input_data['name'];
		$this->_login_user->name_kana = $input_data['name_kana'];
		$this->_login_user->tel = $input_data['tel'];
		$this->_login_user->post_code = $input_data['post_code'];
		$this->_login_user->address = $input_data['address'];
		$this->_login_user->gender_type = $input_data['gender_type'];
		$this->_login_user->birth_day = $input_data['birth_day'];
		$this->_login_user->country_id = $input_data['country_id'];
		$this->_login_user->employ_type = $input_data['employ_type'];
		$this->_login_user->company_id = $input_data['company_id'];
		$this->_login_user->etc_company_name = $input_data['etc_company_name'];
		$this->_login_user->lang_type = $input_data['lang_type'];
		$model_session->set('user', $this->_login_user);

		$model_session->close();

	}

	/**
	 * 資格者証アップロードフォームアクション
	 *
	 */
	public function uploadCertFormAction() {

		// 提出済み書類情報
		$dao_user_cert = new DaoUserCert();
		$user_certs = $dao_user_cert->select_where(array('user_id' => $this->_login_user->id));

		// 資格者証マスタ
		$dao_cert = new DaoCert();
		$certs = $dao_cert->select_all();

		foreach ((array)$certs as $key => $cert) {

			$certs[$key]->is_uploaded = false;

			foreach ((array)$user_certs as $user_cert) {

				// 既にアップロード済みの資格証の場合は、アップロードできないようにする
				if ($cert->id == $user_cert->cert_id) {

					$certs[$key]->is_uploaded = true;

				}

			}

		}

		$this->_view->assign('certs', $certs);

	}

	/**
	 * 資格者証アップロード確認アクション
	 *
	 */
	public function uploadCertConfirmAction() {

		$requests = $this->_request->getFiles();
		$files = array();

		try {

			$error_messages = array();

			if ($requests) {

				foreach ($requests as $key => $request) {

					switch ($request['error']) {

						case 4: // ファイルはアップロードされませんでした。
							// アップロード無しも該当するので正常系とする
							break;

						case 1: // アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
						case 2: // アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
						case 3: // アップロードされたファイルは一部のみしかアップロードされていません。
						case 6: // テンポラリフォルダがありません。
						case 7: // ディスクへの書き込みに失敗しました。
						case 8: // PHP の拡張モジュールがファイルのアップロードを中止しました
							$error_messages['file'] = sprintf(ERR_MSG_UPLOAD_FILE_FAILED, $request['error']);
							break;

						default:
							// 正常系
							break;
					}

					if (!$request['size'])
						continue;

					$keys = explode('_', $key);
					$request['cert_id'] = $keys[1];
					$request['cert_type'] = $keys[2];
					$files[] = $request;

				}

			}

			if (!count($files) && !$error_messages)
				$error_messages['file'] = sprintf(ERR_MSG_RQUIRED, 'いずれかのファイルアップロード');		

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new Exception("Error Processing Request", 1);	

			$upload_dir = SECURE_DIR . '/temp';
			$key = time() . rand();
			$upload_certs = array();

			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();

			foreach ($files as $file) {
				$upload_cert['cert_id'] = $file['cert_id'];
				$upload_cert['cert_type'] = $file['cert_type'];
				$secure_temp_name = sprintf("%s_%s_%s.%s", $key, $file['cert_id'], $file['cert_type'], UtilFile::get_extension($file['name']));
				$upload_cert['secure_temp_name'] = $secure_temp_name;
				$upload_cert['type'] = exif_imagetype($file['tmp_name']);
				$upload_cert['data'] = file_get_contents($file['tmp_name']);
				$upload_certs[] = $upload_cert;
				$model_session->set('upload_certs', $upload_certs);

				UtilFile::upload_file($file['tmp_name'], $secure_temp_name, $upload_dir);				
			}

			// 資格者証マスタ
			$dao_cert = new DaoCert();
			$certs = $dao_cert->select_all();

			foreach ($files as $key => $file) {

				foreach ($certs as $cert) {
					
					if ($file['cert_id'] == $cert->id) {

						switch ($this->_login_user->lang_type) {
							case PARAM_CONST_LANG_TYPE_EN:
								$files[$key]['cert_name'] = sprintf("%s（%s）", $cert->name, PARAM_CONST_EN_CERT_TYPES[$file['cert_type']]);
								break;
							case PARAM_CONST_LANG_TYPE_JP:
							default:
								$files[$key]['cert_name'] = sprintf("%s（%s）", $cert->name, PARAM_CONST_CERT_TYPES[$file['cert_type']]);
								break;
						}

					}

				}

			}

			$model_session->set('files', $files);

			// $model_session->close();

			$this->_view->assign('files', $files);

		} catch (Exception $e) {

			// 提出済み資格証情報
			$dao_user_cert = new DaoUserCert();
			$user_certs = $dao_user_cert->select_where(array('user_id' => $this->_login_user->id));

			// 資格者証マスタ
			$dao_cert = new DaoCert();
			$certs = $dao_cert->select_all();

			foreach ((array)$certs as $key => $cert) {

				$certs[$key]->is_uploaded = false;
				
				foreach ((array)$user_certs as $user_cert) {

					// 既にアップロード済みの資格証の場合は、アップロードできないようにする
					if ($cert->id == $user_cert->cert_id) {

						$certs[$key]->is_uploaded = true;

					}

				}

			}

			$this->_view->assign('certs', $certs);		

			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('uploadCertForm');

		}

	}

	/**
	 * 資格者証アップロード完了アクション
	 *
	 */
	public function uploadCertFinishAction() {

		try {

			// セッションから登録値を取得
			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();

			// アカウント証明書ファイルの設置
			$upload_dir = CERT_UPLOAD_DIR . '/' . $this->_login_user->code;
			$upload_temp_dir = SECURE_DIR . '/temp';

			// ディレクトリ
			if (!is_dir($upload_dir))
				mkdir($upload_dir, 0777);

			$dao_user_cert = new DaoUserCert();
			$upload_certs = $model_session->get('upload_certs');
			if ($upload_certs) {

				// 資格証アップロード済みチェック
				foreach ($upload_certs as $upload_cert) {
					
					$file_secure_temp_name = $upload_temp_dir . '/' . $upload_cert['secure_temp_name'];

					// 00000000000_00_0.png
					$file = sprintf("%s_%s_%s.%s", $this->_login_user->code, sprintf('%02d', $upload_cert['cert_id']), $upload_cert['cert_type'], UtilFile::get_extension($file_secure_temp_name));

					$file_name = $upload_dir . '/' . $file;
					if (file_exists($file_name))
						throw new Exception(sprintf("ERR_MSG_UPLOADED", "資格証"), 1);	

				}

				// 資格証アップロード & データ登録
				foreach ($upload_certs as $upload_cert) {

					$file_secure_temp_name = $upload_temp_dir . '/' . $upload_cert['secure_temp_name'];

					// 00000000000_00_0.png
					$file = sprintf("%s_%s_%s.%s", $this->_login_user->code, sprintf('%02d', $upload_cert['cert_id']), $upload_cert['cert_type'], UtilFile::get_extension($file_secure_temp_name));
					$file_name = $upload_dir . '/' . $file;
					rename($file_secure_temp_name, $file_name);

					// アカウント証明書登録
					$entity_user_cert = new EntityUserCert();
					$entity_user_cert->id = null;
					$entity_user_cert->user_id = $this->_login_user->id;
					$entity_user_cert->cert_id = $upload_cert['cert_id'];
					$entity_user_cert->cert_type = $upload_cert['cert_type'];
					$entity_user_cert->file_name = $file;
					$entity_user_cert->is_approved = 0;
					$entity_user_cert->comment = null;
					if (!$un_cert_id =$dao_user_cert->insert($entity_user_cert))
						throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

				}

			}
			
			// $model_session->close();

			// TODO : 管理者へメールを送信
			// 送信内容と宛先を確認する

		} catch (Exception $e) {

			echo $e->getMessage();

		}

	}

	/**
	 * 退会確認アクション
	 *
	 */
	public function resignFormAction() {

		$model_user = new ModelUser($this->_login_user->id);
		$this->_view->assign('user', $model_user->get());

	}

	/**
	 * 退会完了アクション
	 *
	 */
	public function resignFinishAction() {

		$is_resign = $this->_request->getPost('is_resign');

		$error_messages = array();

		try {

			if (!$is_resign) {
				switch ($this->_login_user->lang_type) {
					case PARAM_CONST_LANG_TYPE_EN:
						$error_messages['is_resign'] = sprintf(ERR_MSG_CONSENT_EN, 'Non-Disclosure Agreement (NDA)');
						break;
					case PARAM_CONST_LANG_TYPE_JP:
					default:
						$error_messages['is_resign'] = sprintf(ERR_MSG_CONSENT, '秘密保持契約書（NDA）');
						break;
				}
			}

			// エラーがあった場合、前画面へ戻す
			if ($error_messages)
				throw new ValidationException("Error Processing Request", 1);				

			// アカウント証明書ファイルを全削除
			$dao_user_cert = new DaoUserCert();
			$dao_user_cert->begin();

			// ユーザ情報を取得
			$dao_user = new DaoUser();
			$user = $dao_user->select_by_key($this->_login_user->id);

			$upload_dir = CERT_UPLOAD_DIR . '/' . $user->code;
			$user_certs = $dao_user_cert->select_where(array('user_id' => $user->id));
			if ($user_certs) {

				// ファイルを個別に削除
				foreach ((array)$user_certs as $user_cert) {
					
					$cert_file = $upload_dir . '/' . $user_cert->file_name;
					if (is_file($cert_file))
						unlink($cert_file);
				
				}

				// 証明書情報を削除
				if (false === $dao_user_cert->delete(array('user_id' => $user->id)))
					throw new Exception(ERR_MSG_DB_ERROR, __LINE__);

			}

			// 証明書ディレクトリ削除
			if (is_dir($upload_dir))
				rmdir($upload_dir);

			// 個人情報に関わる会員情報のみ論理削除
			$dao_user = new DaoUser();
			$sets['status_type'] = PARAM_CONST_USER_STATUS_TYPE_DELETE;
			$sets['name'] = "";
			$sets['name_kana'] = "";
			$sets['tel'] = "";
			$sets['email'] = "";
			$sets['password'] = "";
			$sets['post_code'] = "";
			$sets['address'] = "";
			$sets['birth_day'] = "2099-01-01";
			$wheres['id'] = $user->id;
			if (false === $dao_user->update($sets, $wheres))
				throw new Exception("Error Processing Request", 1);

			$dao_user_cert->commit();

			// 退会完了メールを送信する
			$mail_params['name'] = $user->name;

			$model_mail = new ModelMail();
			$model_mail->set_from(NOTICE_EMAIL);
			$model_mail->set_replyto(NOTICE_EMAIL);
			switch ($this->_login_user->lang_type) {
				case PARAM_CONST_LANG_TYPE_EN:
					$model_mail->set_subject(MAIL_SUBJECT_RESIGN_FINISH_EN_);
					$model_mail->create_body('resign_finish_en', $mail_params);
					break;
				case PARAM_CONST_LANG_TYPE_JP:
				default:
					$model_mail->set_subject(MAIL_SUBJECT_RESIGN_FINISH);
					$model_mail->create_body('resign_finish', $mail_params);
					break;
			}
			$model_mail->set_address($user->email);
			$model_mail->send();
			unset($model_mail);

			// ログアウトする（セッションを全削除）
			$model_session = new ModelSession();
			$model_session->set_dir(SESSION_DIR);
			$model_session->open();
			$model_session->destroy();

		} catch (ValidationException $ve) {

			$model_user = new ModelUser($this->_login_user->id);
			$this->_view->assign('user', $model_user->get());
			$this->_view->assign('error_messages', $error_messages);

			// フォームへ遷移
			$this->setAction('resignForm');

		} catch (Exception $e) {

			if (isset($dao_user_cert))
				$dao_user_cert->rollback();

			// エラー画面へ
			header('Location: ' . UtilCommon::get_base_url('Error'));
			exit;	

		} 

	}


}