<?php

namespace Iyuu\Spider\Traits;

use Iyuu\Spider\Sites\Params;
use Iyuu\Spider\Utils;

/**
 * 站点分页组件
 */
trait SitePagination
{
    /**
     * 种子列表页，第一页默认页码
     */
    protected int $beginPage = 0;

    /**
     * @return int
     */
    public function currentPage(): int
    {
        $sitePageFile = $this->sitePageFilename();
        if (is_file($sitePageFile)) {
            $current_page = file_get_contents($sitePageFile, false, null);
            return (int)$current_page;
        }

        $path = dirname($sitePageFile);
        if (!is_dir($path)) {
            Utils::createDir($path);
        }
        $page = $this->getStartPage();
        file_put_contents($sitePageFile, $page);
        return $page;
    }

    /**
     * @param int $step 步进
     * @return int
     */
    public function nextPage(int $step = 1): int
    {
        $current_page = $this->currentPage();
        $next_page = $current_page + $step;
        $sitePageFile = $this->sitePageFilename();
        file_put_contents($sitePageFile, $next_page);
        return $next_page;
    }

    /**
     * @return string
     */
    private function sitePageFilename(): string
    {
        $site = $this->getParams()->site;
        return runtime_path("/page/$site.page");
    }

    /**
     * 获取开始页码
     * @return int
     */
    protected function getStartPage(): int
    {
        $page = $this->getParams()->begin;
        return is_numeric($page) ? (int)$page : $this->beginPage;
    }

    /**
     * 获取启动参数
     * @return Params
     */
    abstract public function getParams(): Params;
}
