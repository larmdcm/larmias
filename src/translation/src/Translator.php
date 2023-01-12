<?php

declare(strict_types=1);

namespace Larmias\Translation;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\TranslatorInterface;
use Larmias\Utils\Arr;
use Larmias\Utils\Str;

class Translator implements TranslatorInterface
{
    /**
     * @var string
     */
    protected string $locale;

    /**
     * @var string[]
     */
    protected array $config = [
        'locale' => 'zh_CN',
        'fallback_locale' => 'zh_CN',
        'path' => '',
        'use_group' => false,
    ];

    /**
     * @var array
     */
    protected array $lang = [];

    /**
     * @var string
     */
    protected string $fallbackLocale;

    /**
     * Translator constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = \array_merge($this->config, $config->get('translation', []));
        $this->locale = $this->config['locale'];
        $this->fallbackLocale = $this->config['fallback_locale'];
    }

    /**
     * @param string $key
     * @param array $vars
     * @param string|null $locale
     * @return string
     */
    public function trans(string $key, array $vars = [], ?string $locale = null): string
    {
        $locale = $locale ?: $this->getLocale();
        if (!isset($this->lang[$locale])) {
            $this->loadLocale($locale);
        }
        if (!$this->has($key, $locale) && $this->has($key, $this->fallbackLocale)) {
            return $this->trans($key, $vars, $this->fallbackLocale);
        }
        return Str::template(Arr::get($this->lang[$locale], $key, ''), $vars);
    }

    /**
     * @param string $locale
     * @return self
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string|array $file
     * @param string $locale
     * @return array
     */
    public function load(string|array $file, string $locale): array
    {
        if (!isset($this->lang[$locale])) {
            $this->lang[$locale] = [];
        }

        foreach ((array)$file as $name) {
            if (\is_file($name)) {
                $result = $this->parse($name);
                $this->lang[$locale] = \array_merge($this->lang[$locale], $result);
            }
        }
        return $this->lang[$locale];
    }

    /**
     * @param string $key
     * @param string|null $locale
     * @return bool
     */
    public function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();
        if (!isset($this->lang[$locale])) {
            $this->loadLocale($locale);
        }
        return Arr::has($this->lang[$locale], $key);
    }

    /**
     * @return string
     */
    public function getFallbackLocale(): mixed
    {
        return $this->fallbackLocale;
    }

    /**
     * @param string $fallbackLocale
     */
    public function setFallbackLocale(string $fallbackLocale): self
    {
        $this->fallbackLocale = $fallbackLocale;
        return $this;
    }

    /**
     * @param string $locale
     * @return void
     */
    protected function loadLocale(string $locale): void
    {
        $path = rtrim($this->config['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . '*.*';
        $files = \glob($path);
        $this->load($files, $locale);
    }

    /**
     * @param string $file
     * @return array
     */
    protected function parse(string $file): array
    {
        $info = \pathinfo($file);

        switch ($info['extension']) {
            case 'php':
                $result = require $file;
                break;
            case 'yml':
            case 'yaml':
                if (\function_exists('yaml_parse_file')) {
                    $result = yaml_parse_file($file);
                }
                break;
            case 'json':
                $data = \file_get_contents($file);

                if (false !== $data) {
                    $data = \json_decode($data, true);

                    if (\json_last_error() === JSON_ERROR_NONE) {
                        $result = $data;
                    }
                }

                break;
        }
        if (isset($result) && \is_array($result)) {
            return $this->config['use_group'] ? [$info['filename'] => $result] : $result;
        }
        return [];
    }
}