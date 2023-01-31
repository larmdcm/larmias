<?php

declare(strict_types=1);

namespace Larmias\Captcha;

use Larmias\Contracts\ConfigInterface;

class Captcha
{
    /**
     * @var array
     */
    protected array $config = [
        'width' => 130,
        'height' => 50,
        'length' => 4,
        'font_size' => 20,
    ];

    /**
     * @var resource|\GdImage
     */
    protected mixed $image;

    /**
     * Captcha constructor.
     * @param ConfigInterface|null $config
     */
    public function __construct(?ConfigInterface $config = null)
    {
        if (!\is_null($config)) {
            $this->config = \array_merge($this->config, $config->get('captcha', []));
        }
    }

    /**
     * @return void
     */
    protected function background(): void
    {
        $this->image = \imagecreatetruecolor($this->config['width'], $this->config['height']);
        $color = \imagecolorallocate($this->image, \mt_rand(157, 255), \mt_rand(157, 255), \mt_rand(157, 255));
        \imagefilledrectangle($this->image, 0, $this->config['width'], $this->config['height'], 0, $color);
    }

    /**
     * @return void
     */
    protected function writeLine(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $color = \imagecolorallocate($this->image, \mt_rand(0, 156), \mt_rand(0, 156), \mt_rand(0, 156));
            imageline($this->image, \mt_rand(0, $this->config['width']), \mt_rand(0, $this->config['height']),
                \mt_rand(0, $this->config['width']), \mt_rand(0, $this->config['height']), $color);
        }
    }
}