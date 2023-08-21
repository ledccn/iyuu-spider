<?php

namespace Iyuu\Spider\Contract;

/**
 * 辅种计算种子特征码
 */
interface Reseed
{
    /**
     * 契约方法
     * @param string $metadata
     * @return array|null
     */
    public static function reseed(string $metadata = ''): ?array;
}
