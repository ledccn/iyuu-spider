<?php

namespace Iyuu\Spider\Contract;

use Iyuu\Spider\Sites\Payload;

/**
 * 结果输出
 * - 处理种子的管道接口
 */
interface Pipeline
{
    /**
     * 契约方法
     * @param Payload $payload
     * @param callable $next
     * @return mixed
     */
    public static function process(Payload $payload, callable $next): mixed;
}
