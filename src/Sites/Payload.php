<?php

namespace Iyuu\Spider\Sites;

/**
 * 有效载荷
 */
class Payload
{
    /**
     * @param Sites $sites
     * @param Torrents $torrent
     */
    public function __construct(public Sites $sites, public Torrents $torrent)
    {
    }
}
