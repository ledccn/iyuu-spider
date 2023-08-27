# 安装 Install

```
composer require iyuu/spider
```


# 使用 Usage

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