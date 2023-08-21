<?php

namespace Iyuu\Spider\Api;

use Iyuu\Spider\Sites\Torrents;
use Ledc\Curl\Curl;
use RuntimeException;

/**
 * 分布式爬虫客户端
 */
class SpiderClient
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
     * 爱语飞飞token
     * @var string
     */
    protected string $token;
    /**
     * 上报密钥
     * @var string
     */
    protected string $secret;
    /**
     * @var Curl
     */
    protected Curl $curl;

    /**
     * 构造函数
     * @param string $token 爱语飞飞token
     * @param string $secret 上报密钥
     */
    public function __construct(string $token, string $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
        $this->curl = new Curl();
        $this->curl->setCommon(8, 8);
    }

    /**
     * 查重
     * @param string $site 站点名称
     * @param int $torrent_id 种子ID
     * @return void
     */
    public function findTorrent(string $site, int $torrent_id): void
    {
        $data = [
            'site' => $site,
            'torrent_id' => $torrent_id,
        ];
        $res = $this->curl->get(static::API_SPIDER_FIND, $data);
        if ($res->isSuccess()) {
            $response = json_encode($res->response, true);
            $code = $response['code'] ?? -1;
            $msg = $response['msg'] ?? '缺失错误信息';
            switch (true) {
                case (200 === $code):   // 服务器不存在该种子
                    // 1.种子符合下载条件
                    return;
                case (202 === $code):   // 服务器存在该种子
                    throw new RuntimeException('-----种子：在远端服务器已存在！！！');
                case (405 === $code):   // 服务器不存在该种子，但是任务被领取，锁定300秒后才能再次被领取。
                    throw new RuntimeException('-----种子：' . $msg);
                default:
                    echo "-----远端服务器无响应，请稍后再试。" . PHP_EOL;
                    sleep(5);
                    throw new RuntimeException('-----错误消息：' . $msg . PHP_EOL);
            }
        }

        $err_msg = $this->formatErrorMessage($res);
        throw new RuntimeException('查重失败：' . $err_msg);
    }

    /**
     * 创建
     * @param string $site
     * @param Torrents $torrent
     * @param array $data
     * @return void
     */
    public function createTorrent(string $site, Torrents $torrent, array $data): void
    {
        $now = time();
        //Step1：组装上报数据
        $data['site'] = $site;
        $data['torrent_id'] = $torrent->id;
        // 特殊字段：种子分组ID【海豚、海报、皮等特有字段】
        if ($group_id = $torrent->group_id) {
            $data['group_id'] = $group_id;
        }
        $data['h1'] = $torrent->h1 ?? '';
        $data['title'] = $torrent->title ?? '';
        $data['timestamp'] = $now;

        //Step2：非超级管理员的时候，添加appid参数，验证用户站点上传权限
        if (!$this->isAdmin()) {
            $data['appid'] = substr($this->token, 0, strpos($this->token, 'T'));
        }

        //Step3：简单签名 sha1(timestamp + secret)
        //普通用户的secret与爱语飞飞token相同
        $signature = sha1($now . $this->secret);
        $data['sign'] = $signature;

        $res = $this->curl->post(static::API_SPIDER_CREATE, $data);
        if ($res->isSuccess()) {
            $response = json_encode($res->response, true);
            $code = $response['code'] ?? -1;
            $msg = $response['msg'] ?? '缺失错误信息';
            if (200 === $code) {
                // 种子特征码上报成功
                echo '种子特征码上报成功。' . $msg . PHP_EOL . PHP_EOL;
            } else {
                echo "-----远端服务器无响应，请稍后再试。" . PHP_EOL;
                echo '-----错误消息：' . $msg . PHP_EOL . PHP_EOL;
                sleep(mt_rand(5, 10));
                throw new RuntimeException('-----错误消息：' . $msg . PHP_EOL);
            }
        }

        $err_msg = $this->formatErrorMessage($res);
        throw new RuntimeException('特征码上报失败：' . $err_msg);
    }

    /**
     * 是否超级管理员
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->secret && false === str_starts_with($this->secret, 'IYUU');
    }

    /**
     * @param \Curl\Curl $curl
     * @return string
     */
    public function formatErrorMessage(\Curl\Curl $curl): string
    {
        return $curl->error_message ?? '服务器无响应';
    }
}
