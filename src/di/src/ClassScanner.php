<?php

declare(strict_types=1);

namespace Larmias\Di;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\Aop\AopInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Di\ClassScannerInterface;
use Larmias\Di\Aop\AspectCollector;
use Larmias\Support\Composer;
use Larmias\Support\FileSystem;
use Larmias\Support\FileSystem\Finder;
use Larmias\Support\Reflection\ReflectUtil;
use Throwable;

class ClassScanner implements ClassScannerInterface
{
    /**
     * 配置
     * @var array
     */
    protected array $config = [
        'include_path' => [],
        'exclude_path' => [],
        'runtime_path' => null,
        'proxy_class_generate' => true,
        'proxy_class_cache' => false,
        'annotation_handlers' => [],
        'check_syntax' => true,
    ];

    /**
     * @var FileSystem
     */
    protected FileSystem $fileSystem;

    /**
     * @var array
     */
    protected array $classMap = [];

    /**
     * @var string
     */
    protected string $proxyClassFile;

    /**
     * @param ContainerInterface $container
     * @param AnnotationInterface $annotation
     * @param AopInterface $aop
     * @param array $config
     */
    public function __construct(
        protected ContainerInterface  $container,
        protected AnnotationInterface $annotation,
        protected AopInterface        $aop,
        array                         $config = []
    )
    {
        $this->config = array_merge($this->config, $config);
        $this->fileSystem = new FileSystem();
        foreach ((array)$this->config['annotation_handlers'] as $annot => $handler) {
            $this->annotation->addHandler($annot, $handler);
        }

        if (empty($this->config['runtime_path'])) {
            $this->config['runtime_path'] = sys_get_temp_dir();
        }

        $this->config['runtime_path'] = rtrim($this->config['runtime_path'], '/');

        $this->proxyClassFile = $this->config['runtime_path'] . '/proxyClassMap.php';
    }

    /**
     * 添加扫描路径
     * @param string|array $path
     * @return ClassScannerInterface
     */
    public function addIncludePath(string|array $path): ClassScannerInterface
    {
        $this->config['include_path'] = array_merge($this->config['include_path'], (array)$path);
        return $this;
    }

    /**
     * 添加扫描排除路径
     * @param string|array $path
     * @return ClassScannerInterface
     */
    public function addExcludePath(string|array $path): ClassScannerInterface
    {
        $this->config['exclude_path'] = array_merge($this->config['exclude_path'], (array)$path);
        return $this;
    }

    /**
     * @param string $class
     * @param string $realpath
     * @return ClassScannerInterface
     */
    public function addClass(string $class, string $realpath): ClassScannerInterface
    {
        $this->classMap[$class] = $realpath;
        return $this;
    }

    /**
     * 扫描
     * @return void
     * @throws Throwable
     */
    public function scan(): void
    {
        $proxyClassMap = $this->config['proxy_class_generate'] ? $this->getProxyClassMap() : [];
        if (!empty($proxyClassMap)) {
            Composer::getClassLoader()->addClassMap($proxyClassMap);
        }
        $this->scanFilesGenerateClassMap();
        $this->collectAnnotation();
    }

    /**
     * 生成代理类
     * @return void
     */
    public function scanGenerateProxyClassMap(): void
    {
        if ($this->config['proxy_class_cache'] && $this->fileSystem->isFile($this->proxyClassFile)) {
            return;
        }

        $this->scanFilesGenerateClassMap();
        $this->collectAnnotation();
        $collectClass = AspectCollector::getAspectClasses();
        $proxyDir = $this->config['runtime_path'] . '/proxy';

        $this->fileSystem->isDirectory($proxyDir) && $this->fileSystem->cleanDirectory($proxyDir);
        $this->fileSystem->ensureDirectoryExists($proxyDir);

        $proxies = [];
        foreach ($collectClass as $class) {
            $filePath = $this->classMap[$class] ?? null;
            if (!$filePath) {
                continue;
            }
            $proxyPath = $proxyDir . '/' . str_replace('\\', '_', $class) . '_Proxy.php';
            $this->fileSystem->put($proxyPath, $this->aop->generateProxyClass($class, $filePath));
            $proxies[$class] = $proxyPath;
        }

        $this->fileSystem->put($this->proxyClassFile, sprintf("<?php \nreturn %s;", var_export($proxies, true)));
    }

    /**
     * @return void
     */
    public function scanFilesGenerateClassMap(): void
    {
        $files = Finder::create()->include($this->config['include_path'])->exclude($this->config['exclude_path'])->includeExt('php')->files();
        foreach ($files as $file) {
            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
            $error = $this->config['check_syntax'] ? ReflectUtil::checkFileSyntaxError($filePath) : null;
            if ($error !== null) {
                continue;
            }
            $classes = ReflectUtil::getAllClassesInFile($filePath);
            if (empty($classes)) {
                continue;
            }
            foreach ($classes as $class) {
                $this->addClass($class, $filePath);
            }
        }
    }

    /**
     * @return void
     */
    public function collectAnnotation(): void
    {
        foreach ($this->classMap as $class => $filePath) {
            $this->annotation->parse($class);
        }
        $this->annotation->handle();
    }

    /**
     * @return array
     */
    public function getProxyClassMap(): array
    {
        if (!$this->fileSystem->isFile($this->proxyClassFile)) {
            return [];
        }

        return require $this->proxyClassFile;
    }
}