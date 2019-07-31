<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/28
 * Time: 9:40 PM
 */

namespace app\index\controller;

use \core\Controller;

class Index extends Controller
{

    public function index()
    {
        $this->display();
    }

    public function testTpl()
    {
        $assignArray = [
            'string1' => '测试string1',
            'string2' => '测试string2',
            'string3' => '测试string3',
            'bool' => '测试bool',
            'array' => ['a' => 123, 'b' => 456]
        ];

        $this->assign($assignArray);
        $this->display();
    }

    public function world()
    {
        echo 'hello, TH Framework';
    }

    public function testParams($param)
    {
        echo '参数值为:'. $param;
    }
}