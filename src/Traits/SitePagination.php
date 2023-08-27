<?php

namespace Iyuu\Spider\Traits;

use Iyuu\Spider\Application;
use Iyuu\Spider\Helper;
use Iyuu\Spider\Sites\Params;
use Iyuu\Spider\Utils;
use support\Log;

/**
 * 站点分页组件
 */
trait SitePagination
{
    /**
     * 获取启动参数
     * @return Params
     */
    abstract public function getParams(): Params;

    /**
     * 种子列表页，第一页默认页码
     * @return int
     */
    abstract public function getBeginPage(): int;

    /**
     * 下一页
     * @param int $step 步进
     * @param bool $retCurrent 返回当前页
     * @return int
     */
    public function nextPage(int $step = 1, bool $retCurrent = true): int
    {
        $current_page = $this->currentPage();
        $next_page = $current_page + $step;
        $sitePageFile = $this->sitePageFilename();
        Log::debug($this->getParams()->site . '进程' . Application::getWorker()->id . ' 页码：' . $next_page);
        file_put_contents($sitePageFile, $next_page);
        return $retCurrent ? $current_page : $next_page;
    }

    /**
     * 当前页
     * @return int
     */
    public function currentPage(): int
    {
        if (empty($this->getParams()->action)) {
            return $this->getStartPage();
        }
        clearstatcache();
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
     * 获取开始页码
     * @return int
     */
    protected function getStartPage(): int
    {
        $page = $this->getParams()->begin;
        return ctype_digit($page) ? (int)$page : $this->getBeginPage();
    }

    /**
     * @return string
     */
    private function sitePageFilename(): string
    {
        $site = $this->getParams()->site;
        return Helper::sitePageFilename($site);
    }
}
