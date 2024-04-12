<?php

declare(strict_types=1);

namespace LarmiasTest\Support;

use Larmias\Support\Reflection\ReflectUtil;
use function Larmias\Framework\app;

class ReflectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetAllClassesInFile(): void
    {
        $classes = ReflectUtil::getAllClassesInFile(__DIR__ . '/ProtocolTestCase.php');
        $this->assertSame('LarmiasTest\Support\ProtocolTestCase', $classes[0]);
    }

    /**
     * @return void
     */
    public function testCheckFileSyntaxError(): void
    {
        $error = ReflectUtil::checkFileSyntaxError(app()->getRuntimePath() . 'syntax_err.php');
        $this->assertNull($error);
    }
}