TH Framework

自己写了一个简单的PHP框架，仅供参考

路由仅支持PATHINFO模式，路由必须得在 /router/web.php 中注册。路由找不到会使用默认配置的模块、控制器、方法

V(视图层)中也仅仅实现了解析变量、IF、foreach及注释等

需自己创建 runtime 缓存目录，并保证有可写权限