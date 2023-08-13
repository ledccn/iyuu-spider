<?php

namespace Iyuu\Spider\Sites\beitai;

use Iyuu\Spider\Frameworks\NexusPHP\Parser;
use Iyuu\Spider\Observers\Report;
use Iyuu\Spider\Sites\Torrents;

/**
 * 爬虫句柄
 */
class Handler extends Parser
{
    protected function init(): void
    {
        Torrents::observer(Report::class);
    }
}
