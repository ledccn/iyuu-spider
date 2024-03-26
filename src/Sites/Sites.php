<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Api\SiteModel;
use Iyuu\Spider\Contract\Downloader;
use Iyuu\Spider\Contract\PageUriBuilder;
use Iyuu\Spider\Contract\Processor;
use Iyuu\Spider\Exceptions\DownloadHtmlException;
use Iyuu\Spider\Exceptions\DownloadTorrentException;
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
     * @param Config $localConfig 本地配置
     * @param SiteModel $serverConfig 服务器配置
     * @param Params $cliParams 启动参数
     */
    final public function __construct(Config $localConfig, SiteModel $serverConfig, Params $cliParams)
    {
        $this->config = $localConfig;
        $this->siteModel = $serverConfig;
        $this->params = $cliParams;
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
    public function getParams(): Params
    {
        return $this->params;
    }

    /**
     * 请求html页面
     * @param string $url
     * @return string
     * @throws DownloadHtmlException
     */
    public function requestHtml(string $url): string
    {
        $curl = Curl::getInstance()->setUserAgent(Helper::selfUserAgent())->setCommon(20, 30)->setSslVerify();
        $this->setCurlProxy($curl);
        $config = $this->getConfig();
        $curl->setCookies($config->get('cookies'));
        $curl->get($url);
        if (!$curl->isSuccess()) {
            $errmsg = $curl->error_message ?? '网络不通或cookies过期';
            throw new DownloadHtmlException('下载HTML失败：' . $errmsg);
        }
        $html = $curl->response;
        if (is_bool($html) || empty($html)) {
            throw new RuntimeException('curl_exec返回错误');
        }
        return $html;
    }

    /**
     * 设置代理服务器
     * @param Curl $curl
     * @return void
     */
    private function setCurlProxy(Curl $curl): void
    {
        $proxy = $this->getConfig()->get('curl_opt.proxy', '');
        $proxyAuth = $this->getConfig()->get('curl_opt.proxy_auth', '');
        $curl->setCurlProxy($proxy, $proxyAuth);
    }

    /**
     * 请求Xml页面
     * @param string $url
     * @return string
     * @throws DownloadHtmlException
     */
    public function requestXml(string $url): string
    {
        $curl = Curl::getInstance()->setUserAgent(Helper::selfUserAgent())->setCommon(20, 30)->setSslVerify();
        $this->setCurlProxy($curl);
        $curl->get($url);
        if (!$curl->isSuccess()) {
            $errmsg = $curl->error_message ?? '网络不通或流控';
            throw new DownloadHtmlException('下载XML失败：' . $errmsg);
        }
        $xml = $curl->response;
        if (is_bool($xml) || empty($xml)) {
            throw new RuntimeException('curl_exec返回错误');
        }
        return $xml;
    }

    /**
     * 下载种子
     * - cookie下载或rss下载
     * @param null $args
     * @return string|bool|null
     * @throws DownloadTorrentException
     */
    public function download($args = null): string|bool|null
    {
        if ($args instanceof Torrents) {
            $curl = Curl::getInstance()->setUserAgent(Helper::selfUserAgent())->setCommon(30, 120)->setSslVerify()->setFollowLocation(1);
            $this->setCurlProxy($curl);
            if ($args->isCookieRequired()) {
                $curl->setCookies($this->getConfig()->get('cookies'));
            }
            $curl->get($args->download);
            if (!$curl->isSuccess()) {
                var_dump($curl);
                $errmsg = $curl->error_message ?? '网络不通或cookie过期';
                throw new DownloadTorrentException('种子下载失败：' . $errmsg);
            }
            return $curl->response;
        }
        return '';
    }
}
