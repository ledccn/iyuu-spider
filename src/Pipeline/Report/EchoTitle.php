<?php

namespace Iyuu\Spider\Pipeline\Report;

use Iyuu\Spider\Contract\Pipeline;
use Iyuu\Spider\Sites\Payload;

/**
 * 控制台显示种子信息
 */
class EchoTitle implements Pipeline
{
    /**
     * @param Payload $payload
     * @param callable $next
     * @return mixed
     */
    public static function process(Payload $payload, callable $next): mixed
    {
        $sites = $payload->sites;
        $torrent = $payload->torrent;
        if (!$sites->getParams()->daemon) {
            $body = [
                '主标题：' . $torrent->h1 ?? '',
                '副标题：' . $torrent->title ?? '',
                '详情页：' . $torrent->details ?? '',
            ];
            echo implode(PHP_EOL, $body) . PHP_EOL;
        }

        return $next($payload);
    }
}
