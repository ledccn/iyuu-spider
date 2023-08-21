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
}
