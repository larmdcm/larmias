<?php

declare(strict_types=1);

namespace LarmiasTest\Di;

use Larmias\Di\AnnotationCollector;
use Larmias\Di\AnnotationManager;
use LarmiasTest\Di\Annotation\Classes;
use LarmiasTest\Di\Annotation\Method;
use LarmiasTest\Di\Annotation\ParentClasses;
use LarmiasTest\Di\Annotation\Props;
use LarmiasTest\Di\Classes\User;

class AnnotationTest extends TestCase
{
    /**
     * @return void
     */
    public function testScan(): void
    {
        AnnotationManager::scan();

        $classAnnotation = AnnotationCollector::get(sprintf('%s.class', User::class));
        $this->assertSame(array_keys($classAnnotation), [
            ParentClasses::class,
            Classes::class,
        ]);

        $propAnnotation = AnnotationCollector::get(sprintf('%s.property', User::class));
        $this->assertSame(array_keys($propAnnotation), [
            'id', 'name', 'baseName'
        ]);
        $this->assertSame(key($propAnnotation['id']), Props::class);

        $methodAnnotation = AnnotationCollector::get(sprintf('%s.method', User::class));
        $this->assertSame(key($methodAnnotation['getId']), Method::class);
    }
}