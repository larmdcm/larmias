<?php

declare(strict_types=1);

namespace Larmias\View\Driver;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ViewInterface;
use function method_exists;
use function is_array;

abstract class Driver implements ViewInterface
{
    /**
     * @var array
     */
    protected array $vars = [];

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, protected array $config = [])
    {
        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @param string|array $name
     * @param mixed|null $value
     * @return ViewInterface
     */
    public function with(string|array $name, mixed $value = null): ViewInterface
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$name] = $value;
        }
        return $this;
    }
}