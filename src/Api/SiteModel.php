<?php

namespace Iyuu\Spider\Api;

use Iyuu\Spider\Exceptions\BadRequestException;
use Iyuu\Spider\Support\DataStruct;
use RuntimeException;

/**
 * 站点配置
 * @property int $id 主键
 * @property string $site 站点标识
 * @property string $nickname 站点昵称
 * @property string $base_url 主机domain
 * @property string $download_page 下载页规则
 * @property string $reseed_check 辅种检查项
 * @property int $is_https 是否支持HTTPS
 */
final class SiteModel extends DataStruct
{
    /**
     * 站点JSON文件名
     */
    const SITES_JSON_FILE = 'sites.json';

    /**
     * @param string $site
     * @return self
     * @throws BadRequestException
     */
    final public static function make(string $site): self
    {
        $sites = self::getServerSites();
        if (empty($sites[$site])) {
            throw new RuntimeException('服务器配置为空');
        }
        return new self($sites[$site]);
    }

    /**
     * 从服务器获取全部站点
     * @return array
     * @throws BadRequestException
     */
    final public static function getServerSites(): array
    {
        $expire = 3600 * 6;     // 站点json文件有效期6小时
        $file = runtime_path(self::SITES_JSON_FILE);
        if (is_file($file) && ((filemtime($file) + $expire) > time())) {
            $json = file_get_contents($file, false, null);
            $sites = json_decode($json, true);
            if (false === $sites) {
                throw new RuntimeException('读取站点配置失败！' . json_last_error_msg());
            }
        } else {
            $client = SpiderClient::getInstance();
            $sites = $client->siteList();
            self::saveToJson($sites);
        }

        return $sites;
    }

    /**
     * @param array $data
     * @return bool
     */
    final protected static function saveToJson(array $data): bool
    {
        file_put_contents(runtime_path('sites.php'), print_r($data, true));
        return file_put_contents(runtime_path(self::SITES_JSON_FILE), json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 构造下载种子的URL链接
     * @param string|null $torrentUrl
     * @param array $urlReplace
     * @param array $urlJoin
     * @return string
     */
    final public function builderTorrentUrl(string $torrentUrl = null, array $urlReplace = [], array $urlJoin = []): string
    {
        $torrentUrl = $torrentUrl ?: $this->download_page;
        //第一步：替换
        $torrentUrl = strtr($torrentUrl, $urlReplace);

        //第二步：拼接
        $delimiter = str_contains($torrentUrl, '?') ? '&' : '?';
        return $torrentUrl . $delimiter . implode('&', $urlJoin);
    }

    /**
     * @return string
     */
    final public function getHost(): string
    {
        $base_url = $this->base_url;
        if (!$this->is_https) {
            return 'http://' . $base_url;
        }
        return 'https://' . $base_url;
    }
}
