<?php

namespace Iyuu\Spider\Frameworks;

/**
 * 框架枚举
 */
class Frameworks
{
    /**
     * 类型映射表
     */
    const TYPE = [
        '0' => \Iyuu\Spider\Frameworks\NexusPHP\Parser::class,
        '1' => \Iyuu\Spider\Frameworks\UNIT3D\Parser::class,
    ];
}
