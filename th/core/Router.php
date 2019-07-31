<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/29
 * Time: 4:43 PM
 */

namespace core;
use design\Facade;


/**
 * 路由类
 * Class Router
 * @package core
 */
class Router
{

    /**
     * 路由规则
     * @var array
     */
    private static $rulesArray = [
        'get' => [],
        'post' => [],
        'put' => [],
        'delete' => [],
    ];


    /**
     * 路由绑定
     * @param $rule
     * @param $route
     * @param string $type
     */
    public static function bindRule($rule, $route, $type = '*')
    {
        $ruleString = DS . strtolower(ltrim($rule, DS));
        $routeString = strtolower($route);

        if ($type == '*') //全部请求方式都绑定
        {
            $typeArray = array_keys(self::$rulesArray);
            foreach ($typeArray as $v)
                self::addRule($v, $ruleString, $routeString);
        }
        elseif (is_array($type)) //N个请求方式都绑定
        {
            $typeArray = array_change_key_case($type);
            foreach ($typeArray as $v)
                self::addRule($v, $ruleString, $routeString);
        }
        else //固定某个请求方式绑定
            self::addRule($type, $ruleString, $routeString);
    }


    /**
     * 将路由添加到规则数组
     * @param $type
     * @param $rule
     * @param $route
     * @return bool
     */
    private static function addRule($type, $rule, $route)
    {
        $routeTypeArray = array_keys(self::$rulesArray);
        if (in_array($type, $routeTypeArray))
        {
            self::$rulesArray[$type][$rule] = $route;

            return true;
        }
        else
            return false;
    }

    /**
     * 直接像Router::get()快捷绑定路由
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments)
    {
        array_push($arguments, $name);
        self::bindRule(...$arguments);
    }


    /**
     * 解析路由
     * @return array
     */
    public static function parseUrl()
    {
        $matchUrlArray = self::match();
        if (empty($matchUrlArray)) //未匹配到路由
            $matchUrlArray = self::defaultRoute(); //获取默认路由

        //获取方法
        $actionString = substr(strstr($matchUrlArray['route'], '@'), 1);

        $routeString = substr($matchUrlArray['route'], 0, strpos($matchUrlArray['route'], '@'));
        //获取控制器
        $controllerString = substr(strrchr($routeString, '\\'), 1);

        //命名空间
        $namespaceString = substr($routeString, 0, strrpos($routeString, $controllerString));

        //获取模块
        $moduleString = substr(strstr($namespaceString, '\\controller', true), strlen('\\app\\'));

        return [
            'namespace' => $namespaceString,
            'module' => $moduleString,
            'controller' => ucfirst($controllerString),
            'action' => $actionString,
            'params' => !empty($matchUrlArray['params']) ? $matchUrlArray['params'] : []
        ];
    }

    /**
     * 检测URL和规则路由是否匹配
     * @return array
     */
    public static function match()
    {
        //获取pathinfo www.xxx.com/index.php 访问是获取不到pathinfo，需判断
        $pathInfoString = empty($_SERVER['PATH_INFO']) ? NULL : strtolower($_SERVER['PATH_INFO']);
        $methodString = strtolower($_SERVER['REQUEST_METHOD']);

        $matchUrlArray = [];

        if (isset(self::$rulesArray[$methodString][$pathInfoString])) //直接匹配上
        {
            $matchUrlArray = [
                'route' => self::$rulesArray[$methodString][$pathInfoString]
            ];
        }
        else //未直接匹配上
        {
            foreach (self::$rulesArray[$methodString] as $rule => $route)
            {
                //获取除了参数的路由字符串
                $strReplaceRuleString = str_replace('/', '\/', (strstr($rule, '{', true)));
                if (!empty($strReplaceRuleString) && preg_match('/^'.$strReplaceRuleString.'/', $pathInfoString, $match)) //和当前PATH_INFO匹配
                {
                    //获取参数
                    $paramString = substr($pathInfoString, strlen($match[0]));
                    $paramArray = array_filter(explode(DS, $paramString));

                    $matchUrlArray = [
                        'route' => $route,
                        'params' => $paramArray
                    ];

                    break;
                }
            }
        }

        return $matchUrlArray;
    }

    /**
     * 默认路由
     * @return array
     */
    private static function defaultRoute()
    {
        //默认模块
        $defaultModuleString = Facade::Config('get', 'default_module');
        //默认控制器
        $defaultControllerString = Facade::Config('get', 'default_controller');
        //默认方法
        $defaultActionString = Facade::Config('get', 'default_action');

        $ruleUrlString = strtolower('\\app\\' . $defaultModuleString . '\\controller\\'
            . $defaultControllerString . '@' . $defaultActionString);

        return ['route' => $ruleUrlString];
    }

}