<?php

declare(strict_types=1);

namespace Larmias\Di\Aop;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class AstManager
{
    /**
     * @var Parser
     */
    protected static Parser $parser;

    /**
     * @return Parser
     */
    public static function getParser(): Parser
    {
        if (!isset(static::$parser)) {
            static::$parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        }

        return static::$parser;
    }

    /**
     * @param string $realpath
     * @return array
     */
    public static function getNodes(string $realpath): array
    {
        $nodes = static::getParser()->parse(file_get_contents($realpath));
        return $nodes ?: [];
    }

    /**
     * @param string $realpath
     * @return array
     */
    public static function getClassesByRealPath(string $realpath): array
    {
        $classes = [];
        $nodes = static::getNodes($realpath);
        foreach ($nodes as $stmt) {
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name->toCodeString();
                foreach ($stmt->stmts as $subStmt) {
                    if ($subStmt instanceof Class_) {
                        $classes[] = $namespace . '\\' . $subStmt->name->toString();
                    }
                }
            }
        }
        return $classes;
    }
}