<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Contract\Observer;
use Iyuu\Spider\Support\DataStruct;
use RuntimeException;
use think\Collection;

/**
 * 种子数据结构
 * @property int id 种子ID
 * @property string h1 主标题
 * @property string title 副标题
 * @property string details 详情页
 * @property string download 下载页
 * @property string filename 文件名
 * @property string type 促销类型
 */
class Torrents extends DataStruct
{
    /**
     * 观察者
     * @var Observer[]
     */
    private static array $observers = [];

    /**
     * 数据转换为模型对象
     * @param array $items
     * @param Sites $sites
     * @return Collection
     */
    public static function toCollection(array $items, Sites $sites): Collection
    {
        if (empty($items)) {
            return new Collection([]);
        }
        foreach ($items as $key => &$item) {
            $item = new static($item);
            self::notify($sites, $item);
        }
        return new Collection($items);
    }

    /**
     * 添加观察者
     * @param string $observer
     * @return void
     */
    final public static function observer(string $observer): void
    {
        if (!is_a($observer, Observer::class, true)) {
            throw new RuntimeException('未实现观察者接口');
        }
        //去重
        if (!in_array($observer, self::$observers, true)) {
            self::$observers[] = $observer;
        }
    }

    /**
     * 通知观察者
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    final protected static function notify(Sites $sites, Torrents $torrent): void
    {
        if (empty(self::$observers)) {
            return;
        }
        foreach (self::$observers as $observer) {
            $observer::update($sites, $torrent);
        }
    }
}