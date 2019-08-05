<?php
/**
 * 框架入口文件
 * User: zxm
 * Date: 2019/7/26
 * Time: 5:16 PM
 */

// 检测PHP环境
if(version_compare(PHP_VERSION,'7.0','<')) die('require PHP >= 7.0 !');

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

//定义入口文件名
define('ENTRANCE_FILE', 'index.php');

//加载框架引导文件
require __DIR__ . '/../th/start.php';