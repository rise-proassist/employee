<?php

/**
 * システムコンフィグ
 *
 * @author kanemiya
 */

const DISPLAY_ERROR = 1; // エラー表示(0:非表示, 1:表示)

const WEB_SRC = 'https://chiba-chikusan.gtuned.net';

const ADMIN_SRC = 'https://admin-chiba-chikusan.gtuned.net';

const ROOT_DIR = '/home/users/1/lolipop.jp-dp34158915/web/chiba-chikusan';

const HTDOCS_DIR = ROOT_DIR . '/htdocs';

const CONTROLLER_DIR = ROOT_DIR . '/controller';

const DAO_DIR = ROOT_DIR . '/dao';

const ENTITY_DIR = ROOT_DIR . '/entity';
	
const MODEL_DIR = ROOT_DIR . '/model';

const DTO_DIR = ROOT_DIR . '/dto';

const SESSION_DIR = ROOT_DIR . '/session';

const UTIL_DIR = ROOT_DIR . '/util';

const LIB_DIR = ROOT_DIR . '/lib';

const LOG_DIR = ROOT_DIR . '/log';

const CORE_DIR = ROOT_DIR . '/core';

const VIEW_DIR = ROOT_DIR . '/view';

const TMP_DIR = ROOT_DIR . '/tmp';

const SECURE_DIR = ROOT_DIR . '/secure';

const ASSETS_DIR = HTDOCS_DIR . '/assets';

const ASSETS_USER_DIR = ASSETS_DIR . '/user';

const ASSETS_TMP_DIR = ASSETS_DIR . '/tmp';

const ASSETS_PRODUCT_DIR = ASSETS_DIR . '/product';

const IMG_DIR = HTDOCS_DIR . '/img';

// const RESOURCE_DIR = ROOT_DIR . '/resource';

const ASSETS_SRC = WEB_SRC . '/assets';

const ASSETS_USER_SRC = ASSETS_SRC . '/user';

const ASSETS_PRODUCT_SRC = ASSETS_SRC . '/product';

const ASSETS_TMP_SRC = ASSETS_SRC . '/tmp';

const CERT_UPLOAD_DIR = ROOT_DIR . "/admin/htdocs/resource/cert";

const CERT_SRC = ADMIN_SRC . "/resource/cert";

const IMG_SRC = WEB_SRC . '/img';

const JS_SRC = WEB_SRC . '/js';

const CSS_SRC = WEB_SRC . '/css';

const MAIL_ADMIN_WEB_SRC = 'https://admin.delulu.jp';

const DEFAULT_CONTROLLER = 'top';

const DEFAULT_ACTION = 'index';

const DEFAULT_404_REDIRECT_URL = WEB_SRC . '/error';

// クレジットカード決済機能有効化設定（true:有効, false:無効）
const PAYJP_ENABLED = false;

const CSRF_TOKEN_LENGTH = 16;

const IS_DISPLAY_MEMBER_CODE_QR = true; // QRコード表示

const IS_DISPLAY_MEMBER_CODE_BAR = false; // バーコード表示

const SMTP_HOST = 'smtp.lolipop.jp';

const SMTP_USER_NAME = 'no_reply@gtuned.net';

const SMTP_PASSWORD = 'xxxxxx';

const SMTP_SENDER_EMAIL = 'no_reply@gtuned.net';