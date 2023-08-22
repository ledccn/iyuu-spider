<?php

namespace Iyuu\Spider;

use Iyuu\Spider\Contract\Downloader;

/**
 * 助手类
 */
class Helper
{
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
     * 存放站点列表页为空的计数文件
     * @param string $site
     * @return string
     */
    public static function siteEmptyListFilename(string $site): string
    {
        return runtime_path("/page/$site.empty");
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
}
