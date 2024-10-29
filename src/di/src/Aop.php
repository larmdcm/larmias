<?php

declare(strict_types=1);

namespace Larmias\Di;

use Larmias\Contracts\Aop\AopInterface;
use Larmias\Di\Aop\AstManager;
use Larmias\Di\Aop\Metadata;
use Larmias\Di\Aop\ProxyHandlerVisitor;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;

class Aop implements AopInterface
{
    /**
     * 生成代理类文件
     * @param string $class
     * @param string $realpath
     * @return string
     */
    public function generateProxyClass(string $class, string $realpath): string
    {
        $traverser = new NodeTraverser();
        $metadata = new Metadata($class);
        $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
        return (new Standard())->prettyPrintFile($traverser->traverse(AstManager::getNodes($realpath)));
    }
}