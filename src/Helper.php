<?php

namespace Iyuu\Spider;

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
        return !is_file($file) || unlink($file);
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
        return !is_file($file) || unlink($file);
    }
}
