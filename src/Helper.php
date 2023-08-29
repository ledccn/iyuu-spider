<?php

namespace Iyuu\Spider;

use Iyuu\Spider\Contract\Downloader;
use Iyuu\Spider\Contract\Route;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 助手类
 */
class Helper
{
    /**
     * 读取或保存user-agent
     * @param string $userAgent
     * @return string
     */
    public static function selfUserAgent(string $userAgent = ''): string
    {
        $filename = runtime_path('user-agent.local');
        if ($userAgent) {
            file_put_contents($filename, $userAgent);
        } else {
            clearstatcache();
            if (!is_file($filename)) {
                return Downloader::USER_AGENT;
            }

            $userAgent = file_get_contents($filename) ?: Downloader::USER_AGENT;
        }
        return $userAgent;
    }

    /**
     * 删除站点页码文件
     * @param string $site
     * @return bool
     */
    public static function deletePageFilename(string $site): bool
    {
        clearstatcache();
        $file = static::sitePageFilename($site);
        if (is_file($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * 存放站点页码的文件
     * @param string $site
     * @return string
     */
    public static function sitePageFilename(string $site): string
    {
        return runtime_path("/page/$site.page");
    }

    /**
     * 删除站点列表页为空的计数文件
     * @param string $site
     * @return bool
     */
    public static function deleteEmptyListFilename(string $site): bool
    {
        clearstatcache();
        $file = static::siteEmptyListFilename($site);
        if (is_file($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * 存放站点列表页为空的计数文件
     * @param string $site
     * @return string
     */
    public static function siteEmptyListFilename(string $site): string
    {
        return runtime_path("/page/$site.empty");
    }

    /**
     * 支持的路由规则名字
     * @param OutputInterface $output
     * @return void
     */
    public static function routeTable(OutputInterface $output): void
    {
        $headers = ['name', 'value'];
        $rows = [];
        foreach (Route::cases() as $route) {
            $rows[] = [$route->name, $route->value];
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
    }
}
