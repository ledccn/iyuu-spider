<?php

namespace Iyuu\Spider\Contract;

use InvalidArgumentException;

/**
 * 资源路径，回退枚举
 */
enum Route: string
{
    /**
     * 种子列表页
     */
    case N1 = 'torrents.php?incldead=0&page={page}';
    /**
     * 种子RSS页面
     */
    case N2 = 'torrentrss.php?rows=50&linktype=dl&passkey={passkey}';
    /**
     * 种子列表页
     */
    case N3 = 'torrents?page={page}';
    /**
     * 种子RSS页面
     */
    case N4 = 'rss/13.{rsskey}';
    /**
     * 种子列表页
     */
    case N5 = 'special.php?incldead=0&page={page}';

    /**
     * 检查枚举名字
     * @param string $name
     * @return bool
     */
    public static function hasName(string $name): bool
    {
        return in_array(strtoupper($name), array_column(self::cases(), 'name'));
    }

    /**
     * 获取枚举值
     * @param string $name
     * @return string
     */
    public static function getValue(string $name): string
    {
        $name = strtoupper($name);
        $list = self::toArray();
        if (!array_key_exists($name, $list)) {
            throw new InvalidArgumentException('路由不存在');
        }

        return $list[$name];
    }

    /**
     * 枚举条目转为数组
     * - 名 => 值
     * @return array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * 枚举条目转为数组
     * - 值 => 名
     * @return array
     */
    public static function forSelect(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_column(self::cases(), 'name')
        );
    }
}
