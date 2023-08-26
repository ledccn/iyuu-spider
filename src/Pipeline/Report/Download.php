<?php

namespace Iyuu\Spider\Pipeline\Report;

use Iyuu\Spider\Contract\Pipeline;
use Iyuu\Spider\Exceptions\DownloadTorrentException;
use Iyuu\Spider\Exceptions\EmptyMetadataException;
use Iyuu\Spider\Sites\Payload;

/**
 * 下载种子
 */
class Download implements Pipeline
{
    /**
     * @param Payload $payload
     * @param callable $next
     * @return mixed
     * @throws EmptyMetadataException|DownloadTorrentException
     */
    public static function process(Payload $payload, callable $next): mixed
    {
        $sites = $payload->sites;
        $torrent = $payload->torrent;
        $metadata = $sites->download($torrent);
        //检查种子元数据
        if (is_bool($metadata) || empty($metadata)) {
            throw new EmptyMetadataException('种子元数据为空');
        }
        $torrent->metadata = $metadata;

        return $next($payload);
    }
}
