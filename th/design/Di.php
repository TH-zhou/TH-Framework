<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/28
 * Time: 1:07 PM
 */

namespace design;


/**
 * 依赖注入类
 * Class Di
 * @package design
 */
class Di
{

    use Singleton;


    private $register = [];

    /**
     * 注入实例
     * @param string $key 实例名
     * @param mixed $obj 实例对象
     * @param array $param 实例对象参数
     */
    public function set($key, $obj, array $param = [])
    {
        if (!isset($this->register[$key]))
        {
            $this->register[$key] = [
                'obj' => $obj,
                'params' => $param
            ];
        }
    }

    /**
     * 清除某个实例
     * @param $key
     */
    public function delete($key)
    {
        unset($this->register[$key]);
    }

    /**
     * 全部清除
     */
    public function clear()
    {
        $this->register = [];
    }

    /**
     * 获取某个实例
     * @param string $key 实例名
     * @return null|object|string
     */
    public function get($key)
    {
        if (isset($this->register[$key]))
        {
            $currentObj = $this->register[$key];

            if (is_object($currentObj['obj'])) //实例对象，直接返回
                return $currentObj['obj'];
            elseif (is_string($currentObj['obj']) && class_exists($currentObj['obj'])) //类名存在
            {
                //反射类
                $reflectionClass = new \ReflectionClass($currentObj['obj']);
                $newInstance = $reflectionClass->newInstanceArgs($currentObj['params']);
                $this->register[$key]['obj'] = $newInstance;

                return $newInstance;
            }
            else
                return $currentObj['obj'];
        }
        else
            return null;
    }
}