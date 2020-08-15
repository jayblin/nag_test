<?php

require_once 'config.php';
require_once \Config\CORE_FILE_PATH;

// print_r($_SERVER);
// print_r(INFO_VARIABLES);
// print_r($_GET);



$dbInit = [
    'class' => '\Core\DB\DBMYSQL',
    'host' => \Config\DB_HOST,
    'db' => \Config\DB_NAME,
    'user' =>\Config\DB_ROOT_USER_NAME,
    'pass' => \Config\DB_ROOT_USER_PASS
];


global $core;

$core = new \Core\Core($dbInit, \Config\CONTENT_PATH);
