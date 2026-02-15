<?php

/**
 * MODEL ページャ
 * 
 * @author kanemiya
 * 
 */

class ModelPager {

	private $_current_page; // 現在のページ

	private $_total_rec; // 総レコード数

	private $_page_rec; // 1ページに表示するレコード

	private $_show_nav; // 表示するナビゲーションの数

	private $_path = "?pg=";

	private $_link_tag;

	private $_query;


	/**
	 * コンストラクタ
	 * 
	 */
	function __construct() {

	}

	/**
	 * セッタ : 現在のページ
	 * 
	 * @param int $current_page 
	 */
	public function set_current_page($current_page = 0) {

		$this->_current_page = $current_page;

	}

	/**
	 * セッタ : 1ページに表示するレコード
	 * 
	 * @param int $current_page 
	 */
	public function set_page_rec($page_rec = 0) {

		$this->_page_rec = $page_rec;

	}

	/**
	 * セッタ : 総レコード数
	 * 
	 * @param int $total_rec 
	 */
	public function set_total_rec($total_rec = 0) {

		$this->_total_rec = $total_rec;

	}

	/**
	 * セッタ : 表示するナビゲーションの数
	 * 
	 * @param int $show_nav 
	 */
	public function set_show_nav($show_nav = 0) {

		$this->_show_nav = $show_nav;

	}

	/**
	 * セッタ : クエリパラメータ
	 * 
	 * @param string $key クエリ名
	 * @param string $value クエリ値
	 */
	public function set_query($key, $value) {

		// $this->_query = $this->_query ? . $this->_query . '&' . $key . '=' . $value : $key . '=' . $value;
		$this->_query = $this->_query . '&' . $key . '=' . $value;

	}

	/**
	 * セッタ : クエリ
	 * 
	 * @param int $show_nav 
	 */
	public function set_path($path) {

		$this->_path = $path . $this->_path;

	}

	/**
	 * ページャリンクを返す
	 *
	 */
	public function put_link_tag() {

		return $this->_link_tag;

	}

	/**
	 * ページャを作成
	 * 
	 */
	public function create() {

		// 総ページ数
		$total_page = ceil($this->_total_rec / $this->_page_rec);

		// 全てのページ数が表示するページ数より小さい場合、総ページを表示する数にする
		if ($total_page < $this->_show_nav)
			$this->_show_nav = $total_page;

		// トータルページ数が2以下か、現在のページが総ページより大きい場合表示しない
		if ($total_page <= 1 || $total_page < $this->_current_page)
			return;

		// 総ページの半分
		$show_navh = floor($this->_show_nav / 2);

		// 現在のページをナビゲーションの中心にする
		$loop_start = $this->_current_page - $show_navh;
		$loop_end = $this->_current_page + $show_navh;

		// 現在のページが両端だったら端にくるようにする
		if ($loop_start <= 0) {
			$loop_start = 1;
			$loop_end = $this->_show_nav;
		}

		if ($loop_end > $total_page) {
			$loop_start = $total_page - $this->_show_nav +1;
			$loop_end = $total_page;
		}

		$link_tag = '<div id="pagenation"><ul>';

		// 2ページ移行だったら「一番前へ」を表示
		if ($this->_current_page > 2)
			$link_tag .= '<li class="prev"><a href="' . $this->_path . '1' . $this->_query . '">&laquo;</a></li>';

		// 最初のページ以外だったら「前へ」を表示
		if ($this->_current_page > 1)
			$link_tag .= '<li class="prev"><a href="' . $this->_path . ($this->_current_page - 1)  . $this->_query . '">&lsaquo;</a></li>';


		for ($i = $loop_start; $i <= $loop_end; $i++) {

			if ($i > 0 && $total_page >= $i) {
 
				if($i == $this->_current_page) {

					$link_tag .= '<li class="active">';

				} else {

					$link_tag .= '<li>';

				}

				$link_tag .= '<a href="' . $this->_path . $i  . $this->_query . '">' . $i .'</a>';
				$link_tag .= '</li>';

			}

		}

		// 最後のページ以外だったら「次へ」を表示
		if ( $this->_current_page < $total_page)
			$link_tag .= '<li class="next"><a href="' . $this->_path . ($this->_current_page + 1)  . $this->_query . '">&rsaquo;</a></li>';

		// 最後から2ページ前だったら「一番最後へ」を表示
		if ($this->_current_page < $total_page - 1)
			$link_tag .= '<li class="next"><a href="' . $this->_path . $total_page  . $this->_query . '">&raquo;</a></li>';

		$link_tag .= "</ul></div>";

		$this->_link_tag = $link_tag;

	}


}