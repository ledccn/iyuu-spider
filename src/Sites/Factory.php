<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Api\SiteModel;
use RuntimeException;

/**
 * 站点爬虫工厂类
 */
class Factory
{
    /**
     * 创建
     * @param Config $config 本地配置
     * @param SiteModel $siteModel 服务器配置
     * @param Params $params 启动参数
     * @return Sites
     */
    final public static function create(Config $config, SiteModel $siteModel, Params $params): Sites
    {
        $site = $siteModel->site;
        $class = __NAMESPACE__ . "\\{$site}\\Handler";
        if (!class_exists($class)) {
            throw new RuntimeException('站点处理类不存在:' . $class);
        }
        if (!is_a($class, Sites::class, true)) {
            throw new RuntimeException($class . '未继承：' . Sites::class);
        }

        return new $class($config, $siteModel, $params);
    }
}
