<?php

namespace Iyuu\Spider\Contract;

/**
 * 下载器接口
 */
interface Downloader
{
    /**
     * 下载请求对象，生成响应对象
     * @return mixed
     */
    public function download(): mixed;
}
