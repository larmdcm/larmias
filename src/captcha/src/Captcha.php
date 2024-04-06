<?php

declare(strict_types=1);

namespace Larmias\Captcha;

use Larmias\Contracts\ConfigInterface;

use function is_null;
use function array_merge;
use function dirname;
use function is_array;
use function ob_start;
use function imagepng;
use function ob_get_clean;
use function imagedestroy;
use function imagecreatetruecolor;
use function imageline;
use function mt_rand;
use function imagestring;
use function imagettftext;
use function intval;
use function is_file;

class Captcha
{
    /**
     * @var array
     */
    protected array $config = [
        'charset' => 'ABCDEFGHKMNPRSTUVWXYZ23456789',
        'width' => 130,
        'height' => 50,
        'length' => 4,
        'font_size' => 20,
        'font' => null,
    ];

    /**
     * @var resource|\GdImage
     */
    protected mixed $image;

    /**
     * @param ConfigInterface|array|null $config
     */
    public function __construct(ConfigInterface|array|null $config = null)
    {
        if (!is_null($config)) {
            $this->config = array_merge($this->config, is_array($config) ? $config : $config->get('captcha', []));
        }

        if (is_null($this->config['font'])) {
            $this->config['font'] = dirname(__DIR__) . '/resources/fonts/captcha.ttf';
        }
    }

    /**
     * @param string|null $code
     * @return Result
     */
    public function create(?string $code = null): Result
    {
        if (!$code) {
            $code = $this->getCode();
        }
        $this->background();
        $this->writeLine();
        $this->writeNoise();
        $this->writeText($code);
        ob_start();
        // 输出图像
        imagepng($this->image);
        $content = ob_get_clean();
        imagedestroy($this->image);

        return new Result($content, $code);
    }

    /**
     * @return void
     */
    protected function background(): void
    {
        $this->image = imagecreatetruecolor($this->config['width'], $this->config['height']);
        $color = imagecolorallocate($this->image, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        imagefilledrectangle($this->image, 0, $this->config['height'], $this->config['width'], 0, $color);
    }

    /**
     * @return void
     */
    protected function writeLine(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->image, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline($this->image, mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']),
                mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), $color);
        }
    }

    /**
     * @return void
     */
    protected function writeNoise(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->image, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->image, mt_rand(1, 5), mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), '*', $color);
        }
    }

    /**
     * @param string $code
     * @return void
     */
    protected function writeText(string $code): void
    {
        $x = $this->config['width'] / $this->config['length'];
        for ($i = 0; $i < $this->config['length']; $i++) {
            $color = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 150), mt_rand(0, 200));
            if ($this->config['font'] && is_file($this->config['font'])) {
                imagettftext($this->image, 16, mt_rand(-30, 30), intval($x * $i + mt_rand(1, 5)), intval($this->config['height'] / 1.4), $color, $this->config['font'], $code[$i]);
            } else {
                imagestring($this->image, 5, intval($i * $this->config['width'] / $this->config['font_size'] + mt_rand(1, 10)), mt_rand(1, intval($this->config['height'] / 2)), $code[$i], $color);
            }
        }
    }

    /**
     * @return string
     */
    protected function getCode(): string
    {
        $code = '';
        for ($i = 0; $i < $this->config['length']; $i++) {
            $code .= $this->config['charset'][mt_rand(0, $this->config['length'])];
        }
        return $code;
    }
}