<?php

namespace Iyuu\Spider\Frameworks\NexusPHP;

use DOMDocument;
use Exception;
use Iyuu\Spider\Contract\ProcessorXml;
use Iyuu\Spider\Exceptions\DownloadHtmlException;
use Iyuu\Spider\Exceptions\EmptyListException;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;
use Iyuu\Spider\Support\Selector;
use Iyuu\Spider\Traits\SitePagination;
use Iyuu\Spider\Utils;
use RuntimeException;
use think\Collection;

/**
 * NexusPHP页面解析器
 */
class Parser extends Sites implements ProcessorXml
{
    use SitePagination;

    /**
     * @return int
     */
    public function getBeginPage(): int
    {
        return 0;
    }

    /**
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
        $list = Selector::select($html, "//*[@class='torrentname']");
        if (empty($list)) {
            throw new EmptyListException('页面解析失败A');
        }
        $rs = [];
        foreach ($list as $v) {
            $arr = [];
            $regex = "/details.php\?id=(\d+)/i";
            if (preg_match($regex, $v, $matches_id)) {
                $arr['id'] = $matches_id[1];
                // 种子地址
                $url = 'download.php?id=' . $arr['id'];
                // 获取主标题
                $regex = '/<a title=[\'|\"](.*?)[\'|\"]/';
                if (preg_match($regex, $v, $matches_h1)) {
                    $arr['h1'] = $matches_h1[1];
                } else {
                    $arr['h1'] = '';
                }

                // 获取副标题(倒序算法)
                // 偏移量
                $h2StrStart = '<br />';
                $h2StrEnd = '</td><td width="20" class="embedded"';
                $h2_endOffset = strpos($v, $h2StrEnd);
                $temp = substr($v, 0, $h2_endOffset);
                $h2_offset = strrpos($temp, $h2StrStart);
                if (false === $h2_offset) {
                    $arr['title'] = '';
                } else {
                    $h2_len = strlen($temp) - $h2_offset - strlen($h2StrStart);
                    //存在副标题
                    $arr['title'] = substr($temp, $h2_offset + strlen($h2StrStart), $h2_len);
                    // 第二次过滤
                    $arr['title'] = trim(strip_tags($arr['title']));
                }

                // 组合返回数组
                $arr['details'] = $host . 'details.php?id=' . $arr['id'];
                $arr['download'] = $host . $url;
                $arr['filename'] = $arr['id'] . '.torrent';

                // 种子促销类型解码
                if (!str_contains($v, 'class="pro_free')) {
                    // 不免费
                    $arr['type'] = 1;
                } else {
                    // 免费种子
                    $arr['type'] = 0;
                }
                // 存活时间
                // 大小
                // 种子数
                // 下载数
                // 完成数
                // 完成进度
                $rs[] = $arr;
            }
        }

        if (empty($rs)) {
            throw new EmptyListException('页面解析失败B');
        }
        return Torrents::toCollection($rs, $this);
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
        return str_replace('{page}', $page, 'torrents.php?incldead=0&page={page}');
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
            /** @var DOMDocument $item */
            foreach ($elements as $item) {
                $this->filterNexusPHP($item);
                $link = $item->getElementsByTagName('enclosure')->item(0) != null ? $item->getElementsByTagName('enclosure')->item(0)->getAttribute('url') : $item->getElementsByTagName('link')->item(0)->nodeValue;
                $guid = $item->getElementsByTagName('guid')->item(0) != null ? $item->getElementsByTagName('guid')->item(0)->nodeValue : md5($link);
                $details = $item->getElementsByTagName('link')->item(0)->nodeValue;
                $time = strtotime($item->getElementsByTagName('pubDate')->item(0)->nodeValue);
                $length = $item->getElementsByTagName('enclosure')->item(0)->getAttribute('length');
                // 提取id
                if (preg_match('/id=(\d+)/i', $details, $match)) {
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
            return Torrents::toCollection($items, $this, false);
        } catch (Exception $e) {
            throw new RuntimeException('XML页面解析失败' . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * @return string
     */
    private function getDefaultXmlPath(): string
    {
        $config = $this->getConfig();
        return str_replace('{passkey}', $config->get('passkey', ''), 'torrentrss.php?rows=50&linktype=dl&passkey={passkey}');
    }

    /**
     * 过滤XML文档中不需要的元素
     */
    protected function filterNexusPHP(&$item)
    {
        $node = $item->getElementsByTagName('description')->item(0);
        if ($node !== null) {
            return $item->removeChild($node);
        }
        return $item;
    }
}