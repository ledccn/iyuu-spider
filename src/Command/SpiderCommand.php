<?php

namespace Iyuu\Spider\Command;

use InvalidArgumentException;
use Iyuu\Spider\Api\SiteModel;
use Iyuu\Spider\Application;
use Iyuu\Spider\Contract\ProcessorXml;
use Iyuu\Spider\Sites\Config;
use Iyuu\Spider\Sites\Factory;
use Iyuu\Spider\Sites\Params;
use Iyuu\Spider\Utils;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 爬虫命令行
 */
class SpiderCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'spider';
    /**
     * @var string
     */
    protected static $defaultDescription = 'IYUU出品的PT站点页面解析器';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('site', InputArgument::REQUIRED, '站点名称')
            ->addArgument('action', InputArgument::OPTIONAL, 'start|stop|restart|reload|status|connections', '')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, '爬虫类型:cookie,rss', 'cookie')
            ->addOption('uri', null, InputOption::VALUE_OPTIONAL, '统一资源标识符', '')
            ->addOption('begin', null, InputOption::VALUE_OPTIONAL, '开始页码', '')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, '结束页码', '')
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, '守护进程');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 接收参数
        $site = $input->getArgument('site');
        // 接收选项
        $type = $input->getOption('type');
        if (!in_array($type, ['cookie', 'rss'])) {
            throw new RuntimeException('未定义的爬虫类型：' . $type);
        }

        //爬取参数
        $params = array_merge($input->getArguments(), $input->getOptions());
        //本地配置
        $config = config('sites.' . $site);
        if (empty($config)) {
            throw new RuntimeException('本地配置为空');
        }

        $_config = new Config($config);
        $_params = new Params($params);
        //服务器配置
        $siteModel = SiteModel::make($site);
        if ($input->getArgument('action')) {
            if (Utils::isWindowsOs()) {
                throw new InvalidArgumentException('常驻内存仅支持Linux');
            }
            return $this->startApplication($_config, $siteModel, $_params);
        }

        //构造爬虫实例
        $sites = Factory::create($_config, $siteModel, $_params);
        $output->writeln("爬取站点 开始 ----->>> $site");
        switch ($type) {
            case 'rss':
                if ($sites instanceof ProcessorXml) {
                    $sites->processXml();
                } else {
                    throw new RuntimeException(get_class($sites) . '未实现接口：' . ProcessorXml::class);
                }
                break;
            case 'cookie':
            default:
                $sites->process();
                break;
        }
        // 指令输出
        $output->writeln("爬取站点 结束 ----->>> $site");
        return self::SUCCESS;
    }

    /**
     * 守护进程
     * @param Config $config
     * @param SiteModel $siteModel
     * @param Params $params
     * @return int
     */
    protected function startApplication(Config $config, SiteModel $siteModel, Params $params): int
    {
        $conf = Application::buildConfig($params->site);
        Application::initWorker($conf, $params->daemon);
        $process_config = [
            'count' => $config->get('count', 5),
            'reloadable' => false,
            'handler' => Application::class,
            'constructor' => [
                'config' => $config,
                'siteModel' => $siteModel,
                'params' => $params,
            ],
        ];
        worker_start($siteModel->site, $process_config);
        Application::runAll();
        return self::SUCCESS;
    }
}
