<?php

declare(strict_types=1);

namespace Larmias\Http\Message;

use InvalidArgumentException;
use DateTimeInterface;
use Stringable;

class Cookie implements Stringable
{
    /** @var string */
    public const SAMESITE_LAX = 'lax';

    /** @var string */
    public const SAMESITE_STRICT = 'strict';

    /** @var string */
    public const SAMESITE_NONE = 'none';

    /**
     * @var int
     */
    protected int $expire = 0;

    /**
     * Cookie constructor.
     * @param string $name
     * @param string $value
     * @param \DateTimeInterface|int|string $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     */
    public function __construct(
        protected string $name,
        protected string $value = '',
        DateTimeInterface|int|string $expire = 0,
        protected string $path = '/',
        protected string $domain = '',
        protected bool $secure = false,
        protected bool $httpOnly = true,
        protected bool $raw = false,
        protected ?string $sameSite = null
    )
    {
        if (\preg_match("/[=,; \t\r\n\013\014]/", $this->name)) {
            throw new InvalidArgumentException(\sprintf('The cookie name "%s" contains invalid characters.', $this->name));
        }
        if ($expire instanceof DateTimeInterface) {
            $expire = (int)$expire->format('U');
        } else if (!\is_numeric($expire)) {
            $expire = \strtotime($expire);
        }

        $this->expire = $expire;

        if ($this->sameSite !== null) {
            $this->sameSite = strtolower($sameSite);
        }

        if (!in_array($this->sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE, null], true)) {
            throw new \InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString(): string
    {
        $str = ($this->isRaw() ? $this->getName() : urlencode($this->getName())) . '=';

        if ($this->getValue() === '') {
            $str .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001) . '; max-age=-31536001';
        } else {
            $str .= $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());

            if ($this->getExpiresTime() !== 0) {
                $str .= '; expires=' . gmdate(
                        'D, d-M-Y H:i:s T',
                        $this->getExpiresTime()
                    ) . '; max-age=' . $this->getMaxAge();
            }
        }

        if ($this->getPath()) {
            $str .= '; path=' . $this->getPath();
        }

        if ($this->getDomain()) {
            $str .= '; domain=' . $this->getDomain();
        }

        if ($this->isSecure() === true) {
            $str .= '; secure';
        }

        if ($this->isHttpOnly() === true) {
            $str .= '; httponly';
        }

        if ($this->getSameSite() !== null) {
            $str .= '; samesite=' . $this->getSameSite();
        }

        return $str;
    }

    /**
     * Gets the name of the cookie.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of the cookie.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the domain that the cookie is available to.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Gets the time the cookie expires.
     *
     * @return int
     */
    public function getExpiresTime(): int
    {
        return $this->expire;
    }

    /**
     * Gets the max-age attribute.
     *
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->expire !== 0 ? $this->expire - time() : 0;
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     *
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * Whether this cookie is about to be cleared.
     *
     * @return bool
     */
    public function isCleared(): bool
    {
        return $this->expire < time();
    }

    /**
     * Checks if the cookie value should be sent with no url encoding.
     *
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * Gets the SameSite attribute.
     *
     * @return string|null
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }
}