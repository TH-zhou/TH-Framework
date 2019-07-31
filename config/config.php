<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/28
 * Time: 10:28 PM
 */

return [

    //调试模式 true开启 false关闭
    'debug' => true,

    //URL模式 1：pathinfo模式
    'url_type' => 1,

    //默认模块
    'default_module' => 'index',

    //默认控制器
    'default_controller' => 'index',

    //默认方法
    'default_action' => 'index',

    //模版缓存 true开启 false关闭
    'tpl_cache' => true,

    //缓存文件内容N秒内不更新
    'tpl_cache_time' => 5,

    //缓存存放路径
    'tpl_cache_path' => RUNTIME_PATH . 'cache' . DS,

];