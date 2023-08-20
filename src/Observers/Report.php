<?php

namespace Iyuu\Spider\Observers;

use InvalidArgumentException;
use Iyuu\Spider\Contract\Observer;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;
use Iyuu\Spider\Utils;
use support\Log;
use Throwable;

/**
 * 示例：一个观察者
 */
class Report implements Observer
{
    public static function update(Sites $sites, Torrents $torrent): void
    {
        if (!class_exists("\\db\\Bencode")) {
            return;
        }
        if (!$sites->getParams()->daemon) {
            Utils::echo(sprintf('站点：%s | 页码：%s', $sites->getParams()->site, $sites->currentPage()));
        }
        //print_r($torrent->toArray());

        try {
            //1. 控制台打印
            self::step1_echoTitle($sites, $torrent);
            //2. 检查种子id
            self::step2_checkTorrentId($sites, $torrent);
            //3. 前置操作：流量控制等
            self::step3_beforeSiteLimit($sites, $torrent);
            //4. 查重
            self::step4_find($sites, $torrent);
            //5. 查找本地种子文件
            if (self::step5_existsTorrentFile($sites, $torrent)) {
                //5.1 读取本地种子文件
                $metadata = '';
            } else {
                //6. 下载种子
                $metadata = false;
            }
            //7. 检查种子元数据
            self::step7_checkTorrentMetadata($sites, $torrent, $metadata);
            //8. 上报种子元数据
            self::step8_pushTorrentInfo($sites, $torrent, $metadata);
            //9. 保存种子元数据
            self::step9_saveTorrentFile($sites, $torrent, $metadata);
            //10. 后置操作：流量控制等
            self::step10_after($sites, $torrent);
        } catch (Throwable $throwable) {
            Log::error('[种子观察者]异常：' . $throwable->getMessage(), $torrent->toArray());
        }
    }

    /**
     * 控制台显示种子信息
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    private static function step1_echoTitle(Sites $sites, Torrents $torrent): void
    {
        if ($sites->getParams()->daemon) {
            return;
        }
        $body = [
            '主标题：' . $torrent->h1 ?? '',
            '副标题：' . $torrent->title ?? '',
            '详情页：' . $torrent->details ?? '',
        ];
        echo implode(PHP_EOL, $body) . PHP_EOL;
    }

    /**
     * 检查种子id
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    private static function step2_checkTorrentId(Sites $sites, Torrents $torrent): void
    {
        $id = $torrent->id ?? '';
        if (empty($id) || false === ctype_digit((string)$id)) {
            throw new InvalidArgumentException(sprintf('【%s】种子ID非数字', $sites->getParams()->site));
        }
    }

    /**
     * 前置操作：流量控制
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    private static function step3_beforeSiteLimit(Sites $sites, Torrents $torrent): void
    {}

    /**
     * 查重
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    private static function step4_find(Sites $sites, Torrents $torrent): void
    {}

    /**
     * 查找本地种子文件
     * @param Sites $sites
     * @param Torrents $torrent
     * @return bool
     */
    private static function step5_existsTorrentFile(Sites $sites, Torrents $torrent): bool
    {
        return false;
    }

    /**
     * 下载种子
     * @param Sites $sites
     * @param Torrents $torrent
     * @return bool|string
     */
    private static function step6_downloadTorrentFile(Sites $sites, Torrents $torrent): bool|string
    {
        return true;
    }

    /**
     * 检查种子元数据
     * @param Sites $sites
     * @param Torrents $torrent
     * @param bool|string $metadata
     * @return void
     */
    private static function step7_checkTorrentMetadata(Sites $sites, Torrents $torrent, bool|string $metadata): void
    {}

    /**
     * 上报种子元数据
     * @param Sites $sites
     * @param Torrents $torrent
     * @param string $metadata
     * @return void
     */
    private static function step8_pushTorrentInfo(Sites $sites, Torrents $torrent, string $metadata): void
    {}

    /**
     * 保存种子元数据到文件中
     * @param Sites $sites
     * @param Torrents $torrent
     * @param string $metadata
     * @return void
     */
    private static function step9_saveTorrentFile(Sites $sites, Torrents $torrent, string $metadata): void
    {}

    /**
     * 后置操作：流量控制
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    private static function step10_after(Sites $sites, Torrents $torrent): void
    {
    }
}
