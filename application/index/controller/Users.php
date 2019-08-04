<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/8/4
 * Time: 1:44 PM
 */
namespace app\index\controller;

use core\Controller;
use core\Db;

class Users extends Controller
{

    public function index()
    {
        $aa = Db::name('users')->alias('a')->fields('a.id,a.name,a.tel,b.age')
            ->join('userinfo b', 'a.id = b.userid', 'left')
            ->limit([3,2])
            ->select();
        echo '<pre>';
        print_r($aa);
    }
}