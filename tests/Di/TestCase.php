<?php

declare(strict_types=1);

namespace LarmiasTest\Di;

use Larmias\Context\ApplicationContext;
use Larmias\Di\Annotation;
use Larmias\Di\AnnotationManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function setUp(): void
    {
        $container = ApplicationContext::getContainer();
        $config = [
            'include_path' => [
                __DIR__ . '/Classes'
            ],
            'exclude_path' => [],
            'handlers' => [],
        ];
        $annotation = new Annotation($container, $config);
        AnnotationManager::init($annotation);
    }
}