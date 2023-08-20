<?php

namespace Iyuu\Spider\Sites;

use Iyuu\Spider\Api\SiteModel;
use Iyuu\Spider\Contract\Downloader;
use Iyuu\Spider\Contract\PageUriBuilder;
use Iyuu\Spider\Contract\Processor;
use Iyuu\Spider\Observers\Report;

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
}
