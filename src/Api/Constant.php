<?php

namespace Iyuu\Spider\Api;

/**
 * 常量
 */
class Constant
{
    /**
     * 爬虫服务主域名
     */
    const SPIDER_HOST = 'http://api.iyuu.cn:2120';
    /**
     * 创建
     */
    const API_SPIDER_CREATE = self::SPIDER_HOST . '/spider/torrent/create';
    /**
     * 查重
     */
    const API_SPIDER_FIND = self::SPIDER_HOST . '/spider/torrent/find';

    /**
     * 谷歌验证码 密钥
     */
    const GOOGLE_SECRET = 'H6WSXWT22FE4DDDR';
}
