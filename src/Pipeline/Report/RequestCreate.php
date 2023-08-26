<?php

namespace Iyuu\Spider\Pipeline\Report;

use Iyuu\Spider\Api\SpiderClient;
use Iyuu\Spider\Contract\Pipeline;
use Iyuu\Spider\Contract\Reseed;
use Iyuu\Spider\Exceptions\BadRequestException;
use Iyuu\Spider\Exceptions\ParseMetadataException;
use Iyuu\Spider\Exceptions\ServerErrorHttpException;
use Iyuu\Spider\Sites\Payload;
use Iyuu\Spider\Sites\Torrents;
use RuntimeException;

/**
 * 上报种子元数据
 */
class RequestCreate implements Pipeline
{
    /**
     * @param Payload $payload
     * @param callable $next
     * @return mixed
     * @throws BadRequestException
     * @throws ServerErrorHttpException
     */
    public static function process(Payload $payload, callable $next): mixed
    {
        $sites = $payload->sites;
        $torrent = $payload->torrent;
        $metadata = $torrent->metadata;
        $decoder = Torrents::$decoder;
        if (class_exists($decoder) && is_a($decoder, Reseed::class, true)) {
            $data = $decoder::reseed($metadata);
            if (empty($data)) {
                throw new ParseMetadataException('种子元数据解码错误');
            }

            $client = SpiderClient::getInstance();
            $client->createTorrent($sites->getSiteModel()->site, $torrent, $data);

            return $next($payload);
        } else {
            throw new RuntimeException('默认的种子解码器不存在或未实现契约');
        }
    }
}
