<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/29
 * Time: 11:46 AM
 */

namespace design;

/**
 * 门面模式
 * Class Facade
 * @package design
 */
class Facade
{

    //存储门面实例数组
    private static $facadeArray = [];

    /**
     * @param $name
     * @param array $arguments
     * @return mixed|null|object
     */
    public static function __callStatic($name, $arguments = [])
    {
        $methodString = array_shift($arguments);

        if (!isset(self::$facadeArray[$name]))
            self::getFacade($name, $arguments);

        $facadeInstance = self::$facadeArray[$name];

        if (method_exists($facadeInstance, $methodString))
        {
            return call_user_func_array([$facadeInstance, $methodString], $arguments);
        }
        else
            return NULL;
    }


    /**
     * 获取门面实例
     * @param $name
     * @param $arguments
     * @return mixed|null|object
     */
    private static function getFacade($name, $arguments)
    {
        if (self::isExistsByDi($name))
            return self::$facadeArray[$name];

        //TODO 目前仅支持core核心类的使用
        $name = '\\core\\'.$name;
        if (class_exists($name))
        {
            $reflectionClass = new \ReflectionClass($name);
            $newInstance = $reflectionClass->newInstanceArgs($arguments);
            self::$facadeArray[$name] = $newInstance;

            return $newInstance;
        }
        else
            return NULL;
    }


    /**
     * 验证是否在Di类中已经存在
     * @param $name
     * @return bool
     */
    private static function isExistsByDi($name)
    {
        $nameString = strtoupper($name);

        $nameObj = Di::getInstance()->get($nameString);
        if (!empty($nameObj))
        {
            self::$facadeArray[$name] = $nameObj;

            return true;
        }
        else
            return false;
    }
}