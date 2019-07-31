<?php
/**
 * Created by PhpStorm.
 * User: zxm
 * Date: 2019/7/30
 * Time: 10:23 PM
 */

namespace core;


use design\Facade;

/**
 * 视图基类
 * Class View
 * @package core
 */
class View
{

    //模版变量
    private $assignArray = [];

    //模版文件内容
    private $fileContent;

    /**
     * 设置模版变量
     * @param array $assign
     * @return $this
     */
    public function setAssign(array $assign)
    {
        if (!empty($this->assignArray))
            array_merge($this->assignArray, $assign);
        else
            $this->assignArray = $assign;

        return $this;
    }

    /**
     * 缓存及解析模版中的数据
     * @param $file
     * @return mixed
     */
    public function display($file)
    {
        $fileString = APP_PATH . $file . EXT;
        if (!file_exists($fileString))
            exit('template does not exist');

        $this->fileContent = file_get_contents($fileString);
        if ($this->fileContent === false) //读取失败
            exit('template file read failed');

        //获取模块名
        $moduleString = strstr($file, '/', true);
        //获取方法名
        $actionString = substr($file, strrpos($file, DS)+1);
        //获取控制器名
        $getControllerString = substr($file, 0, strrpos($file, '/'.$actionString));
        $controllerString = substr($getControllerString, strrpos($getControllerString, '/')+1);
        //缓存文件目录
        $cachePathDir = Facade::Config('get', 'tpl_cache_path') . strtolower($moduleString . '/' . $controllerString);
        if (!is_dir($cachePathDir))
            mkdir($cachePathDir, 0777, true);
        //缓存文件
        $cachePathString = $cachePathDir . '/' . $actionString . EXT;

        if (Facade::Config('get', 'tpl_cache')) //开启缓存
        {
            //文件存在且在缓存时间内直接返回缓存文件
            if (file_exists($cachePathString)
                && (time() - filemtime($cachePathString) < (int)Facade::Config('get', 'tpl_cache_time')))
                return include $cachePathString;
        }

        //解析模版中的数据
        $this->patternContent();

        //缓存文件
        file_put_contents($cachePathString, $this->fileContent);

        include $cachePathString;

        exit();
    }

    /**
     * 解析模版中的数据
     */
    private function patternContent()
    {
        //解析变量
        $this->patternVar();

        //解析if
        $this->patternIf();

        //解析foreach
        $this->patternForeach();

        //解析注释
        $this->patternComment();
    }

    /**
     * 解析变量
     */
    private function patternVar()
    {
        $pattern = '/\{\$(\w+)\}/';

        if (preg_match($pattern, $this->fileContent, $match))
            $this->fileContent = preg_replace($pattern, "<?php echo \$this->assignArray['$1']; ?>", $this->fileContent);
    }

    /**
     * 解析if
     */
    private function patternIf()
    {
        //匹配 {if $xxx}
        $patternStart = '/\{if\s+\$(\w+)\}/';
        //匹配 {else}
        $patternElse = '/\{else\}/';
        //匹配 {/if}
        $patternEnd = '/\{\/if\}/';

        if (preg_match($patternStart, $this->fileContent, $startMatch))
            $this->fileContent = preg_replace($patternStart, "<?php if (\$this->assignArray['$1']){ ?>", $this->fileContent);

        if (preg_match($patternElse, $this->fileContent, $elseMatch))
            $this->fileContent = preg_replace($patternElse, "<?php }else{  ?>", $this->fileContent);

        if (preg_match($patternEnd, $this->fileContent, $endMatch))
            $this->fileContent = preg_replace($patternEnd, "<?php } ?>", $this->fileContent);
    }

    /**
     * 解析foreach
     */
    private function patternForeach()
    {
        //匹配 {foreach $array as key => value}
        $patternStart = '/\{foreach\s+\$(\w+)\s+as\s+(\w+)\s+\=>\s+(\w+)\}/';
        //匹配 foreach中的内容 {@key}{@value}
        $patternContent = '/\{@(\w+)\}/';
        //匹配 {/foreach}
        $patternEnd = '/\{\/foreach\}/';

        if (preg_match($patternStart, $this->fileContent, $startMatch))
            $this->fileContent = preg_replace($patternStart, "<?php foreach(\$this->assignArray['$1'] as \$$2 => \$$3){ ?>", $this->fileContent);

        if (preg_match($patternContent, $this->fileContent, $contentMatch))
            $this->fileContent = preg_replace($patternContent, "<?php echo \$$1 ?>", $this->fileContent);

        if (preg_match($patternEnd, $this->fileContent, $endMatch))
            $this->fileContent = preg_replace($patternEnd, "<?php } ?>", $this->fileContent);
    }


    /**
     * 解析注释
     */
    private function patternComment()
    {
        //匹配{#}xxx{/#}
        $pattern = '/\{#\}(.*)\{\/#\}/';

        if (preg_match($pattern, $this->fileContent, $startMatch))
            $this->fileContent = preg_replace($pattern, "<?php /** $1 */ ?>", $this->fileContent);
    }
}