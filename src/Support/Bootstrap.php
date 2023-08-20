<?php

namespace Iyuu\Spider\Support;

use Iyuu\Spider\Sites\Torrents;
use Workerman\Worker;

/**
 * 进程启动前初始化
 */
class Bootstrap implements \Webman\Bootstrap
{
    /**
     * onWorkerStart
     * @param Worker|null $worker
     * @return void
     */
    public static function start(?Worker $worker): void
    {
        $config = config('torrent_observer', []);
        foreach ($config as $observer) {
            Torrents::observer($observer);
        }
    }
}
