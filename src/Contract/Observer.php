<?php

namespace Iyuu\Spider\Contract;

use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;

/**
 * 观察者接口
 */
interface Observer
{
    /**
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    public static function update(Sites $sites, Torrents $torrent): void;
}