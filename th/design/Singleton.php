<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/28
 * Time: 1:02 PM
 */

namespace design;

/**
 * 单例基类
 * Trait Singleton
 * @package design
 */
trait Singleton
{

    private static $instance;

    public static function getInstance(...$param)
    {
        if (!isset(self::$instance))
            self::$instance = new static(...$param);

        return self::$instance;
    }
}