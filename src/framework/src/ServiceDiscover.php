<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Closure;
use Larmias\Command\Annotation\Command;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\Di\ClassScannerInterface;
use Larmias\Contracts\ServiceDiscoverInterface;
use Larmias\Di\AnnotationCollector;
use Larmias\Event\Annotation\Listener;
use Larmias\Framework\Annotation\Provider;
use Larmias\Framework\Annotation\Server;
use Larmias\Process\Annotation\Process;
use Larmias\Support\FileSystem;
use Larmias\Support\ScanHandler\ScanHandlerFactory;
use Throwable;
use function array_column;
use function array_merge;
use function array_values;
use function call_user_func;
use function class_exists;
use function date;
use function json_decode;
use function var_export;
use const PHP_EOL;

class ServiceDiscover implements ServiceDiscoverInterface
{
    /**
     * @var string[]
     */
    protected array $annotationCollect = [
        Provider::class => [
            'name' => self::SERVICE_PROVIDER,
            'method' => 'collectClass',
        ],
        Command::class => [
            'name' => self::SERVICE_COMMAND,
            'method' => 'collectClass',
        ],
        Process::class => [
            'name' => self::SERVICE_PROCESS,
            'method' => 'collectProcess',
        ],
        Server::class => [
            'name' => self::SERVICE_SERVER,
            'method' => 'collectServer',
        ],
        Listener::class => [
            'name' => self::SERVICE_LISTENER,
            'method' => 'collectClass',
        ],
    ];

    /**
     * @var FileSystem
     */
    protected FileSystem $fileSystem;

    /**
     * @var array
     */
    protected array $services = [];

    /**
     * @param ApplicationInterface $app
     * @throws Throwable
     */
    public function __construct(protected ApplicationInterface $app)
    {
        $this->fileSystem = $this->app->getContainer()->get(FileSystem::class);
    }

