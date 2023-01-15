<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface TranslatorInterface
{
    /**
     * @param string $key
     * @param array $vars
     * @param string|null $locale
     * @return string
     */
    public function trans(string $key, array $vars = [], ?string $locale = null): string;

    /**
     * @param string $locale
     * @return TranslatorInterface
     */
    public function setLocale(string $locale): TranslatorInterface;

    /**
     * @return string
     */
    public function getLocale(): string;

    /**
     * @param string|array $file
     * @param string $locale
     * @param string|null $groupName
     * @return array
     */
    public function load(string|array $file, string $locale, ?string $groupName = null): array;

    /**
     * @param string $key
     * @param string|null $locale
     * @return bool
     */
    public function has(string $key, ?string $locale = null): bool;
}