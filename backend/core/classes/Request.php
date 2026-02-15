<?php

class Request {

	// POSTパラメータ
	private $_post;

	// GETパラメータ
	private $_query;

	// REQUESTパラメータ
	private $_request_params;

	// URLパラメータ
	private $_param;

	// FILESパラメータ
	private $_files;

	// SERVERパラメータ
	private $_server;

	// コンストラクタ@
	public function __construct() {
	
		$this->_post = new Post();
		$this->_query = new QueryString();
		$this->_request_params = new RequestParams();
		$this->_param = new UrlParameter();
		$this->_files = new Files();
		$this->_server = new Server();
	
	}

	/**
	 * POST変数取得
	 *
	 */
	public function getPost($key = null) {

		if (null == $key)
			return $this->_post->get();

		if (false == $this->_post->has($key))
			return null;

		return $this->_post->get($key);

	}

	/**
	 * GET変数取得
	 *
	 */
	public function getQuery($key = null) {
		
		if (null == $key)
			return $this->_query->get();
	
		if (false == $this->_query->has($key))
			return null;

		return $this->_query->get($key);

	}

	/**
	 * REQUEST変数取得
	 *
	 */
	public function getRequest($key = null) {
		
		if (null == $key)
			return $this->_request_params->get();
	
		if (false == $this->_request_params->has($key))
			return null;

		return $this->_request_params->get($key);

	}

	/**
	 * URLパラメーター取得
	 *
	 */
	public function getParam($key = null) {
	
		if (null == $key)
			return $this->_param->get();
		
		if (false == $this->_param->has($key))
			return null;
		
		return $this->_param->get($key);

	}

	/**
	 * FILES変数取得
	 *
	 */
	public function getFiles($key = null) {
		
		if (null == $key)
			return $this->_files->get();
	
		if (false == $this->_files->has($key))
			return null;

		return $this->_files->get($key);

	}

	/**
	 * SERVER変数取得
	 *
	 */
	public function getServer($key = null) {
		
		if (null == $key)
			return $this->_server->get();
	
		if (false == $this->_server->has($key))
			return null;

		return $this->_server->get($key);

	}
}