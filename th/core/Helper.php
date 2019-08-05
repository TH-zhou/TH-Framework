<?php
/**
 * 助手函数类
 * User: zxm
 * Date: 2019/8/5
 * Time: 10:09 AM
 */

if (!function_exists('url'))
{
    function url($url)
    {
        $router = \design\Di::getInstance()->get('ROUTER');
        return $router->getUrl($url);
    }
}