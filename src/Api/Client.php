<?php

namespace Iyuu\Spider\Api;

use Ledc\Curl\Curl;
use RuntimeException;

/**
 * IYUU客户端
 */
class Client
{
    /**
     * 客户端版本
     */
    const VERSION = '2.0.0';
    /**
     * @var Curl
     */
    protected Curl $curl;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->curl = new Curl();
        $this->curl->setCommon(8, 8);
    }

    /**
     * 请求服务器，获取支持的站点列表
     * @return array
     */
    public function getSites(): array
    {
        $param = [
            'sign' => $this->getToken(),
            'version' => self::VERSION,
        ];
        $host = $this->getDefaultHost();
        $path = config('iyuu.default.endpoints.sites', '');
        $url = $host . $path;
        $res = $this->curl->get($url, $param);
        if ($res->isSuccess()) {
            $response = json_decode($res->response, true);
            if ($this->isSuccess($response) && !empty($response['data']['sites'])) {
                return array_column($response['data']['sites'], null, 'site');
            }
        }
        throw new RuntimeException('获取站点列表失败');
    }

    /**
     * 读取token
     * @return string
     */
    public function getToken(): string
    {
        return getenv('IYUU_TOKEN') ?: '';
    }

    /**
     * 读取默认的host
     * @return string
     */
    public function getDefaultHost(): string
    {
        return config('iyuu.default.host', '');
    }

    /**
     * @param mixed $response
     * @return bool
     */
    public function isSuccess(mixed $response): bool
    {
        return is_array($response) && isset($response['ret']) && 200 === $response['ret'];
    }
}
