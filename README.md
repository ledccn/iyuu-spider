# 安装 Install

```
composer require iyuu/spider
```


# 使用 Usage

查看命令帮助：
```shell
php webman spider -h
```

输出如下：
```
david@MacBook webman % php webman spider -h    
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
