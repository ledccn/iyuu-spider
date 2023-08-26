<?php

namespace Iyuu\Spider\Frameworks\UNIT3D;

use Iyuu\Spider\Exceptions\EmptyListException;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Sites\Torrents;
use Iyuu\Spider\Support\Selector;
use Iyuu\Spider\Traits\SitePagination;
use think\Collection;

/**
 * UNIT3D
 * @link  https://github.com/HDInnovations/UNIT3D-Community-Edition
 */
class Parser extends Sites
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
     * 契约方法
     * - 凭cookies解析页面
     * @param string $path
     * @return Collection
     * @throws EmptyListException
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
            $regex = "#{$siteModel->getHost()}/torrents/download/(\d+)\.([A-Za-z0-9]+)#i";
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
