<?php

namespace Iyuu\Spider\Contract;

use think\Collection;

/**
 * Rss解析器接口
 */
interface ProcessorXml
{
    /**
     * @return Collection
     */
    public function processXml(): Collection;
}