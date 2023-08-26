<?php

namespace Iyuu\Spider\Pipeline\Report;

use InvalidArgumentException;
use Iyuu\Spider\Contract\Pipeline;
use Iyuu\Spider\Sites\Payload;

/**
 * 检查种子id
 */
class CheckTorrentId implements Pipeline
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
        $id = $torrent->id ?? '';
        if (empty($id) || false === ctype_digit((string)$id)) {
            throw new InvalidArgumentException(sprintf('【%s】种子ID非数字', $sites->getParams()->site));
        }

        return $next($payload);
    }
}
