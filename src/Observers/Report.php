<?php

namespace Iyuu\Spider\Observers;

use Iyuu\Spider\Container;
use Iyuu\Spider\Contract\Observer;
use Iyuu\Spider\Contract\Reseed;
use Iyuu\Spider\Exceptions\BadRequestException;
use Iyuu\Spider\Exceptions\DownloadHtmlException;
use Iyuu\Spider\Exceptions\DownloadTorrentException;
use Iyuu\Spider\Exceptions\ServerErrorHttpException;
use Iyuu\Spider\Pipeline\Report\CheckTorrentId;
use Iyuu\Spider\Pipeline\Report\Download;
use Iyuu\Spider\Pipeline\Report\EchoTitle;
use Iyuu\Spider\Pipeline\Report\RequestCreate;
use Iyuu\Spider\Pipeline\Report\RequestFind;
use Iyuu\Spider\Sites\Payload;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;
use Iyuu\Spider\Utils;
use Ledc\Pipeline\Pipeline;
use support\Log;
use Throwable;

/**
 * 示例：一个观察者
 * - 计算种子特征码并上报
 */
class Report implements Observer
{
    /**
     * 流水线业务逻辑
     * @var array|array[]
     */
    protected static array $pipelines = [
        [EchoTitle::class, 'process'],
        [CheckTorrentId::class, 'process'],
        [RequestFind::class, 'process'],
        [Download::class, 'process'],
        [RequestCreate::class, 'process'],
    ];

    /**
     * @param Sites $sites
     * @param Torrents $torrent
     * @return void
     */
    public static function update(Sites $sites, Torrents $torrent): void
    {
        //存在解码器 && 实现契约
        if (!(class_exists(Torrents::$decoder) && is_a(Torrents::$decoder, Reseed::class, true))) {
            if (!$sites->getParams()->daemon) {
                print_r($torrent->toArray());
                echo '不存在解码器 || 未实现契约' . PHP_EOL;
            }
            return;
        }
        //print_r($torrent->toArray());
        try {
            ob_start();
            Utils::echo(sprintf('站点：%s | 页码：%s', $sites->getParams()->site, $sites->currentPage()));
            $pipeline = new Pipeline(Container::getInstance());
            $pipeline->send(new Payload($sites, $torrent))
                ->through(static::$pipelines)
                ->thenReturn();
        } catch (Throwable $throwable) {
            $message = $sites->getParams()->site . '[种子观察者]异常 ----->>> ' . $throwable->getMessage();
            echo $message . PHP_EOL;

            //记录日志
            if ($throwable instanceof BadRequestException ||
                $throwable instanceof ServerErrorHttpException ||
                $throwable instanceof DownloadTorrentException ||
                $throwable instanceof DownloadHtmlException
            ) {
                $torrent->metadata = '';
                Log::error($message, $torrent->toArray());
                echo get_class($throwable) . ' 发生异常，休眠中...' . PHP_EOL;
                sleep(mt_rand(5, 10));
            }
        } finally {
            $content = ob_get_clean();
            if ($content && !$sites->getParams()->daemon) {
                echo $content;
            }
        }
    }
}
