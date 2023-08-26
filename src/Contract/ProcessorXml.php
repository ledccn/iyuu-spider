<?php

namespace Iyuu\Spider\Contract;

use think\Collection;

/**
 * Rss解析器接口
 */
interface ProcessorXml
{
    /**
     * 契约方法
     * @return Collection
     */
    public function processXml(): Collection;
}