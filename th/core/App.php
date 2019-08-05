<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/29
 * Time: 3:04 PM
 */

namespace core;

/**
 * Class App
 * @package core
 */
class App
{

    /**
     * 框架启动
     */
    public static function run()
    {
        //注册路由类
        \design\Di::getInstance()->set('ROUTER', new Router());

        //加载助手函数
        include CORE_PATH . '/Helper.php';

        //加载配置路由
        include ROUTER_PATH . '/web.php';
        //解析当前URL
        $routerArray = \design\Di::getInstance()->get('ROUTER')->parseUrl();

        //分发路由
        self::dispatch($routerArray);
    }


    /**
     * 分发路由
     * @param array $routerArray
     */
    private static function dispatch(array $routerArray)
    {
        $controllerClass = $routerArray['namespace'] . $routerArray['controller'];
        //验证类是否存在
        if (class_exists($controllerClass))
        {
            //验证类中的方法是否存在
            $actionString = $routerArray['action'];
            if (method_exists($controllerClass, $actionString))
            {
                $newControllerClass = new $controllerClass;

                //设置视图模版
                $viewPath = $routerArray['module'] . '/view/' . $routerArray['controller']
                    . '/' . $routerArray['action'];
                $newControllerClass->setTpl($viewPath);

                //执行方法
                $newControllerClass->$actionString(...$routerArray['params']);

            }
            else
                exit('action does not exist');
        }
        else
            exit('controller does not exist');
    }
}