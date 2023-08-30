<?php

namespace Iyuu\Spider\Command;

use Iyuu\Spider\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 爬虫列表
 */
class SpiderList extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'spider:list';
    /**
     * @var string
     */
    protected static $defaultDescription = 'IYUU出品，打印支持的站点列表';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::OPTIONAL, '名称', '');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        match ($name) {
            'route' => Helper::routeTable($output),
            default => Helper::siteTable($output),
        };
        $output->writeln('Hello spider:list');
        return self::SUCCESS;
    }
}
