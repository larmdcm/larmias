<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Command\Annotation\Command;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ServiceDiscoverInterface;
use Closure;
use Larmias\Engine\Timer;
use Larmias\Event\Annotation\Listener;
use Larmias\Framework\Annotation\Provider;
use Larmias\Process\Annotation\Process;
use Larmias\Utils\FileSystem;
use RuntimeException;
use function class_exists;
use function extension_loaded;
use function array_merge;
use function array_column;
use function array_values;
use function date;
use function var_export;
use function json_decode;
use function call_user_func;
use function method_exists;
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
     */
    public function __construct(protected ApplicationInterface $app)
    {
        $this->fileSystem = $this->app->getContainer()->get(FileSystem::class);
    }

    /**
     * 发现服务配置
     * @param Closure $callback
     * @return void
     * @throws \Throwable
     */
    public function discover(Closure $callback): void
    {
        if (extension_loaded('pcntl')) {
            $pid = \pcntl_fork();
            if ($pid === -1) {
                throw new RuntimeException('fork process error.');
            } else if ($pid === 0) {
                if (method_exists($this->app, 'setIsInit')) {
                    $this->app->setIsInit(true);
                }
                run(function () {
                    try {
                        $this->handle();
                    } finally {
                        Timer::clear();
                    }
                }, [
                    'settings' => ['logger' => false]
                ]);
                exit(0);
            }
            \pcntl_wait($status, \WUNTRACED);
        } else {
            $this->handle();
        }
        $callback();
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
     * @param string $name
     * @param int $count
     * @return void
     */
    public function addProcess(string $process, string $name, int $count = 1): void
    {
        $this->register(self::SERVICE_PROCESS, $process, ['name' => $name, 'count' => $count]);
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
     */
    protected function handle(): void
    {
        $this->serviceProvider();
        if (method_exists($this->app, 'setIsInit')) {
            $this->app->setIsInit(false);
        }
        $this->app->initialize();
        $this->annotation();
        $this->generate();
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
        if (class_exists('\Larmias\Di\AnnotationCollector')) {
            foreach (\Larmias\Di\AnnotationCollector::all() as $item) {
                if (!isset($this->annotationCollect[$item['annotation']])) {
                    continue;
                }
                $result = call_user_func([$this, $this->annotationCollect[$item['annotation']]['method']], $item);
                $this->register($this->annotationCollect[$item['annotation']]['name'], $result['class'], $result['args']);
            }
        }
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
                'count' => $item['value'][0]->count,
            ]
        ];
    }
}