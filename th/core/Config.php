<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/28
 * Time: 2:54 PM
 */

namespace core;

use design\Singleton;

/**
 * 配置类
 * Class Config
 * @package core
 */
class Config
{
    use Singleton;

    //全局配置数组
    private $configArray = [];


    /**
     * 加载配置文件
     * @param $file
     * @return array
     */
    public function load($file)
    {
        if (is_file($file)) //文件
            $this->loadFile($file);
        elseif (is_dir($file)) //目录
            $this->loadDir($file);
        else //返回配置
            return $this->configArray;
    }


    /**
     * @param $file
     * @return bool
     */
    private function loadFile($file)
    {
        if (file_exists($file))
        {
            $fileTypeString = pathinfo($file, PATHINFO_EXTENSION);
            if ($fileTypeString == ltrim(EXT, '.'))
                $this->set(include $file);

            return true;
        }
        else
            return false;
    }


    /**
     * 获取目录下所有文件
     * @param $dir
     * @return bool
     */
    private function loadDir($dir)
    {
        $fileArray = scandir($dir);
        foreach ($fileArray as $file)
        {
            if ($file != '.' && $file != '..')
                $this->loadFile($dir . $file);
        }

        return true;
    }

    /**
     * 设置配置
     * @param mixed $name 名称
     * @param string $val 值
     * @return array
     */
    public function set($name, $val = '')
    {
        if (!empty($name))
        {
            if (is_string($name)) //字符串类型
                $this->configArray[strtolower($name)] = $val;
            elseif (is_array($name)) //数组类型
                $this->configArray = array_merge($this->configArray, array_change_key_case($name));
            else
                return $this->configArray;
        }
        else
            return $this->configArray;
    }


    /**
     * 读取配置
     * @param null $name 配置名
     * @return array|mixed|null
     */
    public function get($name = NULL)
    {
        if (empty($name)) //返回全部配置
            return $this->configArray;

        $nameString = strtolower($name);

        if (isset($this->configArray[$nameString]))
            return $this->configArray[$nameString];
        else
            return NULL;
    }
}