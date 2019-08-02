<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/8/2
 * Time: 10:30 PM
 */

namespace core;

/**
 * Class Db
 * @package core
 */
class Db
{

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        //连接PDO
        self::connect();

        //注册Model实例
        if (empty(\design\Di::getInstance()->get('MODEL')))
            \design\Di::getInstance()->set('MODEL', new Model());

        return call_user_func_array([\design\Di::getInstance()->get('MODEL'), $method], $arguments);
    }


    /**
     * @return null|object|\PDO|string
     */
    private static function connect()
    {
        //判断之前是否依赖注入过
        if (!empty($pdo = \design\Di::getInstance()->get('PDO')))
            return $pdo;
        else
        {
            //获取 DSN
            $dsnString = self::getDsn();
            //实例PDO
            $pdo = new \PDO($dsnString, \design\Facade::Config('get', 'username'), \design\Facade::Config('get', 'password'));

            //注册PDO
            \design\Di::getInstance()->set('PDO', $pdo);

            return $pdo;
        }
    }

    /**
     * 获取 DSN
     * @return string
     */
    private static function getDsn()
    {
        if ($dsnString = \design\Facade::Config('get', 'dsn'))
            return $dsnString;

        $dsnString = \design\Facade::Config('get', 'type').':host='.\design\Facade::Config('get', 'host').
            ';port='.\design\Facade::Config('get', 'port').';dbname='.\design\Facade::Config('get', 'database')
            .';charset='.\design\Facade::Config('get', 'charset');

        return $dsnString;
    }
}