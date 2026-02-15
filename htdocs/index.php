<?php

$system_const_path = dirname(__FILE__) . '/../conf/systemConst.local.php';
if (!file_exists($system_const_path)) {
	$system_const_path = dirname(__FILE__) . '/../conf/systemConst.php';
}
require_once $system_const_path;

ini_set('display_errors', DISPLAY_ERROR);

require_once dirname(__FILE__) . '/../conf/paramConst.php';
require_once dirname(__FILE__) . '/../conf/messageConst.php';
$db_const_path = dirname(__FILE__) . '/../conf/DBConst.local.php';
if (!file_exists($db_const_path)) {
	$db_const_path = dirname(__FILE__) . '/../conf/DBConst.php';
}
require_once $db_const_path;
require_once dirname(__FILE__) . '/../core/ClassLoader.php';

$dispatcher = new Dispatcher();
$dispatcher->dispatch();