<?php

/**
 * システムコンフィグ
 *
 * @author kanemiya
 */

define('ADMIN_WEB_SRC', 'https://admin-chiba-chikusan.gtuned.net'); // フロント側のsystemConstで定義

define('ADMIN_ROOT_DIR', '/home/users/1/lolipop.jp-dp34158915/web/chiba-chikusan/admin');

define('ADMIN_CONTROLLER_DIR', ADMIN_ROOT_DIR . '/controller');

define('ADMIN_SESSION_DIR', ADMIN_ROOT_DIR . '/session');

define('ADMIN_DTO_DIR', ADMIN_ROOT_DIR . '/dto');

define('ADMIN_UTIL_DIR', ADMIN_ROOT_DIR . '/util');

define('ADMIN_LIB_DIR', ADMIN_ROOT_DIR . '/lib');

define('ADMIN_LOG_DIR', ADMIN_ROOT_DIR . '/log');

define('ADMIN_CORE_DIR', ADMIN_ROOT_DIR . '/core');

define('ADMIN_VIEW_DIR', ADMIN_ROOT_DIR . '/view');

define('ADMIN_TMP_DIR', ADMIN_ROOT_DIR . '/tmp');

define('ADMIN_DATA_DIR', ADMIN_TMP_DIR . '/data');

define('ADMIN_MODEL_DIR', ADMIN_ROOT_DIR . '/model');

define('ADMIN_HTDOCS_DIR', ADMIN_ROOT_DIR . '/htdocs');

define('ADMIN_EXCEL_DIR', ADMIN_HTDOCS_DIR . '/excel');

define('ADMIN_CSS_SRC', ADMIN_WEB_SRC . '/css');

define('ADMIN_IMG_SRC', ADMIN_WEB_SRC . '/img');

define('ADMIN_JS_SRC', ADMIN_WEB_SRC . '/js');

define('ADMIN_RESOURCE_DIR', ADMIN_HTDOCS_DIR . '/resource');

define('ADMIN_DEFAULT_CONTROLLER', 'top');

define('ADMIN_DEFAULT_ACTION', 'index');

/* 
 * システムログインID
 *
 */
const ADMIN_LOGIN_ID = 'gtuser';

const ADMIN_LOGIN_USER_NAME = 'ユーザ';

/*
 * システム管理者アカウント
 *
 */

define('ADMIN_ROOT_LOGIN_ID', 9999);

define('ADMIN_ROOT_LOGIN_EMAIL', 'root');

define('ADMIN_ROOT_PASSWORD', 'root123');

define('ADMIN_ROOT_USER_NAME', 'システム管理者');

