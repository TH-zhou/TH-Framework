<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/27
 * Time: 10:51 PM
 */

namespace core;


/**
 * 自动加载类
 * Class Loader
 * @package core
 */
class Loader
{

    //命名空间前缀
    public static $namespace = [];

    /**
     * 注册自动加载
     * @param null $autoload
     */
    public static function register($autoload = NULL)
    {
        //注册自动加载
        spl_autoload_register($autoload ?: 'core\\Loader::autoload', true, true);
    }

    /**
     * 自动加载文件
     * @param $class
     * @return bool
     */
    public static function autoload($class)
    {
        if ($file = self::findFile($class))
        {
            self::includeFile($file);

            return true;
        }
        else //找不到文件
            return false;
    }

    /**
     * 查找文件
     * @param $class
     * @return bool|string
     */
    private static function findFile($class)
    {
        if (!empty(self::$namespace))
        {
            //把字符串中的 \ 替换成 /
            $classDSString = strtr($class, '\\', DS) . EXT;
            $subPathString = $class;
            while (false != $lastPosString = strrpos($subPathString, '\\'))
            {
                $subPathString = substr($subPathString, 0, $lastPosString);
                $prefixString = $subPathString . '\\';
                if (isset(self::$namespace[$prefixString])) //存在该命名空间前缀
                {
                    $pathEndString = DS . substr($classDSString, $lastPosString + 1);
                    foreach (self::$namespace[$prefixString] as $v)
                    {
                        $fileString = $v . $pathEndString;
                        if (file_exists($fileString))
                            return $fileString;
                    }
                }
            }

            return false;
        }
        else
            return false;
    }

    /**
     * @param $file
     */
    private static function includeFile($file)
    {
        include $file;
    }


    /**
     * 支持批量注册命名空间
     * @param $namespace
     * @param string $path
     */
    public static function addNamespaceBatch($namespace, $path = '')
    {
        if (is_array($namespace))
        {
            foreach ($namespace as $prefix => $path)
                self::addNamespace($prefix, $path);
        }
        else
            self::addNamespace($namespace, $path);
    }

    /**
     * 注册命名空间
     * @param string $prefix 命名空间前缀
     * @param string $path 路径
     * @param bool $prepend 预先设置的优先级更高 true表示放在首，false放在尾
     */
    public static function addNamespace($prefix, $path, $prepend = true)
    {
        //规范命名空间前缀
        $prefix = trim($prefix, '\\') . '\\';

        //规范文件目录
        $path = rtrim($path, DS) . DS;

        if (!isset(self::$namespace[$prefix]))
            self::$namespace[$prefix] = [];

        $prepend ? array_unshift(self::$namespace[$prefix], $path)
            : array_push(self::$namespace[$prefix], $path);
    }
}