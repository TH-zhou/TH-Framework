<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/26
 * Time: 5:31 PM
 */

namespace th;

define('DS', DIRECTORY_SEPARATOR);
define('EXT', '.php');
defined('TH_PATH') or define('TH_PATH', __DIR__ . DS);
defined('DESIGN_PATH') or define('DESIGN_PATH', TH_PATH . 'design' . DS);
defined('CORE_PATH') or define('CORE_PATH', TH_PATH . 'core' . DS);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(APP_PATH) . DS);
defined('CONF_PATH') or define('CONF_PATH', ROOT_PATH . 'config'. DS);
defined('ROUTER_PATH') or define('ROUTER_PATH', ROOT_PATH . 'router'. DS);
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);


//引入自动加载文件
require CORE_PATH . 'Loader.php';

//批量注册
\core\Loader::addNamespaceBatch([
    'app' => APP_PATH . DS,
    'core' => TH_PATH . 'core' . DS,
    'design' => TH_PATH. 'design' . DS,
]);
\core\Loader::register();

//注册配置文件类
\design\Di::getInstance()->set('CONFIG', new \core\Config());
//加载配置文件
$configObj = \design\Di::getInstance()->get('CONFIG');
$configObj->load(CONF_PATH);

//判断是否开启debug
$displayStatus = $configObj->get('debug') ? 'On' : 'Off';
ini_set('display_errors', $displayStatus);
//不管是debug开始还是关闭，报错都记录起来，debug为false的话不展示页面上而已
error_reporting(E_ALL);
ini_set('log_errors', 'On');//开启错误日志
ini_set('error_log', LOG_PATH.'/'.date('Ymd').'_error.log'); //指定错误日志文件