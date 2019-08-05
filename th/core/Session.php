<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/8/4
 * Time: 10:07 PM
 */

namespace core;

use design\Facade;

class Session
{

    //判断session是否已经初始化
    private static $init = null;

    //session前缀
    private static $prefix;


    /**
     * session 自动启动或者初始化
     */
    public static function autoStart()
    {
        $initStatus = self::$init;

        if ($initStatus === null)
            self::init();
        elseif ($initStatus === false)
        {
            if (PHP_SESSION_ACTIVE != session_status())
                session_start();

            self::$init = true;
        }
    }

    /**
     * session初始化
     */
    public static function init()
    {
        $isAutoStart = false;

        //未启动session
        if (PHP_SESSION_ACTIVE != session_status())
        {
            //把session会话配置为关闭，用session_start启动
            ini_set('session.auto_start', 0);

            $isAutoStart = true;
        }

        //session前缀
        self::$prefix = Facade::Config('get', 'session_preifx');

        //设置session的过期时间
        if (!empty($sessionExpire = Facade::Config('get', 'session_expire')))
        {
            //session使用cookie的生存期，秒为单位
            ini_set('session.cookie_lifetime', $sessionExpire);
            //设定保存的session文件生存期，超过此参数设定秒后，保存的数据会被视为垃圾，并有垃圾回收程序清理
            ini_set('session.gc_maxlifetime', $sessionExpire);
        }

        if ($isAutoStart)
        {
            //开启session
            session_start();

            self::$init = true;
        }
        else
            self::$init = false;
    }


    /**
     * session设置
     * @param string $name session名称
     * @param mixed $value session值
     */
    public static function set($name, $value)
    {
        empty(self::$init) && self::autoStart();

        if (strpos($name, '.'))
        {
            //二维数组赋值
            list($name1, $name2) = explode('.', $name);
            $_SESSION[self::$prefix][$name1][$name2] = $value;
        }
        else
            $_SESSION[self::$prefix][$name] = $value;
    }


    /**
     * 获取session值
     * @param string $name session名称
     * @return array|bool|null
     */
    public static function get($name)
    {
        empty(self::$init) && self::autoStart();

        if ($name == '') //返回全部session
            return isset($_SESSION[self::$prefix]) ? $_SESSION[self::$prefix] : [];
        else
        {
            if (strpos($name, '.')) //判断是否是二维数组
            {
                list($name1, $name2) = explode('.', $name);

                return isset($_SESSION[self::$prefix][$name1][$name2]) ? $_SESSION[self::$prefix][$name1][$name2] : NULL;
            }
            else
                return isset($_SESSION[self::$prefix][$name]) ? $_SESSION[self::$prefix][$name] : NULL;
        }
    }

    /**
     * 删除session值
     * @param string|array $name session名称
     */
    public static function delete($name)
    {
        empty(self::$init) && self::autoStart();

        if (is_array($name)) //删除多个
            foreach ($name as $v)
                self::delete($v);
        else
        {
            if (strpos($name, '.')) //检测是否是二维数组
            {
                list($name1, $name2) = explode('.', $name);
                unset($_SESSION[self::$prefix][$name1][$name2]);
            }
            else
                unset($_SESSION[self::$prefix][$name]);
        }
    }


    /**
     * 清空session数据
     */
    public static function clear()
    {
        empty(self::$init) && self::autoStart();

        unset($_SESSION[self::$prefix]);
    }

    /**
     * 销魂session
     */
    public static function destroy()
    {
        if (!empty($_SESSION))
            $_SESSION = [];

        //销毁一个会话中的全部数据
        session_destroy();
        //释放所有的会话变量
        session_unset();

        self::$init = NULL;
    }


    /**
     * 暂停session
     */
    public static function pause()
    {
        // 暂停session
        session_write_close();
        self::$init = false;
    }
}