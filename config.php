<?php
namespace Config;

const DS = DIRECTORY_SEPARATOR;

const DB_HOST           = 'localhost';
const DB_NAME           = 'nag_test';
const DB_ROOT_USER_NAME = 'root';
const DB_ROOT_USER_PASS = 'r0O7m!m8tS';

const CONFIG_PATH       = __DIR__ . DS;
const CONFIG_FILE_PATH  = __FILE__;
const PUB_PATH          = CONFIG_PATH . 'public' . DS;
const RES_PATH          = CONFIG_PATH . 'resources' . DS; // путь к ресурсам
const CLASSES_PATH      = RES_PATH . 'classes' . DS;
const TESTS_PATH        = RES_PATH . 'tests' . DS;

const CORE_FILE_PATH     = CLASSES_PATH . 'Core.php';
const TEST_FILE_PATH     = TESTS_PATH . 'test.php';
