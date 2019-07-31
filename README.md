TH Framework

自己写了一个简单的PHP框架，仅供参考

路由仅支持PATHINFO模式，路由必须得在 /router/web.php 中注册。路由找不到会使用默认配置的模块、控制器、方法

V(视图层)中也仅仅实现了解析变量、IF、foreach及注释等

目录结构：
─application            应用目录
│  ├─module_name        模块目录
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │
│  ├─common.php         公共函数文件
│
├─config                应用配置目录
│  ├─config.php         全局配置
│  ├─database.php       数据库配置
│
├─router                路由定义目录
│  ├─web.php            路由定义
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│
├─th                    框架系统目录
│  ├─core               框架核心类库目录
│  ├─design             设计模式类库
│
├─runtime               应用的运行时目录
