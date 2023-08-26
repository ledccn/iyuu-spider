<?php

namespace Iyuu\Spider\Frameworks\UNIT3D;

use DOMDocument;
use Iyuu\Spider\Contract\ProcessorXml;
use Iyuu\Spider\Exceptions\DownloadHtmlException;
use Iyuu\Spider\Exceptions\EmptyListException;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;
use Iyuu\Spider\Support\Selector;
use Iyuu\Spider\Traits\SitePagination;
use RuntimeException;
use think\Collection;
use Throwable;

/**
 * UNIT3D
 * @link  https://github.com/HDInnovations/UNIT3D-Community-Edition
 */
class Parser extends Sites implements ProcessorXml
{
    use SitePagination;

    /**
     * @return int
     */
    public function getBeginPage(): int
    {
        return 1;
    }

    /**
     * 获取正则表达式
     * @return string
     */
    public function getRegex():string
    {
        $siteModel = $this->getSiteModel();
        return "#{$siteModel->getHost()}/torrents/download/(\d+)\.([A-Za-z0-9]+)#i";
    }

    /**
     * 契约方法
     * - 凭cookies解析页面
     * @param string $path
     * @return Collection
     * @throws EmptyListException|DownloadHtmlException
     */
    public function process(string $path = ''): Collection
    {
        $siteModel = $this->getSiteModel();
        $host = $siteModel->getHost() . '/';
        $url = $host . ($path ?: $this->getDefaultPath());
        $html = $this->requestHtml($url);
        $table = Selector::select($html, '//*[@id="torrent-list-table"]');
        if (empty($table)) {
            throw new EmptyListException('页面解析失败A');
        }
        $list = Selector::select($html, '//*[@id="torrent-list-table"]//tbody//tr');
        $rs = [];
        foreach ($list as $v) {
            $tr = [];
            $regex = $this->getRegex();
            if (preg_match($regex, $v, $matches)) {
                $tr['id'] = $matches[1];
                $h1 = Selector::select($v, '//a[contains(@class,"torrent-listings-name")]');
                $tr['h1'] = $this->filterH1Title($h1);
                $title = Selector::select($v, '//span[contains(@class,"torrent-listings-subhead")]/b');
                $tr['title'] = $this->filterH1Title($title);
                $details = Selector::select($v, '//*[contains(@class,"torrent-listings-name")]/@href');
                $tr['details'] = $details ?: $host . 'torrents/' . $tr['id'];
                $tr['download'] = $matches[0];
                $tr['rsskey'] = $matches[2];
                $tr['filename'] = $tr['id'] . '.torrent';
                //下载是否消耗流量：0免费/1不免费
                $tr['type'] = 0;

                $rs[] = $tr;
            }
        }
        if (empty($rs)) {
            throw new EmptyListException('页面解析失败B');
        }

        return Torrents::toCollection($rs, $this, false);
    }

    /**
     * RSS订阅XML的契约方法
     * @param string $path
     * @return Collection
     * @throws DownloadHtmlException
     */
    public function processXml(string $path = ''): Collection
    {
        $siteModel = $this->getSiteModel();
        $host = $siteModel->getHost() . '/';
        $url = $host . ($path ?: $this->getDefaultXmlPath());
        //var_dump($url);
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
            foreach ($elements as $item) {
                $node = $item->getElementsByTagName('description')->item(0);
                if ($node !== null) {
                    $item->removeChild($node);
                }
                $link = $item->getElementsByTagName('link')->item(0)->nodeValue;
                $guid = $item->getElementsByTagName('guid')->item(0) != null ? $item->getElementsByTagName('guid')->item(0)->nodeValue : md5($link);
                $details = $link;
                $time = strtotime($item->getElementsByTagName('pubDate')->item(0)->nodeValue);
                // 提取id
                $regex = '#/torrent/download/(\d+)\.([A-Za-z0-9]+)#i';
                if (preg_match($regex, $details, $matches)) {
                    $id = $matches[1];
                } else {
                    continue;
                }
                $torrent['id'] = $id;
                $torrent['h1'] = $item->getElementsByTagName('title')->item(0)->nodeValue;
                $torrent['title'] = '';
                $torrent['details'] = $details;
                $torrent['download'] = $link;
                $torrent['rsskey'] = $matches[2];
                $torrent['filename'] = $id . '.torrent';
                $torrent['type'] = 0;   // 免费0
                $torrent['time'] = date("Y-m-d H:i:s", $time);
                //$torrent['size'] = Utils::dataSize($length);
                //$torrent['length'] = $length;
                $torrent['guid'] = $guid;
                $items[] = $torrent;
            }
            if (empty($items)) {
                //页面解析失败
                return new Collection([]);
            }
            return Torrents::toCollection($items, $this, false);
        } catch (Throwable $throwable) {
            throw new RuntimeException('XML页面解析失败' . $throwable->getMessage() . PHP_EOL);
        }
    }

    /**
     * @return string
     */
    private function getDefaultXmlPath(): string
    {
        $config = $this->getConfig();
        return str_replace('{rsskey}', $config->get('rsskey', ''), 'rss/13.{rsskey}');
    }

    /**
     * 过滤主标题
     * @param string|null $h1
     * @return string
     */
    private function filterH1Title(string $h1 = null): string
    {
        static $disallow = ["\0", "\r", "\n"];
        return $h1 ? trim(strip_tags(str_replace($disallow, '', $h1))) : '';
    }

    /**
     * 获取默认页面地址
     * @return string
     */
    protected function getDefaultPath(): string
    {
        return static::pageBuilder($this->getStartPage());
    }

    /**
     * @param int $page
     * @return string
     */
    public static function pageBuilder(int $page): string
    {
        return str_replace('{page}', $page, 'torrents?page={page}');
    }
}
