<?php

declare(strict_types=1);

namespace LarmiasTest\Di;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\Aop\AopInterface;
use Larmias\Contracts\Di\ClassScannerInterface;
use Larmias\Di\AnnotationCollector;
use Larmias\Di\Container;
use Larmias\Support\Reflection\ReflectionManager;
use LarmiasTest\Di\Annotation\Classes;
use LarmiasTest\Di\Annotation\Method;
use LarmiasTest\Di\Annotation\ParentClasses;
use LarmiasTest\Di\Annotation\Props;
use LarmiasTest\Di\Classes\BaseUser;
use LarmiasTest\Di\Classes\User;
use LarmiasTest\Di\Classes\UserInfo;
use function Larmias\Framework\app;

class AopTest extends TestCase
{
    /**
     * @return void
     */
    public function testAnnotationParse(): void
    {
        /** @var AnnotationInterface $annotation */
        $annotation = Container::getInstance()->make(AnnotationInterface::class);
        $annotation->parse(BaseUser::class);
        $annotation->parse(User::class);

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

    /**
     * @return void
     */
    public function testGenerateProxyClass(): void
    {
        /** @var AopInterface $aop */
        $aop = Container::getInstance()->make(AopInterface::class);
        $file = app()->getRuntimePath() . 'User_Proxy.php';
        file_put_contents($file, $aop->generateProxyClass(User::class, __DIR__ . '/Classes/User.php'));
        $this->assertTrue(is_file($file));
    }

    /**
     * @return void
     */
    public function testClassScanGenerateProxyClass(): void
    {
        $config = [
            'include_path' => [
                __DIR__ . '/Classes',
            ],
            'runtime_path' => app()->getRuntimePath(),
        ];
        /** @var ClassScannerInterface $classScanner */
        $classScanner = Container::getInstance()->make(ClassScannerInterface::class, ['config' => $config], true);
        $classScanner->scanGenerateProxyClassMap();
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testClassScanProxyClass(): void
    {
        $config = [
            'include_path' => [
                __DIR__ . '/Classes',
            ],
            'runtime_path' => app()->getRuntimePath(),
        ];
        /** @var ClassScannerInterface $classScanner */
        $classScanner = Container::getInstance()->make(ClassScannerInterface::class, ['config' => $config], true);
        $classScanner->scan();
        /** @var User $user */
        $user = Container::getInstance()->get(User::class);
        var_dump(ReflectionManager::reflectClass($user)->getFileName());
        var_dump($user->getId());
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testInvokerClassMethod(): void
    {
        $config = [
            'include_path' => [
                __DIR__ . '/Classes',
            ],
            'runtime_path' => app()->getRuntimePath(),
        ];
        /** @var ClassScannerInterface $classScanner */
        $classScanner = Container::getInstance()->make(ClassScannerInterface::class, ['config' => $config], true);
        $classScanner->scan();
        /** @var UserInfo $userInfo */
        $userInfo = Container::getInstance()->get(UserInfo::class);
        $result = Container::getInstance()->invoke([$userInfo, 'getUserInfo']);
        $this->assertSame($result['id'], 2);
    }
}