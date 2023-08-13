<?php

namespace Iyuu\Spider\Frameworks\NexusPHP;

use DOMDocument;
use Exception;
use Iyuu\Spider\Contract\ProcessorXml;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;
use Iyuu\Spider\Support\Selector;
use Ledc\Curl\Curl;
use RuntimeException;
use think\Collection;

/**
 * NexusPHP页面解析器
 */
class Parser extends Sites implements ProcessorXml
{
    /**
     * 种子列表页，第一页默认页码
     */
    protected int $defaultPage = 0;

    /**
     * @param string $path
     * @return Collection
     */
    public function process(string $path = ''): Collection
    {
        $siteModel = $this->getSiteModel();
        $host = $siteModel->getHost() . '/';
        $url = $host . ($path ?: $this->getDefaultPath());
        $html = $this->requestHtml($url);
        $list = Selector::select($html, "//*[@class='torrentname']");
        if (empty($list)) {
            throw new RuntimeException('页面解析失败');
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
                if (strpos($v, 'class="pro_free') === false) {
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
            //页面解析失败
            return new Collection([]);
        }
        return Torrents::toCollection($rs, $this);
    }

    /**
     * 获取
     * @return string
     */
    protected function getDefaultPath(): string
    {
        return str_replace('{}', $this->getStartPage(), 'torrents.php?incldead=0&page={}');
    }

    /**
     * 获取开始页码
     * @return int
     */
    protected function getStartPage(): int
    {
        $page = $this->params->start;
        return is_numeric($page) ? (int)$page : $this->defaultPage;
    }

    /**
     * 请求html页面
     * @param string $url
     * @return string
     */
    public function requestHtml(string $url = ''): string
    {
        $curl = Curl::getInstance()->setCommon( 5);
        $config = $this->getConfig();
        $curl->setCookies($config->get('cookie'));
        $curl->get($url);
        if (!$curl->isSuccess()) {
            throw new RuntimeException('网络不通或cookie过期');
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
     * @return mixed
     */
    public function download(): mixed
    {
        // TODO: Implement download() method.
        return '';
    }

    /**
     * 解析xml页面
     * @param string $path
     * @return Collection
     */
    public function processXml(string $path = ''): Collection
    {
        $siteModel = $this->getSiteModel();
        $host = $siteModel->getHost() . '/';
        $url = $host . ($path ?: $this->getDefaultXmlPath());
        $curl = Curl::getInstance()->setCommon(false, 5, 10);
        $curl->get($url);
        if (!$curl->isSuccess()) {
            throw new RuntimeException('网络不通或cookie过期');
        }
        $xml = $curl->response;
        if (is_bool($xml) || empty($xml)) {
            throw new RuntimeException('curl_exec返回错误');
        }
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
                $torrent['size'] = data_size($length);
                $torrent['length'] = $length;
                $torrent['guid'] = $guid;
                $items[] = $torrent;
            }
            if (empty($items)) {
                //页面解析失败
                return new Collection([]);
            }
            return Torrents::toCollection($items, $this);
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