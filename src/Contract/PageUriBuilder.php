<?php

namespace Iyuu\Spider\Contract;

/**
 * 页面URI构造接口
 */
interface PageUriBuilder
{
    /**
     * 构造页面URI
     * @param int $page
     * @return string
     */
    public static function pageBuilder(int $page): string;
}