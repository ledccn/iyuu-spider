<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Support\DataStruct;

/**
 * 爬取参数
 * @property string site 站点名称
 * @property string action 动作
 * @property string type 爬虫类型:cookie,rss
 * @property string uri 统一资源标识符
 * @property string begin 开始页码
 * @property string end 结束页码
 * @property bool daemon 守护进程
 */
class Params extends DataStruct
{
    /**
     * 构造函数
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => &$item) {
            if (is_string($item)) {
                $item = trim($item);
            }
        }
        parent::__construct($data);
    }
}
