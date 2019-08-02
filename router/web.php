<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/29
 * Time: 11:05 PM
 */


\core\Router::bindRule('test', '\app\index\controller\index@test');

\core\Router::get('tpl', '\app\index\controller\index@testTpl');

\core\Router::get('hello', '\app\index\controller\index@worlds');

\core\Router::get('testParam/{param}', '\app\index\controller\index@testParams');

\core\Router::get('testDb', '\app\index\controller\Users@index');