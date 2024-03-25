<?php

namespace Iyuu\Spider\Sysop;

use Iyuu\Spider\Api\SpiderClient;
use Iyuu\Spider\Contract\Reseed;
use Iyuu\Spider\Exceptions\BadRequestException;
use Iyuu\Spider\Exceptions\ParseMetadataException;
use Iyuu\Spider\Exceptions\ServerErrorHttpException;
use Iyuu\Spider\Sites\Torrents;
use RuntimeException;

/**
 * 向IYUU服务器，推送种子特征hash
 */
class PushTorrent
{
    /**
     * 创建
     * @param string $site 站点标识
     * @param Torrents $torrent 种子对象
     * @return void
     * @throws BadRequestException
     * @throws ServerErrorHttpException
     */
    public static function create(string $site, Torrents $torrent): void
    {
        $metadata = $torrent->metadata;
        $decoder = Torrents::$decoder;
        if (class_exists($decoder) && is_a($decoder, Reseed::class, true)) {
            $data = $decoder::reseed($metadata);
            if (empty($data)) {
                throw new ParseMetadataException('种子元数据解码错误');
            }

            $client = SpiderClient::getInstance();
            $client->createTorrent($site, $torrent, $data);
        } else {
            throw new RuntimeException('默认的种子解码器不存在或未实现契约');
        }
    }
}
