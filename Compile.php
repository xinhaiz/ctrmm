<?php

/**
 * 路径分隔符
 *
 * window = \
 * linux  = /
 *
 * 避免因环境不同而产生麻烦
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * 应用的根目录
 */
define('APP_PATH', dirname(__FILE__));

// 编译
require('Lib/Consts.php');
require('Lib/Make.php');
require('Lib/Compile.php');
require('Buffer/Folder.php');
require('Buffer/Filec.php');
require('Buffer/Char.php');

$make = new \Lib\Make();
$make->run();