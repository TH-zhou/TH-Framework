<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/8/1
 * Time: 2:08 PM
 */

namespace core;

use design\Facade;
use design\Singleton;

class Model
{
    use Singleton;

    //数据库连接池
    protected static $links = [];

    //PDO实例
    protected $pdo;

    //表名 带前缀
    protected $table;

    //表名 不带前缀
    protected $name;

    //类名
    protected $class;

    //保存的参数
    private $options = [];

    //执行的sql语句
    protected $sql;

    //主键
    protected $pk;


    /**
     * Model constructor.
     */
    public function __construct()
    {
        //数据库连接
        $this->connect();
    }


    /**
     * 数据库连接
     * @return $this
     */
    public function connect()
    {
        //当前类名
        $this->class = get_called_class();

        if (!isset(self::$links[$this->class]))
        {
            $dsnString = $this->getDsn();

            try{

                $this->pdo = new \PDO($dsnString, Facade::Config('get', 'username'), Facade::Config('get', 'password'));

                //获取表名
                $this->name = basename(strtr($this->class, '\\', DS));

                //带前缀表名
                $this->table = Facade::Config('get', 'prefix') . strtolower($this->name);

                self::$links[$this->class] = $this->pdo;

            }catch (\Exception $e)
            {
                exit($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * 获取 DSN
     * @return string
     */
    private function getDsn()
    {
        if ($dsnString = Facade::Config('get', 'dsn'))
            return $dsnString;

        $dsnString = Facade::Config('get', 'type').':host='.Facade::Config('get', 'host').
            ';port='.Facade::Config('get', 'port').';dbname='.Facade::Config('get', 'database')
            .';charset='.Facade::Config('get', 'charset');

        return $dsnString;
    }


    /**
     * 直接调用PDO中的方法
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->pdo, $method], $arguments);
    }


    /**
     * 设置数据表名 带前缀
     * @param string $table
     * @return $this
     */
    public function table($table = '')
    {
        $this->options['table'] = $this->table;
        if (!empty($table) && is_string($table))
            $this->options['table'] = $this->table;

        return $this;
    }

    /**
     * 设置数据表名 不带前缀
     * @param string $name
     * @return $this
     */
    public function name($name = '')
    {
        $nameString = $this->name;
        if (!empty($name) && is_string($name))
            $nameString = $name;

        $this->options['name'] = Facade::Config('get', 'prefix') . strtolower($nameString);

        return $this;
    }


    /**
     * 指定查询字段
     * @param $field
     * @return $this
     */
    public function fields($field)
    {
        if (is_array($field))
            $this->options['fields'] = implode(',', $field);
        else
            $this->options['fields'] = $field;

        return $this;
    }


    /**
     * where条件
     * @param $where
     * @return $this
     */
    public function where($where)
    {
        if (is_array($where))
        {
            $whereString = '';
            foreach ($where as $k => $v)
            {
                if (!empty($whereString))
                    $whereString .= ' AND ';
                $whereString .= $k . ' = ' . $v;
            }
            $this->options['where'] = ' WHERE '.$whereString;
        }
        else
            $this->options['where'] = ' WHERE '.$where;

        return $this;
    }

    /**
     * orderby排序
     * @param $order
     * @return $this
     */
    public function order($order)
    {
        if (is_array($order))
        {
            $orderByString = '';
            foreach ($order as $k => $v)
            {
                if (is_numeric($k)) //['id', 'desc']
                {
                    $orderByString = $order[0] . ' ' . $order[1];
                    break;
                }
                else //['id' => 'desc']
                {
                    if (!empty($orderByString))
                        $orderByString .= ',';
                    $orderByString .= $k . ' ' . $v;
                }
            }

            $this->options['order'] = ' ORDER BY '.$orderByString;
        }
        else
            $this->options['order'] = ' ORDER BY '.$order;

        return $this;
    }

    /**
     * limit分页
     * @param $limit
     * @return $this
     */
    public function limit($limit = '')
    {
        if (is_array($limit))
            $this->options['limit'] = ' LIMIT '.$limit[0].','.$limit[1];
        else
            $this->options['limit'] = ' LIMIT '.$limit;

        return $this;
    }

    /**
     * 表别名
     * @param $alias
     */
    public function alias($alias)
    {
        $this->options['alias'] = ' AS '.$alias;
    }


    /**
     * 连表查询 如：join('表名 表别名', '连表条件', '左/右/内连')
     * @param array ...$args
     * @return $this
     */
    public function join(...$args)
    {
        //left/right/inner join
        $joinString = ' '.$args[3].' JOIN ';

        $joinTableString = $args[0];
        if (strpos($args[0], Facade::Config('get', 'prefix')) !== false)
            $joinTableString = Facade::Config('get', 'prefix').$joinTableString;

        $joinWhereString = ' ON '.$args[2];

        $this->options['join'] = $joinString . $joinTableString . $joinWhereString;

        return $this;
    }


    /**
     * 允许操作的字段 true表示表中所有字段 ['字段1'，'字段2']允许N个对应字段
     * @param bool $allowed
     * @return $this
     */
    public function allowed($allowed = true)
    {
        $this->options['allowed'] = $allowed;

        return $this;
    }


    /**
     * 查询一条数据
     * @param string $sql 可直接传规范的sql语句来查询
     * @return array
     */
    public function find($sql = '')
    {
        if (empty($sql))
        {
            $sql = 'SELECT %fields% FROM %tableString% %whereString% %limit%';

            //限制查询一条
            $this->limit(1);

            //默认查询所有字段
            if (empty($optionsArray['fields']))
                $this->fields('*');

            //获取参数
            $optionsArray = $this->getOptions();

            //替换内容
            $sql = str_replace(
                ['%fields%', '%tableString%', '%whereString%', '%limit%'],
                [$optionsArray['fields'], $optionsArray['table'], $optionsArray['where'], $optionsArray['limit']],
                $sql
            );

            //清空options参数
            $this->clearOption();
        }

        $PDOStatement = $this->querySql($sql);

        //从 PDOStatement 中获取一条数据 返回索引数组
        $fetchArray = $PDOStatement->fetch(\PDO::FETCH_ASSOC);
        if (empty($fetchArray)) $fetchArray = [];

        return $fetchArray;
    }


    /**
     * 查询多条数据
     * @param string $sql 可直接传规范的sql语句来查询
     * @return array
     */
    public function select($sql = '')
    {
        if (empty($sql))
        {
            $sql = 'SELECT %fields% FROM %tableString% %whereString% %orderby% %limit%';

            //默认查询所有字段
            if (empty($optionsArray['fields']))
                $this->fields('*');

            //获取参数
            $optionsArray = $this->getOptions();

            //替换内容
            $sql = str_replace(
                ['%fields%', '%tableString%', '%whereString%', '%orderby%', '%limit%'],
                [$optionsArray['fields'], $optionsArray['table'], $optionsArray['where'], $optionsArray['order'], $optionsArray['limit']],
                $sql
            );

            //清空options参数
            $this->clearOption();
        }

        $PDOStatement = $this->querySql($sql);

        //从 PDOStatement 中获取多条数据 返回索引数组
        $fetchAllArray = $PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($fetchAllArray)) $fetchAllArray = [];

        return $fetchAllArray;
    }


    /**
     * 取单个字段的值
     * @param string $field
     * @return string
     */
    public function value($field = '')
    {
        /**
         * $field 优先级高于 $this->options['fields']
         * $this->options['fields']有多个字段的话，只获取第一个
         * $this->options['fields']也不存在的话，获取表中的第一个字段
         */
        $fieldsString = $field ? (is_array($field) ? implode(',', $field) : $field)
            : (!empty($this->options['fields']) ? explode(',', $this->options['fields'])[0] : '*');

        $sql = 'SELECT '.$fieldsString.' FROM %tableString% %whereString% %orderby% %limit%';

        //获取参数
        $optionsArray = $this->getOptions();

        //替换内容
        $sql = str_replace(
            ['%tableString%', '%whereString%', '%orderby%', '%limit%'],
            [$optionsArray['table'], $optionsArray['where'], $optionsArray['order'], $optionsArray['limit']],
            $sql
        );

        //清空options参数
        $this->clearOption();

        $PDOStatement = $this->querySql($sql);

        //从 PDOStatement 中获取一条数据 返回索引数组
        $fetchArray = $PDOStatement->fetch(\PDO::FETCH_ASSOC);
        if (empty($fetchArray)) return '';

        return current($fetchArray);
    }

    /**
     * 获取一列数据
     * @param string $field
     * @return array
     */
    public function column($field = '')
    {
        /**
         * $field 优先级高于 $this->options['fields']
         * $this->options['fields']有多个字段的话，只获取前两个
         * $this->options['fields']也不存在的话，获取表中的第一个字段这一列
         */
        $fieldsString = $field ? (is_array($field) ? implode(',', $field) : $field) :
            (!empty($this->options['fields']) ? $this->options['fields'] : '*');
        //只取2个
        $fieldsArray = array_slice(array_filter(explode(',', $fieldsString)), 0, 2);
        $fieldsCount = count($fieldsArray);
        $fieldsString = implode(',', $fieldsArray);

        $sql = 'SELECT '.$fieldsString.' FROM %tableString% %whereString% %orderby% %limit%';

        //获取参数
        $optionsArray = $this->getOptions();

        //替换内容
        $sql = str_replace(
            ['%tableString%', '%whereString%', '%orderby%', '%limit%'],
            [$optionsArray['table'], $optionsArray['where'], $optionsArray['order'], $optionsArray['limit']],
            $sql
        );

        //清空options参数
        $this->clearOption();

        $PDOStatement = $this->querySql($sql);

        //从 PDOStatement 中获取多条数据 返回索引数组
        $fetchAllArray = $PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($fetchAllArray)) return [];

        if ($fieldsCount == 1) //只有一个字段
            $resultArray = array_column($fetchAllArray, $fieldsString);
        else
            $resultArray = array_combine(array_column($fetchAllArray, $fieldsArray[0]), array_column($fetchAllArray, $fieldsArray[1]));

        return $resultArray;
    }


    /**
     * 查询sql，以 PDOStatement 对象形式返回结果集
     * @param $sql
     * @return mixed
     */
    private function querySql($sql)
    {
        //记录执行sql
        $this->sql = $sql;

        $query = $this->pdo->query($sql);
        //查询时报错，打印错误信息
        if ($query === false) die(print_r($this->pdo->errorInfo(), true));

        return $query;
    }

    /**
     * 执行sql，并返回受影响的行数
     * @param $sql
     * @return mixed
     */
    private function execSql($sql)
    {
        //记录执行sql
        $this->sql = $sql;

        $execStatus = $this->pdo->exec($sql);
        //执行时报，打印错误信息
        if ($execStatus === false) die(print_r($this->pdo->errorInfo(), true));

        return $execStatus;
    }

    /**
     * 获取$this->options中的数据
     * @param array $methods
     * @return array
     */
    private function getOptions(array $methods = [])
    {
        $optionsArray = [];
        $checkMethodArray = $methods ?: ['table', 'name', 'fields', 'where', 'order', 'limit', 'alias', 'join', 'allowed'];

        foreach ($checkMethodArray as $v)
            $optionsArray[$v] = !empty($this->options[$v]) ? $this->options[$v] : '';

        //没有调用table()或者name方法时补上表名
        if (empty($optionsArray['table']) || empty($optionsArray['name']))
            $optionsArray['table'] = $optionsArray['name'] = $this->table;

        return $optionsArray;
    }

    /**
     * 清空$this->options参数
     */
    private function clearOption()
    {
        $this->options = [];
    }


    /**
     * 插入单条数据
     * @param array $data
     * @return bool|mixed
     */
    public function insertGetId(array $data)
    {
        if (empty($data)) return false;
        //检验可允许操作的字段
        $allowedFieldsArray = $this->checkAllowedFieldsByTable();

        //过滤不能操作的字段
        $addArray = $this->filterFields($data, $allowedFieldsArray);

        if (!empty($addArray))
        {
            //加``小引号
            $addKeyArray = array_map(function ($v){
                return '`'.$v.'`';
            }, array_keys($addArray));

            //加""双引号 有字符串
            $addArray = array_map(function ($v){
                return '"'.$v.'"';
            }, $addArray);

            //拼接sql
            $this->sql = $sql = 'INSERT INTO '.$this->table.' ('.implode(',', $addKeyArray). ') VALUES ('.implode(',', $addArray).')';

            //执行sql
            $resultInt = $this->execSql($sql);
            if ($resultInt)
                return $this->pdo->lastInsertId(); //获取最后插入的id
            else
                return $resultInt;
        }

        return false;
    }

    /**
     * 批量插入数据
     * @param array $data
     * @return bool|mixed
     */
    public function insertAll(array $data)
    {
        if (empty($data)) return false;
        $allowedFieldsArray = $this->checkAllowedFieldsByTable();

        $addArray = $addKeyArray = [];
        foreach ($data as $k => $v)
        {
            foreach (array_keys($v) as $val)
            {
                if (in_array($val, $allowedFieldsArray))
                {
                    $addKeyArray[] = $val;
                    $addArray[$k][$val] = $v[$val];
                }
            }
        }

        if (!empty($addArray))
        {
            $addKeyArray = array_map(function ($v){
                return '`'.$v.'`';
            }, array_unique($addKeyArray));

            $sql = 'INSERT INTO '.$this->table.' ('.implode(',', $addKeyArray).') VALUES ';

            $valuesString = '';
            foreach ($addArray as $k => $v)
            {
                if (!empty($valuesString))
                    $valuesString .= ',';

                $v = array_map(function ($v){
                    return '"'.$v.'"';
                }, $v);

                $valuesString .= '('.implode(',', $v).')';
            }

            $sql = $sql . $valuesString;

            $resultInt = $this->execSql($sql);

            return $resultInt;
        }

        return false;
    }

    /**
     * 更新数据
     * @param array $data
     * @return bool|mixed
     */
    public function setField(array $data)
    {
        if (empty($data)) return false;
        $allowedFieldsArray = $this->checkAllowedFieldsByTable();

        //过滤不能操作的字段
        $upArray = $this->filterFields($data, $allowedFieldsArray);

        if (!empty($upArray))
        {
            $setFieldsString = ' SET ';
            foreach ($upArray as $k => $v)
                $setFieldsString .= '`'.$k.'` = "'.$v.'",';

            $setFieldsString = rtrim($setFieldsString, ',');

            $sql = 'UPDATE '.$this->table.' '.$setFieldsString.' %whereString%';

            //获取$this->options参数
            $optionsArray = $this->getOptions();
            $sql = str_replace('%whereString%', $optionsArray['where'], $sql);

            //清除$this->options参数
            $this->clearOption();

            //执行sql
            $resultInt = $this->execSql($sql);

            return $resultInt;
        }

        return false;
    }


    /**
     * 删除数据
     * @return mixed
     */
    public function delete()
    {
        //获取$this->options参数
        $optionsArray = $this->getOptions();
        if (empty($optionsArray['where']))
            exit('请确定删除条件');

        $sql = 'DELETE FROM '.$this->table.' %whereString%';
        $sql = str_replace('%whereString%', $optionsArray['where'], $sql);

        //清除$this->options参数
        $this->clearOption();

        //执行sql
        $result = $this->execSql($sql);

        return $result;
    }


    /**
     * 验证允许操作的表字段
     * @return array
     */
    private function checkAllowedFieldsByTable()
    {
        $getTableFieldsArray = $this->getTableFields();
        $tableFieldsArray = array_keys($getTableFieldsArray);

        //把主键从数组中剔除，因为主键是自增
        if (($searchKey = array_search($this->pk, $tableFieldsArray)) !== false)
            unset($tableFieldsArray[$searchKey]);

        $allowedFieldsArray = [];
        if (empty($this->options['allowed']) || $this->options['allowed'] === true) //空或者true是表中的所有字段
            $allowedFieldsArray = $tableFieldsArray;
        else
        {
            $allowedArray = is_array($this->options['allowed']) ? $this->options['allowed']
                : explode(',', $this->options['allowed']);

            foreach ($allowedArray as $v)
            {
                if (in_array($v, $tableFieldsArray))
                    $allowedFieldsArray[] = $v;
            }
        }

        return $allowedFieldsArray;
    }


    /**
     * 获取表字段信息
     * @return array
     */
    private function getTableFields()
    {
        $sqlString = 'SHOW COLUMNS FROM `' . $this->table . '`'; //拼接 SQL 语句
        $resultArray = $this->select($sqlString);

        $infoArray = [];
        if (!empty($resultArray)) {
            foreach ($resultArray as $key => $val) {
                $val = array_change_key_case($val);

                //主键标识
                if ($isPkString = (strtolower($val['key']) == 'pri'))
                    $this->pk = $val['field'];

                $infoArray[$val['field']] = [
                    'name' => $val['field'],
                    'type' => $val['type'],
                    'notnull' => (bool)('' === $val['null']),
                    'default' => $val['default'],
                    'primary' => $isPkString,
                    'auto' => (strtolower($val['extra']) == 'auto_increment'),
                ];
            }
        }

        return $infoArray;
    }

    /**
     * 过滤不能操作的字段
     * @param array $data
     * @param array $allowedFieldsArray
     * @return array
     */
    private function filterFields(array $data, array $allowedFieldsArray)
    {
        $allowedArray = [];
        foreach (array_keys($data) as $v)
        {
            if (in_array($v, $allowedFieldsArray))
                $allowedArray[$v] = $data[$v];
        }

        return $allowedArray;
    }


    /**
     * 获取最后一条执行sql
     * @return mixed
     */
    public function getLastSql()
    {
        return $this->sql;
    }


    /**
     * 启动事务
     */
    public function startTrans()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        $this->pdo->rollback();
    }
}