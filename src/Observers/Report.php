<?php

namespace Iyuu\Spider\Observers;

use Iyuu\Spider\Contract\Observer;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;

/**
 * 示例：一个观察者
 */
class Report implements Observer
{
    public static function update(Sites $sites, Torrents $torrent): void
    {
        echo $sites->getSiteModel()->site . PHP_EOL;
        //print_r($torrent->toArray());
    }
}
