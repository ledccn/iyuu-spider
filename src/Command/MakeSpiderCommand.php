<?php

namespace Iyuu\Spider\Command;

use Iyuu\Spider\Sites\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * make爬虫handler类
 */
class MakeSpiderCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'make:spider';
    /**
     * @var string
     */
    protected static $defaultDescription = 'Make spider handler';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'spider handler name');
        $this->addArgument('type', InputArgument::OPTIONAL, 'Type', '');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $site = $name = trim($input->getArgument('name'));
        $type = $input->getArgument('type');

        $output->writeln("Make spider handler $name");

        $name = $this->nameToNamespace($name);
        $file = Factory::getDirname() . "/$name/" . Factory::DEFAULT_CLASSNAME . ".php";
        if (is_file($file)) {
            throw new \RuntimeException('已存在文件：' . "$name/" . Factory::DEFAULT_CLASSNAME . '.php');
        }
        $namespace = Factory::getNamespace() . "\\$name";
        $this->editProvider($site, $name, $namespace);
        $this->createSpider($site, $name, $namespace, $file, $type);

        return self::SUCCESS;
    }

    /**
     * @param string $site 站点标识
     * @param string $name 转换后的目录名
     * @param string $namespace 服务提供者的命名空间
     * @return void
     */
    protected function editProvider(string $site, string $name, string $namespace): void
    {
        if ($site !== $name) {
            if (Factory::getProvider($site)) {
                throw new \RuntimeException('已存在服务提供者：' . $site);
            }

            $file = Factory::getFilepath();
            $file_content = file_get_contents($file);
            $file_content = preg_replace('/\];\/\/PROVIDER_END/', "    '$site' => \\$namespace\\" . Factory::DEFAULT_CLASSNAME . "::class,\n    ];//PROVIDER_END", $file_content);
            file_put_contents($file, $file_content);
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function nameToNamespace(string $name): string
    {
        // make:spider 不支持子目录、不支持-_
        $namespace = str_replace(['\\', '/', '-', '_'], '', strtolower($name));
        if (is_scalar($namespace) && ctype_alnum($namespace)) {
            if (preg_match('/^[0-9].*$/', $namespace, $matches)) {
                return 'site' . $namespace;
            }
            return $namespace;
        }
        throw  new \InvalidArgumentException('无效的站点名称');
    }

    /**
     * @param string $name
     * @param string $namespace
     * @param string $file
     * @param string $type
     * @return void
     */
    protected function createSpider(string $site, string $name, string $namespace, string $file, string $type): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $content = <<<EOF
<?php

namespace $namespace;

use Iyuu\Spider\Frameworks\NexusPHP\Parser;

/**
 * 爬虫句柄
 * - dirname:$name
 */
class Handler extends Parser
{
    const SITE_NAME = '$site';
}

EOF;
        file_put_contents($file, $content);
    }

}
