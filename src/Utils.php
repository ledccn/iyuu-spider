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

    /**
     * 转换成易读的容量格式(包含小数)
     * @param float|int $bytes 字节
     * @param string $delimiter 分隔符 [&nbsp; | <br />]
     * @param int $decimals 保留小数点
     * @return string
     */
    public static function dataSize(float|int $bytes, string $delimiter = '', int $decimals = 2): string
    {
        $type = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $i = 0;
        while ($bytes >= 1024) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, $decimals) . $delimiter . $type[$i];
    }
}
