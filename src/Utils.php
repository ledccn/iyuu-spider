<?php

namespace Iyuu\Spider;

/**
 * 工具包
 */
class Utils
{
    /**
     * 判断windows操作系统
     * @return bool
     */
    public static function isWindowsOs(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
}
