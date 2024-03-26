# 安装 Install

```
composer require iyuu/spider
```


# 仓库地址
https://github.com/ledccn/iyuu-spider



# 使用 Usage

### 部署步骤
```shell
composer install

/config/sites.php

/runtime/Bencode.php

.env
```

## 运行爬虫
查看命令帮助：
```shell
php webman spider -h
```

输出如下：
```shell
david@MacBook iyuu-spider % php webman spider -h    
Description:
  IYUU出品的PT站点页面解析器

Usage:
  spider [options] [--] <site> [<action>]

Arguments:
  site                  站点名称
  action                start|stop|restart|reload|status|connections [default: ""]

Options:
      --type[=TYPE]     爬虫类型:cookie,rss [default: "cookie"]
      --uri[=URI]       统一资源标识符 [default: ""]
      --begin[=BEGIN]   开始页码 [default: ""]
      --end[=END]       结束页码 [default: ""]
      --daemon          守护进程

```



## 创建爬虫

查看命令帮助：
```shell
php webman make:spider -h
```

输出如下：
```shell
david@MacBook iyuu-spider % php webman make:spider -h
Description:
  IYUU出品的命令行创建解析器

Usage:
  make:spider <name> [<type>]

Arguments:
  name                  解析器服务提供者的类名
  type                  解析器的框架类型 [default: "0"]

```


# 支持站点步骤

- 注册站点账号
- 登录站点账号
- 在服务器添加站点基础信息
- 在本地添加站点基础信息
- 命令行创建站点的解析器
- 命令行运行站点的解析器，测试抓取效果
- 完成


## 目录结构
```tree
├───src
│   ├───Api         IYUU接口
│   ├───Command     支持的命令
│   ├───config      插件配置
│   │   └───plugin
│   ├───Contract    接口契约
│   ├───Exceptions  异常类
│   ├───Frameworks  站点所属的框架
│   │   ├───NexusPHP
│   │   └───UNIT3D
│   ├───Observers   观察者
│   ├───Pipeline    管道流水线
│   │   └───Report
│   ├───Sites       各站点解析器
│   │   ├─── ...
│   ├───Support
│   └───Traits      特性
└───tests
```


## 站点SYSOP对接

```php
$config = new \Iyuu\Spider\Sites\Config([
'provider' => '继承 \Iyuu\Spider\Sysop\AnySite 开发的类，实现download()方法'
]);
$handler = new \Iyuu\Spider\Sysop\Sysop('站点标识', $config);
//二维数组，参考 \Iyuu\Spider\Sites\Torrents
$items = []
$handler->run($items);
```
