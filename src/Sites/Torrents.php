<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Contract\Observer;
use Iyuu\Spider\Support\DataStruct;
use RuntimeException;
use think\Collection;
use Throwable;

/**
 * 种子数据结构
 * @property int $id 种子ID
 * @property string $h1 主标题
 * @property string $title 副标题
 * @property string $details 详情页
 * @property string $download 下载页
 * @property string $filename 文件名
 * @property string $type 促销类型
 * @property ?int $group_id 种子分组ID（特有字段：海豚、海报、皮等）
 */
class Torrents extends DataStruct
{
    /**
     * 默认的种子解码器
     * - 实现契约 \Iyuu\Spider\Contract\Reseed::class
     * @var string
     */
    public static string $decoder = "\\db\\Bencode";
    /**
     * 观察者
     * @var Observer[]
     */
    private static array $observers = [];
    /**
     * 下载种子是否需要cookie
     * @var bool
     */
    protected bool $cookieRequired = true;

    /**
     * 设置下载种子是否需要cookie
     * @param bool $cookieRequired 下载种子是否需要cookie
     */
    public function setCookieRequired(bool $cookieRequired): void
    {
        $this->cookieRequired = $cookieRequired;
    }

    /**
     * 判断下载种子是否需要cookie
     * @return bool
     */
    public function isCookieRequired(): bool
    {
        return $this->cookieRequired;
    }

    /**
     * 数据转换为种子对象
     * @param array $items 二维的种子数组
     * @param Sites $sites 站点对象
     * @param bool $cookieRequired 下载种子是否需要cookie
     * @return Collection
     */
    public static function toCollection(array $items, Sites $sites, bool $cookieRequired = true): Collection
    {
        if (empty($items)) {
            return new Collection([]);
        }
        foreach ($items as $key => &$item) {
            $item = new static($item);
            $item->setCookieRequired($cookieRequired);
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
     * @param Sites $sites 站点对象
     * @param Torrents $torrent 单个种子对象
     * @return void
     */
    final protected static function notify(Sites $sites, Torrents $torrent): void
    {
        if (empty(self::$observers)) {
            return;
        }
        foreach (self::$observers as $observer) {
            try {
                $observer::update($sites, $torrent);
            } catch (Throwable $throwable) {}
        }
    }
}