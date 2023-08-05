<?php

declare(strict_types=1);

namespace Larmias\Translation;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\TranslatorInterface;
use Larmias\Utils\Arr;
use Larmias\Utils\Str;
use function array_merge;
use function is_file;
use function rtrim;
use function is_dir;
use function glob;
use function pathinfo;
use function file_get_contents;
use function function_exists;
use function json_decode;
use function json_last_error;
use function is_array;
use const JSON_ERROR_NONE;

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
        $this->config = array_merge($this->config, $config->get('translation', []));
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
        $this->loadLocale($locale);
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
     * @param string|null $groupName
     * @return array
     */
    public function load(string|array $file, string $locale, ?string $groupName = null): array
    {
        if (!isset($this->lang[$locale])) {
            $this->lang[$locale] = [];
        }
        if ($groupName && !isset($this->lang[$locale][$groupName])) {
            $this->lang[$locale][$groupName] = [];
        }

        foreach ((array)$file as $name) {
            if (is_file($name)) {
                $result = $this->parse($name);
                if ($groupName) {
                    $this->lang[$locale][$groupName] = array_merge($this->lang[$locale][$groupName], $result);
                } else {
                    $this->lang[$locale] = array_merge($this->lang[$locale], $result);
                }
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
        $this->loadLocale($locale);
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
        if (!isset($this->lang[$locale])) {
            $path = rtrim($this->config['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . '*';
            $list = glob($path);

            if (empty($list)) {
                $this->lang[$locale] = [];
                return;
            }

            foreach ($list as $item) {
                if (is_dir($item)) {
                    $this->load(glob($item . DIRECTORY_SEPARATOR . '*.*'), $locale, \basename($item));
                } else {
                    $this->load($item, $locale);
                }
            }
        }
    }

    /**
     * @param string $file
     * @return array
     */
    protected function parse(string $file): array
    {
        $info = pathinfo($file);

        switch ($info['extension']) {
            case 'php':
                $result = require $file;
                break;
            case 'yml':
            case 'yaml':
                if (function_exists('yaml_parse_file')) {
                    $result = yaml_parse_file($file);
                }
                break;
            case 'json':
                $data = file_get_contents($file);

                if (false !== $data) {
                    $data = json_decode($data, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $result = $data;
                    }
                }

                break;
        }
        return isset($result) && is_array($result) ? $result : [];
    }
}