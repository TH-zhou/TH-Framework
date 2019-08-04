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
 * @method Model table(string $table) static 指定数据表（含前缀）
 * @method Model name(string $name) static 指定数据表（不含前缀）
 * @method Model fields($field) static 需要查询的字段 string|array
 * @method Model where($where) static 查询条件 string|array
 * @method Model order($order) static 查询ORDER string|array
 * @method Model limit($limit) static 查询LIMIT string|array
 * @method Model alias(string $alias) static 指定表别名
 * @method Model join($table, $on, $join) static JOIN查询
 * @method Model allowed($allowed) static 指定允许操作的字段 bool|array
 * @method Model find($sql = null) static 查询单个记录
 * @method Model select($sql = null) static 查询多个记录
 * @method Model value(string $field) static 获取某个字段的值
 * @method Model column($field) static 获取某个列的值 string|array*
 * @method Model insertGetId(array $data) static 插入一条记录并返回自增ID
 * @method Model insertAll(array $data) static 插入多条记录
 * @method Model setField(array $data) static 更新指定字段
 * @method Model delete() static 删除记录
 * @method Model getLastSql() static 获取最后一次执行的sql
 * @method Model startTrans() static 启动事务
 * @method Model commit() static 用于非自动提交状态下面的查询提交
 * @method Model rollback() static 事务回滚
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