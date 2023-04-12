<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ServiceDiscoverInterface;
use Closure;
use RuntimeException;
use function class_exists;
use function extension_loaded;
use function is_file;
use function array_merge;
use function array_column;
use function array_values;
use function file_put_contents;
use function date;
use function var_export;
use function json_decode;
use function file_get_contents;
use function call_user_func;
use const PHP_EOL;

class ServiceDiscover implements ServiceDiscoverInterface
{
    /**
     * @var string[]
     */
    protected array $annotationCollect = [
        'Larmias\Process\Annotation\Process' => [
            'name' => ServiceDiscoverInterface::SERVICE_PROCESS,
            'method' => 'collectProcess',
        ],
        'Larmias\Command\Annotation\Command' => [
            'name' => ServiceDiscoverInterface::SERVICE_COMMAND,
            'method' => 'collectCommand',
        ],
    ];

    /**
     * @var array
     */
    protected array $services = [];

    /**
     * @param ApplicationInterface $app
     */
    public function __construct(protected ApplicationInterface $app)
    {
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public function discover(Closure $callback): void
    {
        if (extension_loaded('pcntl')) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new RuntimeException('fork process error.');
            } else if ($pid === 0) {
                $this->handle();
                exit(0);
            }
            pcntl_wait($status, WUNTRACED);
        } else {
            $this->handle();
        }
        $callback();
    }

    /**
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
     * @return array
     */
    public function services(): array
    {
        $file = $this->app->getRuntimePath() . 'services.php';
        $services = $this->services;
        if (is_file($file)) {
            $services = array_merge(require $file, $services);
        }
        return $this->optimize($services);
    }

    /**
     * @param string $process
     * @param string $name
     * @param int $count
     * @return void
     */
    public function addProcess(string $process, string $name, int $count = 1): void
    {
        $this->register(ServiceDiscoverInterface::SERVICE_PROCESS, $process, ['name' => $name, 'count' => $count]);
    }

    /**
     * @param string|array $commands
     * @return void
     */
    public function commands(string|array $commands): void
    {
        foreach ((array)$commands as $command) {
            $this->register(ServiceDiscoverInterface::SERVICE_COMMAND, $command);
        }
    }

    /**
     * @return void
     */
    protected function handle(): void
    {
        $this->app->setStatus(ApplicationInterface::STATUS_PRELOAD)->initialize();
        $this->serviceProvider();
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
        file_put_contents($this->app->getRuntimePath() . 'services.php', $content);
    }

    /**
     * @return void
     */
    protected function serviceProvider(): void
    {
        if (is_file($path = $this->app->getRootPath() . 'vendor/composer/installed.json')) {
            $installed = json_decode(file_get_contents($path), true);
            $packages = $installed['packages'] ?? $installed;

            foreach ($packages as $package) {
                $extra = $package['extra']['larmias'] ?? [];
                if (!empty($extra['providers'])) {
                    foreach ((array)$extra['providers'] as $provider) {
                        $this->register(ServiceDiscoverInterface::SERVICE_PROVIDER, $provider);
                    }
                }
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

    /**
     * @param array $item
     * @return array
     */
    protected function collectCommand(array $item): array
    {
        return [
            'class' => $item['class'],
            'args' => [],
        ];
    }
}