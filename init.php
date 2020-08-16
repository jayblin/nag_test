<?php

require_once 'config.php';
require_once \Config\CORE_FILE_PATH;

$dbInit = [
    'class' => '\Core\DB\DBMYSQL',
    'host' => \Config\DB_HOST,
    'db' => \Config\DB_NAME,
    'user' =>\Config\DB_ROOT_USER_NAME,
    'pass' => \Config\DB_ROOT_USER_PASS
];


global $core;

$core = \Core\Core::CreateInstance($dbInit, \Config\CONTENT_PATH);
$core->HandleRequest();