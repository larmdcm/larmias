<?php

declare(strict_types=1);

namespace Larmias\JWTAuth;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\JWTAuth\Contracts\JWTInterface;

abstract class AbstractJWT implements JWTInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var string
     */
    protected string $scene = 'default';

    /**
     * @var string
     */
    protected string $tokenScenePrefix = 'jwt_scene';

    /**
     * @param ContainerInterface $container
     * @throws \Throwable
     */
    public function __construct(protected ContainerInterface $container)
    {
        $config = $this->container->get(ConfigInterface::class);
        $this->config = $config->get('jwt', []);
        $scenes = $this->config['scene'];
        unset($this->config['scene']);
        foreach ($scenes as $key => $scene) {
            $sceneConfig = array_merge($this->config, $scene);
            $this->setSceneConfig($key, $sceneConfig);
        }

        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * 获取当前场景
     * @return string
     */
    public function getScene(): string
    {
        return $this->scene;
    }

    /**
     * 设置当前场景
     * @param string $scene
     * @return self
     */
    public function setScene(string $scene = 'default'): self
    {
        $this->scene = $scene;
        return $this;
    }

    /**
     * 获取场景配置
     * @param string $scene
     * @return array
     */
    public function getSceneConfig(string $scene = 'default'): array
    {
        return $this->config[$scene] ?? [];
    }

    /**
     * 设置场景配置
     * @param string $scene
     * @param array $value
     * @return self
     */
    public function setSceneConfig(string $scene = 'default', array $value = []): self
    {
        $this->config[$scene] = $value;
        return $this;
    }

    /**
     * 获取当前场景配置
     * @return array
     */
    public function getCurrSceneConfig(): array
    {
        return $this->getSceneConfig($this->getScene());
    }
}