    /**
     * 发现服务配置
     * @param Closure $callback
     * @return void
     * @throws Throwable
     */
    public function discover(Closure $callback): void
    {
        $scanHandlerFactory = new ScanHandlerFactory();
        $scanHandler = $scanHandlerFactory->make();
        $scanned = $scanHandler->scan();
        if (!$scanned->isScanned()) {
            $this->runHandle();
            return;
        }
        $callback();
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function runHandle(): void
    {
        $this->app->setIsInitialize(true);
        run(function () {
            $this->handle();
        }, [
            'settings' => ['logger' => false]
        ]);
        exit(0);
    }

    /**
     * 注册服务
     * @param string $name
     * @param string $class
     * @param array $args
     * @return void
     */
    public function register(string $name, string $class, array $args = []): void
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = [];
        }
        $this->services[$name][] = ['class' => $class, 'args' => $args];
    }


    /**
     * 获取注册的服务
     * @return array
     */
    public function services(): array
    {
        $file = $this->app->getRuntimePath() . 'services.php';
        $services = $this->services;
        if ($this->fileSystem->isFile($file)) {
            $services = array_merge(require $file, $services);
        }
        return $this->optimize($services);
    }

    /**
     * 添加服务提供者
     * @param string|array $providers
     * @return void
     */
    public function providers(string|array $providers): void
    {
        $this->batchRegister(self::SERVICE_PROVIDER, $providers);
    }

    /**
     * 添加命令服务
     * @param string|array $commands
     * @return void
     */
    public function commands(string|array $commands): void
    {
        $this->batchRegister(self::SERVICE_COMMAND, $commands);
    }

    /**
     * 添加进程服务
     * @param string $process
     * @param string|null $name
     * @param int|null $num
     * @param array $options
     * @return void
     */
    public function addProcess(string $process, ?string $name = null, ?int $num = 1, array $options = []): void
    {
        $options['name'] = $name;
        $options['num'] = $num;
        $this->register(self::SERVICE_PROCESS, $process, $options);
    }

    /**
     * 添加事件监听服务
     * @param string|array $listeners
     * @return void
     */
    public function listener(string|array $listeners): void
    {
        $this->batchRegister(self::SERVICE_LISTENER, $listeners);
    }

    /**
     * 批量注册服务
     * @param string $name
     * @param string|array $services
     * @return void
     */
    protected function batchRegister(string $name, string|array $services): void
    {
        foreach ((array)$services as $service) {
            $this->register($name, $service);
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function handle(): void
    {
        $this->serviceProvider();
        $this->app->setIsInitialize(false);
        $this->app->initialize();
        $this->annotation();
        $this->generate();
        $this->generateProxyClassMap();
    }

    /**
     * @param array $services
     * @return array
     */
    protected function optimize(array $services): array
    {
        $uniqueName = [self::SERVICE_PROVIDER, self::SERVICE_COMMAND];

        foreach ($uniqueName as $name) {
            if (!isset($services[$name])) {
                continue;
            }
            $services[$name] = array_column($services[$name], null, 'class');
            $services[$name] = array_values($services[$name]);
        }

        return $services;
    }

    /**
     * @return void
     */
    protected function generate(): void
    {
        $header = '// Service automatic discovery generated at ' . date('Y-m-d H:i:s') . PHP_EOL . 'declare(strict_types=1);' . PHP_EOL;
        $content = '<?php ' . PHP_EOL . $header . "return " . var_export($this->optimize($this->services), true) . ';';
        $runtimePath = $this->app->getRuntimePath();
        $this->fileSystem->ensureDirectoryExists($runtimePath);
        $this->fileSystem->put($runtimePath . 'services.php', $content);
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function serviceProvider(): void
    {
        if ($this->fileSystem->isFile($path = $this->app->getRootPath() . 'vendor/composer/installed.json')) {
            $installed = json_decode($this->fileSystem->get($path), true);
            $packages = $installed['packages'] ?? $installed;
            foreach ($packages as $package) {
                $this->registerPackage($package);
            }
        }

        if (defined('LARMIAS_COMPOSER_FILE') && $this->fileSystem->isFile(LARMIAS_COMPOSER_FILE)) {
            $package = json_decode($this->fileSystem->get(LARMIAS_COMPOSER_FILE), true);
            $this->registerPackage($package);
        }
    }

    /**
     * @param array $package
     * @return void
     */
    protected function registerPackage(array $package): void
    {
        $extra = $package['extra']['larmias'] ?? [];
        if (!empty($extra['providers'])) {
            $providers = (array)$extra['providers'];
            foreach ($providers as $provider) {
                $this->register(self::SERVICE_PROVIDER, $provider);
                $this->app->register($provider);
            }
        }
    }

    /**
     * @return void
     */
    protected function annotation(): void
    {
        if (!class_exists(AnnotationCollector::class)) {
            return;
        }
        foreach (AnnotationCollector::all() as $item) {
            if (!isset($this->annotationCollect[$item['annotation']])) {
                continue;
            }
            $result = call_user_func([$this, $this->annotationCollect[$item['annotation']]['method']], $item);
            $this->register($this->annotationCollect[$item['annotation']]['name'], $result['class'], $result['args']);
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function generateProxyClassMap(): void
    {
        if (!$this->app->getContainer()->has(ClassScannerInterface::class)) {
            return;
        }
        /** @var ClassScannerInterface $classScan */
        $classScan = $this->app->getContainer()->get(ClassScannerInterface::class);
        $classScan->scanGenerateProxyClassMap();
    }

    /**
     * @param array $item
     * @return array
     */
    protected function collectClass(array $item): array
    {
        return [
            'class' => $item['class'],
            'args' => [],
        ];
    }

    /**
     * @param array $item
     * @return array
     */
    protected function collectProcess(array $item): array
    {
        return [
            'class' => $item['class'],
            'args' => [
                'name' => $item['value'][0]->name,
                'num' => $item['value'][0]->num,
                'timespan' => $item['value'][0]->timespan,
                'enabled' => $item['value'][0]->enabled,
                'enableCoroutine' => $item['value'][0]->enableCoroutine,
            ]
        ];
    }

    /**
     * @param array $item
     * @return array
     */
    protected function collectServer(array $item): array
    {
        return [
            'class' => $item['class'],
            'args' => [
                'type' => $item['value'][0]->type,
                'host' => $item['value'][0]->host,
                'port' => $item['value'][0]->port,
                'name' => $item['value'][0]->name,
                'num' => $item['value'][0]->num,
                'settings' => $item['value'][0]->settings,
                'enabled' => $item['value'][0]->enabled,
                'enableCoroutine' => $item['value'][0]->enableCoroutine,
            ]
        ];
    }
}