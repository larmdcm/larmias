<?php

declare(strict_types=1);

namespace Larmias\Di\Aop;

use Larmias\Stringable\Str;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class ProxyHandlerVisitor extends NodeVisitorAbstract
{
    public function __construct(protected Metadata $metadata)
    {
    }

    public function leaveNode(Node $node): void
    {
        if ($node instanceof Class_) {
            $node->stmts = array_merge(
                [new TraitUse([new Name('\\' . ProxyHandler::class)])],
                $node->stmts
            );
        }

        if ($node instanceof ClassMethod) {
            $methodName = $node->name->toString();
            if ($methodName === '__construct') {
                $this->metadata->hasConstructor = true;
                return;
            }
            $methodCall = new Node\Expr\StaticCall(
                new Name('self'),
                '__callViaProxy',
                [
                    new Arg(new Function_()),
                    new Arg(new FuncCall(new Name('func_get_args'))),
                    new Arg(new Closure([
                        'params' => $node->getParams(),
                        'stmts' => $node->stmts,
                    ])),
                ]
            );
            $returnType = $node->getReturnType();
            if ($returnType instanceof Identifier && $returnType->name === 'void') {
                $node->stmts = [new Expression($methodCall)];
            } else {
                if ($node->returnsByRef()) {
                    $valueRef = new Variable('__returnValueRef_' . Str::random());
                    $node->stmts = [new Expression(new Assign($valueRef, $methodCall)), new Return_($valueRef)];
                } else {
                    $node->stmts = [new Return_($methodCall)];
                }
            }
        }
    }
}