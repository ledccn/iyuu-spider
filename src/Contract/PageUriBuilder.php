<?php

namespace Iyuu\Spider\Contract;

/**
 * 页面URI构造接口
 */
interface PageUriBuilder
{
    /**
     * 构造页面URI
     * @param int $page 页码
     * @param Route|string|null $route 路由实例或路由枚举值
     * @return string
     */
    public static function pageBuilder(int $page, Route|string $route = null): string;

    /**
     * 当前页
     * @return int
     */
    public function currentPage(): int;

    /**
     * 下一页
     * @return int
     */
    public function nextPage(): int;
}