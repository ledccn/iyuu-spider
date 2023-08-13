<?php

namespace Iyuu\Spider\Contract;

use think\Collection;

/**
 * 页面解析器接口
 * - 解析页面数据，生成数据集对象
 * - 解析页面中待抓取链接，存入调度器
 */
interface Processor
{
    /**
     * @return Collection
     */
    public function process(): Collection;
}
