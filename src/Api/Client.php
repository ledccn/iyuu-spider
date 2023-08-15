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
     * 客户端版本号
     */
    const VERSION = '2.0.0';
    /**
     * 接口主域名
     */
    const HOST = 'http://api.iyuu.cn';
    /**
     * token绑定
     */
    const API_BIND = self::HOST . '/App.Api.Bind';
    /**
     * 站点列表
     */
    const API_SITES = self::HOST . '/App.Api.Sites';
    /**
     * 查询影视条目
     */
    const API_FIND_MOVIE = self::HOST . '/App.Api.FindMovieSubject';
    /**
     * 绑定影视条目
     */
    const API_BIND_MOVIE = self::HOST . '/App.Api.BindMovieSubject';
    /**
     * @var string
     */
    protected string $iyuuToken = '';
    /**
     * @var Curl
     */
    protected Curl $curl;

    /**
     * 构造函数
     */
    public function __construct(string $iyuuToken)
    {
        $this->iyuuToken = $iyuuToken;
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
        $res = $this->curl->get(self::API_SITES, $param);
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
        return $this->iyuuToken;
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
