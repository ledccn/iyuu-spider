<?php

namespace Iyuu\Spider\Sites\mteam;

use DOMDocument;
use Exception;
use Iyuu\Spider\Exceptions\DownloadHtmlException;
use Iyuu\Spider\Frameworks\NexusPHP\Parser;
use Iyuu\Spider\Sites\Torrents;
use Iyuu\Spider\Utils;
use RuntimeException;
use think\Collection;

/**
 * 爬虫句柄
 */
class Handler extends Parser
{
    const SITE_NAME = 'm-team';

    /**
     * @return string
     */
    protected function getDefaultXmlPath(): string
    {
        $config = $this->getConfig();
        return $config->get('rss_uri');
    }

    /**
     * 解析xml页面
     * @param string $path
     * @return Collection
     * @throws DownloadHtmlException
     */
    public function processXml(string $path = ''): Collection
    {
        $siteModel = $this->getSiteModel();
        $host = rtrim($siteModel->getHost(), '/') . '/';
        $url = $host . ltrim(($path ?: $this->getDefaultXmlPath()), '/');
        $xml = $this->requestXml($url);
        try {
            $items = [];
            $dom = new DOMDocument();
            // 禁用标准的 libxml 错误
            libxml_use_internal_errors(true);
            $dom->loadXML($xml);
            // 清空 libxml 错误缓冲
            libxml_clear_errors();
            $elements = $dom->getElementsByTagName('item');
            /** @var DOMDocument $item */
            foreach ($elements as $item) {
                $this->filterNexusPHP($item);
                $details = $item->getElementsByTagName('link')->item(0)->nodeValue;
                $link = $item->getElementsByTagName('enclosure')->item(0) != null ? $item->getElementsByTagName('enclosure')->item(0)->getAttribute('url') : $details;
                $guid = $item->getElementsByTagName('guid')->item(0) != null ? $item->getElementsByTagName('guid')->item(0)->nodeValue : md5($link);
                $time = strtotime($item->getElementsByTagName('pubDate')->item(0)->nodeValue);
                $length = $item->getElementsByTagName('enclosure')->item(0)->getAttribute('length');
                // 提取id
                if (preg_match('/detail\/(\d+)/i', $details, $match)) {
                    $id = $match[1];
                } else {
                    continue;
                }
                $torrent['id'] = $id;
                $torrent['h1'] = $item->getElementsByTagName('title')->item(0)->nodeValue;
                $torrent['title'] = '';
                $torrent['details'] = $details;
                $torrent['download'] = $link;
                $torrent['filename'] = $id . '.torrent';
                $torrent['type'] = 0;   // 免费0
                $torrent['time'] = date("Y-m-d H:i:s", $time);
                $torrent['size'] = Utils::dataSize($length);
                $torrent['length'] = $length;
                $torrent['guid'] = $guid;
                $items[] = $torrent;
            }
            if (empty($items)) {
                //页面解析失败
                return new Collection([]);
            }
            //var_dump($items);exit;
            return Torrents::toCollection($items, $this, false);
        } catch (Exception $e) {
            throw new RuntimeException('XML页面解析失败' . $e->getMessage() . PHP_EOL);
        }
    }
}
