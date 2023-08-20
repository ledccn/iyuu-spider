<?php

namespace Iyuu\Spider;

use InvalidArgumentException;
use Iyuu\Spider\Api\SiteModel;
use Iyuu\Spider\Sites\Config;
use Iyuu\Spider\Sites\Factory;
use Iyuu\Spider\Sites\Params;
use Iyuu\Spider\Sites\Sites;
use Throwable;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use function file_get_contents;
use function is_file;
use function posix_kill;
use function time;
use function usleep;

/**
 *  爬虫应用
 */
class Application
{
    /**
     * worker容器
     * @var Worker|null
     */
    protected static ?Worker $worker = null;

    /**
     * 站点对象
     * @var Sites
     */
    protected Sites $sites;

    /**
     * 构造函数
     * @param Config $config 本地配置
     * @param SiteModel $siteModel 服务器配置
     * @param Params $params 启动参数
     */
    public function __construct(Config $config, SiteModel $siteModel, Params $params)
    {
        if (Utils::isWindowsOs()) {
            throw new InvalidArgumentException('常驻内存仅支持Linux');
        }
        $this->sites = Factory::create($config, $siteModel, $params);
    }

    /**
     * 子进程启动时回调函数
     * @param Worker $worker
     * @return void
     */
    public function onWorkerStart(Worker $worker): void
    {
        static::$worker = $worker;
        $endPage = (int)$this->sites->getParams()->end ?: 0;
        do {
            $page = $this->sites->nextPage();
            try {
                $uri = ($this->sites)->pageBuilder($page);
                $this->sites->process($uri);
            } catch (Throwable $throwable) {
            }
        } while ($page < $endPage);

        if ($this->sites->getParams()->action && $endPage && ($page > $endPage)) {
            self::stopMasterProcess(static::$worker);
        } else {
            self::stopAll();
        }
    }

    /**
     * 子进程退出时回调函数
     * @return void
     */
    public function onWorkerStop(): void
    {
    }

    /**
     * 停止master进程
     * @param Worker $worker
     */
    public function stopMasterProcess(Worker $worker): void
    {
        $start_file = $this->sites->getParams()->site;
        $master_pid = is_file($worker::$pidFile) ? (int)file_get_contents($worker::$pidFile) : 0;
        $master_pid && posix_kill($master_pid, SIGINT);
        // Timeout.
        $timeout = $worker::$stopTimeout + 3;
        $start_time = time();
        // Check master process is still alive?
        while (1) {
            $master_is_alive = $master_pid && posix_kill($master_pid, 0);
            if ($master_is_alive) {
                // Timeout?
                if (time() - $start_time >= $timeout) {
                    $worker::log("Workerman Spider [$start_file] stop fail");
                    exit;
                }
                // Waiting moment.
                usleep(10000);
                continue;
            }
            // Stop success.
            $worker::log("Workerman Spider [$start_file] stop success");
            exit(0);
        }
    }

    /**
     * 初始化worker容器
     * @param array $config 配置
     * @param bool $daemon 常驻守护进程
     * @return void
     */
    final public static function initWorker(array $config, bool $daemon = false): void
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);
        if ($timezone = $config['default_timezone'] ?? 'Asia/Shanghai') {
            date_default_timezone_set($timezone);
        }

        Worker::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        Worker::$pidFile = $config['pid_file'] ?? '';
        Worker::$stdoutFile = $config['stdout_file'] ?? '/dev/null';
        Worker::$logFile = $config['log_file'] ?? '';
        Worker::$eventLoopClass = $config['event_loop'] ?? '';
        TcpConnection::$defaultMaxPackageSize = $config['max_package_size'] ?? 10 * 1024 * 1024;
        if (property_exists(Worker::class, 'statusFile')) {
            Worker::$statusFile = $config['status_file'] ?? '';
        }
        if (property_exists(Worker::class, 'stopTimeout')) {
            Worker::$stopTimeout = $config['stop_timeout'] ?? 7;
        }

        Worker::$daemonize = $daemon;
    }

    /**
     * worker容器配置构造器
     * @param string $site 站点名称
     * @return array
     */
    final public static function buildConfig(string $site): array
    {
        return [
            //PHP配置
            'error_reporting' => E_ALL,
            'default_timezone' => 'Asia/Shanghai',
            //worker主进程配置
            'event_loop' => '',
            'stop_timeout' => 7,
            'pid_file' => rtrim(runtime_path(), PHP_EOL) . "/application_$site.pid",
            'status_file' => rtrim(runtime_path(), PHP_EOL) . "/application_$site.status",
            'stdout_file' => rtrim(runtime_path(), PHP_EOL) . '/logs/stdout.log',
            'log_file' => rtrim(runtime_path(), PHP_EOL) . '/logs/workerman.log',
            'max_package_size' => 10 * 1024 * 1024,
        ];
    }

    /**
     * 运行所有worker容器
     * @return void
     */
    public static function runAll(): void
    {
        Worker::runAll();
    }

    /**
     * 退出进程
     * @param int $code
     * @param string $log
     * @return void
     */
    final public static function stopAll(int $code = 0, string $log = ''): void
    {
        Worker::stopAll($code, $log);
    }
}
