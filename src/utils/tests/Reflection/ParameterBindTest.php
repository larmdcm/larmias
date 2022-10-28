<?php

declare(strict_types=1);

namespace Reflection;

use Larmias\Utils\Reflection\ParameterBind;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

class TestClass
{
    public string $name = 'class';

    public function __construct(string $name,int $age = 23,TestClass2 $test = null)
    {

    }

    public function test()
    {
        println($this->name);
    }
};

class TestClass2
{

}

final class ParameterBindTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function test(): void
    {
        $func = function (string $name = 'function') {
            println($name);
        };
        $classRef  = new ReflectionClass(TestClass::class);
        $paramBind = new ParameterBind($classRef->getConstructor());
        $args      = $paramBind('test');
        var_dump($args);
        $this->assertTrue(true);
    }
}