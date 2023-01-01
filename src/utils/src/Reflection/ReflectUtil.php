<?php

declare(strict_types=1);

namespace Larmias\Utils\Reflection;

use ReflectionProperty;

class ReflectUtil
{
    public static function setProperty(ReflectionProperty $refProperty,object $object,mixed $value): void
    {
        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }
        $refProperty->setValue($object,$value);
    }

    public static function getProperty(ReflectionProperty $refProperty,object $object): mixed
    {
        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }
        return $refProperty->getValue($object);
    }

    /**
     * 获取文件中的所有类
     *
     * @param string $file
     * @return array
     */
    public static function getAllClassesInFile(string $file): array
    {
        if (!\is_file($file) || !\is_readable($file)) {
            throw new \RuntimeException('Not a file or a readable file: ' . $file);
        }
        $classes = [];
        $tokens = \token_get_all(file_get_contents($file));
        $count = \count($tokens);
        $tNamespace = [\T_NAME_QUALIFIED,\T_STRING];
        $namespace = '';

        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] === \T_NAMESPACE && $tokens[$i - 1][0] === \T_WHITESPACE) {
                $namespace = '';
                if (!\in_array($tokens[$i][0],$tNamespace)) {
                    continue;
                }
                $tempNamespace = $tokens[$i][1] ?? '';
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                    if (\in_array($tokens[$j][0],$tNamespace)) {
                        $tempNamespace .= '\\' . $tokens[$j][1];
                    }
                }
                $namespace = $tempNamespace;
            } else if ($tokens[$i - 2][0] === \T_CLASS && $tokens[$i - 1][0] === \T_WHITESPACE && $tokens[$i][0] === \T_STRING) {
                $classes[] = ($namespace ? $namespace . '\\' : '') . $tokens[$i][1];
            }
        }

        return $classes;
    }
}