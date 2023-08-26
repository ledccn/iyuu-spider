<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Api\SiteModel;
use Iyuu\Spider\Contract\Downloader;
use Iyuu\Spider\Contract\PageUriBuilder;
use Iyuu\Spider\Contract\Processor;
use Iyuu\Spider\Helper;
use Ledc\Curl\Curl;
use RuntimeException;

/**
 * 站点基础类
 */
abstract class Sites implements Processor, Downloader, PageUriBuilder
{
    /**
     * 本地配置
     * @var Config
     */
    private Config $config;
    /**
     * 服务器配置
     * @var SiteModel
     */
    private SiteModel $siteModel;
    /**
     * 启动参数
     * @var Params
     */
    private Params $params;

    /**
     * 构造函数
     * @param Config $config 本地配置
     * @param SiteModel $siteModel 服务器配置
     * @param Params $params 启动参数
     */
    final public function __construct(Config $config, SiteModel $siteModel, Params $params)
    {
        $this->config = $config;
        $this->siteModel = $siteModel;
        $this->params = $params;
        $this->init();
    }

    /**
     * 子类的初始化方法
     * @return void
     */
    protected function init(): void
    {
    }

    /**
     * 获取本地配置
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 获取服务器配置
     * @return SiteModel
     */
    public function getSiteModel(): SiteModel
    {
        return $this->siteModel;
    }

    /**
     * 获取启动参数
     * @return Params
     */
    final public function getParams(): Params
    {
        return $this->params;
    }

    /**
     * 请求html页面
     * @param string $url
     * @return string
     */
    public function requestHtml(string $url = ''): string
    {
        $curl = Curl::getInstance()->setUserAgent(Helper::selfUserAgent())->setCommon(20, 30)->setSslVerify();
        $config = $this->getConfig();
        $curl->setCookies($config->get('cookies'));
        $curl->get($url);
        if (!$curl->isSuccess()) {
            $errmsg = $curl->error_message ?? '网络不通或cookies过期';
            throw new RuntimeException($errmsg);
        }
        $html = $curl->response;
        if (is_bool($html) || empty($html)) {
            throw new RuntimeException('curl_exec返回错误');
        }
        return $html;
    }

    /**
     * 下载种子
     * - cookie下载或rss下载
     * @param null $args
     * @return string|bool|null
     */
    public function download($args = null): string|bool|null
    {
        if ($args instanceof Torrents) {
            $curl = Curl::getInstance()->setUserAgent(Helper::selfUserAgent())->setCommon(30, 120)->setSslVerify();
            if ($args->isCookieRequired()) {
                $curl->setCookies($this->getConfig()->get('cookies'));
            }
            $curl->get($args->download);
            if (!$curl->isSuccess()) {
                $errmsg = $curl->error_message ?? '网络不通或cookie过期';
                throw new RuntimeException($errmsg);
            }
            return $curl->response;
        }
        return '';
    }
}
