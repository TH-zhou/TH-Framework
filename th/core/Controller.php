<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/28
 * Time: 9:40 PM
 */

namespace core;

/**
 * 控制器基类
 * Class Controller
 * @package core
 */
class Controller
{

    //模版变量
    protected $assignArray = [];

    //视图模版
    protected $tpl;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        //注册视图实例
        \design\Di::getInstance()->set('VIEW', new View());
    }

    /**
     * 设置视图模版
     * @param $tpl
     */
    final public function setTpl($tpl)
    {
        $this->tpl = $tpl;
    }


    /**
     * 设置变量
     * @param $assign
     * @param string $val
     */
    final public function assign($assign, $val = '')
    {
        if (is_array($assign))
        {
            foreach ($assign as $k => $v)
                $this->assignArray[$k] = $v;
        }
        else
            $this->assignArray[$assign] = $val;
    }


    /**
     * 渲染模版
     * @param string $tpl 模版路径
     */
    final public function display($tpl = '')
    {
        $this->tpl = $tpl ?: $this->tpl;

        //获取视图实例
        $viewObj = \design\Di::getInstance()->get('VIEW');

        $viewObj->setAssign($this->assignArray)->display($this->tpl);
    }
}