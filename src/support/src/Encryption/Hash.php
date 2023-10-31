<?php

declare(strict_types=1);

namespace Larmias\Support\Encryption;

use function array_merge;
use function password_hash;
use function password_verify;
use const PASSWORD_DEFAULT;

class Hash
{
    /**
     * @param string $password
     * @param array $options
     */
    public function __construct(protected string $password, protected string|int|null $algo = PASSWORD_DEFAULT, protected array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param string $password
     * @param string|int|null $algo
     * @param array $options
     * @return static
     */
    public static function make(string $password, string|int|null $algo = PASSWORD_DEFAULT, array $options = []): static
    {
        return new static($password, $algo, $options);
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return password_hash($this->password, $this->algo, $this->options);
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function check(string $hash): bool
    {
        return password_verify($this->password, $hash);
    }
}