<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Api\SiteModel;
use Iyuu\Spider\Contract\Downloader;
use Iyuu\Spider\Contract\PageUriBuilder;
use Iyuu\Spider\Contract\Processor;
use Iyuu\Spider\Contract\ProcessorXml;
use RuntimeException;
use Throwable;

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
        '1ptba' => \Iyuu\Spider\Sites\site1ptba\Handler::class,
        '52pt' => \Iyuu\Spider\Sites\site52pt\Handler::class,
        'hd-torrents' => \Iyuu\Spider\Sites\hdtorrents\Handler::class,
    ];//PROVIDER_END不要删除这里

    /**
     * 创建
     * @param Config $localConfig 本地配置
     * @param SiteModel $serverConfig 服务器配置
     * @param Params $cliParams 启动参数
     * @return Sites
     */
    public static function create(Config $localConfig, SiteModel $serverConfig, Params $cliParams): Sites
    {
        $site = $serverConfig->site;
        $provider = self::getProvider($site);
        if (!$provider) {
            $provider = static::getNamespace() . "\\{$site}\\" . self::DEFAULT_CLASSNAME;
        }
        self::checkProvider($provider);

        return new $provider($localConfig, $serverConfig, $cliParams);
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
     * 所有服务提供者
     * @return string[]
     */
    final public static function allProvider(): array
    {
        return self::$provider;
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

    /**
     * 获取服务提供者类状态
     * @param string $provider
     * @return array
     */
    final public static function providerStatus(string $provider): array
    {
        return [
            is_a($provider, Processor::class, true),
            is_a($provider, Downloader::class, true),
            is_a($provider, PageUriBuilder::class, true),
            is_a($provider, ProcessorXml::class, true),
            $provider,
        ];
    }

    /**
     * 获取支持的站点列表
     * @return array
     */
    final public static function siteList(): array
    {
        $rows = [];
        //服务提供者
        foreach (static::allProvider() as $site => $provider) {
            $rows[$site] = [
                $site,
                ... self::providerStatus($provider)
            ];
        }

        //服务类
        foreach (glob(self::getDirname() . '/*/' . self::DEFAULT_CLASSNAME . '.php') as $filename) {
            try {
                $site = basename(dirname($filename));
                $provider = static::getNamespace() . "\\{$site}\\" . self::DEFAULT_CLASSNAME;
                self::checkProvider($provider);
                $site = $provider::SITE_NAME;
                $rows[$site] = [
                    $site,
                    ... self::providerStatus($provider)
                ];
            } catch (Throwable $throwable) {
            }
        }

        //多维数组排序
        $_site = [];
        foreach ($rows as $key => $value) {
            $_site[$key] = $value[0];
        }
        array_multisort($_site, SORT_ASC, $rows);

        return $rows;
    }
}
