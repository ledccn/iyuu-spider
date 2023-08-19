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
     * 默认类名
     */
    final const DEFAULT_CLASSNAME = 'Handler';
    /**
     * 服务提供者
     * @var string[]
     */
    private static array $provider = [
        'm-team' => \Iyuu\Spider\Sites\mteam\Handler::class,
    ];//PROVIDER_END不要删除这里

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
        $provider = self::getProvider($site);
        if (!$provider) {
            $provider = static::getNamespace() . "\\{$site}\\" . self::DEFAULT_CLASSNAME;
        }
        self::checkProvider($provider);

        return new $provider($config, $siteModel, $params);
    }

    /**
     * 验证服务提供者类
     * @param string $provider 服务提供者的完整类名
     * @return void
     */
    final public static function checkProvider(string $provider): void
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
    final public static function getProvider(string $site): ?string
    {
        return self::$provider[$site] ?? null;
    }

    /**
     * 所有服务提供者
     * @return string[]
     */
    final public static function allProvider(): array
    {
        return self::$provider;
    }

    /**
     * 注册服务提供者
     * @param string $site 站点标识
     * @param string $provider 服务提供者的完整类名
     */
    final public static function setProvider(string $site, string $provider): void
    {
        Factory::checkProvider($provider);
        Factory::$provider[$site] = $provider;
    }

    /**
     * 获得当前命名空间
     * @return string
     */
    public static function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    /**
     * 获取当前文件路径
     * @return string
     */
    final public static function getFilepath(): string
    {
        return __FILE__;
    }

    /**
     * 获取当前目录名
     * @return string
     */
    final public static function getDirname(): string
    {
        return __DIR__;
    }
}
