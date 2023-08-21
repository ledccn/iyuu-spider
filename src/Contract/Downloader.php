<?php

namespace Iyuu\Spider\Contract;

use Iyuu\Spider\Sites\Torrents;

/**
 * 下载器接口
 */
interface Downloader
{
    /**
     * 浏览器UA
     */
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.163 Safari/537.36';

    /**
     * 下载请求对象，生成响应对象
     * @param Torrents $args
     * @return mixed
     */
    public function download(Torrents $args): mixed;
}
