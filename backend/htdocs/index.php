<?php

require_once dirname(__FILE__) . '/../conf/systemConst.php';

ini_set('display_errors', DISPLAY_ERROR);

require_once dirname(__FILE__) . '/../conf/paramConst.php';
require_once dirname(__FILE__) . '/../conf/messageConst.php';
require_once dirname(__FILE__) . '/../conf/DBConst.php';
require_once dirname(__FILE__) . '/../core/ClassLoader.php';

$dispatcher = new Dispatcher();
$dispatcher->dispatch();