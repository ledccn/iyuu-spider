<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Api\SiteModel;
use RuntimeException;

/**
 * 站点爬虫工厂类
 */
final class Factory
{
    /**
     * 服务提供者
     * @var string[]
     */
    private static array $provider = [
        'm-team' => \Iyuu\Spider\Sites\mteam\Handler::class,
    ];

    /**
     * 创建
     * @param Config $config 本地配置
     * @param SiteModel $siteModel 服务器配置
     * @param Params $params 启动参数
     * @return Sites
     */
    public static function create(Config $config, SiteModel $siteModel, Params $params): Sites
    {
        $site = $siteModel->site;
        $provider = Factory::getProvider($site);
        if (!$provider) {
            $provider = __NAMESPACE__ . "\\{$site}\\Handler";
        }
        Factory::checkProvider($provider);

        return new $provider($config, $siteModel, $params);
    }

    /**
     * 验证服务提供者类
     * @param string $provider 服务提供者的完整类名
     * @return void
     */
    public static function checkProvider(string $provider): void
    {
        if (!class_exists($provider)) {
            throw new RuntimeException('服务提供者类不存在:' . $provider);
        }
        if (!is_a($provider, Sites::class, true)) {
            throw new RuntimeException($provider . '未继承：' . Sites::class);
        }
    }

    /**
     * 获取服务提供者
     * @param string $site 站点标识
     * @return string|null
     */
    public static function getProvider(string $site): ?string
    {
        return Factory::$provider[$site] ?? null;
    }

    /**
     * 注册服务提供者
     * @param string $site 站点标识
     * @param string $provider 服务提供者的完整类名
     */
    public static function setProvider(string $site, string $provider): void
    {
        Factory::checkProvider($provider);
        Factory::$provider[$site] = $provider;
    }

    /**
     * 获得当前命名空间
     * @return string
     */
    final public static function getNamespace(): string
    {
        return __NAMESPACE__;
    }
}
