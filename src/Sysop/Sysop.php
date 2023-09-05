<?php

namespace Iyuu\Spider\Sysop;

use Exception;
use Iyuu\Spider\Api\SiteModel;
use Iyuu\Spider\Sites\Config;
use Iyuu\Spider\Sites\Factory;
use Iyuu\Spider\Sites\Params;
use Iyuu\Spider\Sites\Torrents;

/**
 * 站点研发直接对接
 */
class Sysop
{
    /**
     * @var AnySite
     */
    public AnySite $provider;

    /**
     * @param string $site 站点在IYUU的标识
     * @param Config $config 本地配置
     * @throws Exception
     */
    public function __construct(string $site, Config $config)
    {
        $provider = $config->get('provider', '');
        Factory::checkProvider($provider);
        //服务器配置
        $siteModel = SiteModel::make($site);

        $this->provider = new $provider($config, $siteModel, new Params([]));
    }

    /**
     * @param array $items
     * @return void
     */
    public function run(array $items): void
    {
        Torrents::toCollection($items, $this->provider);
    }
}
