<?php

namespace Iyuu\Spider\Frameworks\UNIT3D;

use Iyuu\Spider\Exceptions\EmptyListException;
use Iyuu\Spider\Sites\Sites;
use Iyuu\Spider\Support\Selector;
use Iyuu\Spider\Traits\SitePagination;
use think\Collection;

/**
 * UNIT3D
 * - https://github.com/HDInnovations/UNIT3D-Community-Edition
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
        var_dump($list);
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
