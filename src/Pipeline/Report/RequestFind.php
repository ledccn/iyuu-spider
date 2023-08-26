<?php

namespace Iyuu\Spider\Pipeline\Report;

use Iyuu\Spider\Api\SpiderClient;
use Iyuu\Spider\Contract\Pipeline;
use Iyuu\Spider\Exceptions\BadRequestException;
use Iyuu\Spider\Exceptions\ServerErrorHttpException;
use Iyuu\Spider\Sites\Payload;

/**
 * 发起查询请求
 */
class RequestFind implements Pipeline
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
        $client = SpiderClient::getInstance();
        $client->findTorrent($sites->getSiteModel()->site, $torrent->id);

        return $next($payload);
    }
}
