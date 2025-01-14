<?php

declare(strict_types=1);

namespace Larmias\Paginator;

use Larmias\Contracts\CollectionInterface;
use Larmias\Contracts\PaginatorInterface;
use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use DomainException;
use IteratorAggregate;
use JsonSerializable;
use Larmias\Paginator\Driver\Bootstrap;
use Larmias\Collection\Collection;
use Traversable;

abstract class Paginator implements PaginatorInterface, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * 是否简洁模式
     * @var bool
     */
    protected bool $simple = false;

    /**
     * 数据集
     * @var CollectionInterface
     */
    protected CollectionInterface $items;

    /**
     * 当前页
     * @var int
     */
    protected int $currentPage;

    /**
     * 最后一页
     * @var int
     */
    protected int $lastPage;

    /**
     * 数据总数
     * @var integer|null
     */
    protected ?int $total;

    /**
     * 每页数量
     * @var int
     */
    protected int $listRows;

    /**
     * 是否有下一页
     * @var bool
     */
    protected bool $hasMore;

    /**
     * 分页配置
     * @var array
     */
    protected array $options = [
        'var_page' => 'page',
        'path' => '/',
        'query' => [],
        'fragment' => '',
    ];

    /**
     * 获取当前页码
     * @var Closure
     */
    protected static Closure $currentPageResolver;

    /**
     * 获取当前路径
     * @var Closure
     */
    protected static Closure $currentPathResolver;

    /**
     * @var Closure
     */
    protected static Closure $maker;

    /**
     * @param array|CollectionInterface $items
     * @param int $listRows
     * @param int $currentPage
     * @param int|null $total
     * @param bool $simple
     * @param array $options
     */
    public function __construct(array|CollectionInterface $items, int $listRows, int $currentPage = 1, int $total = null, bool $simple = false, array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->options['path'] = '/' != $this->options['path'] ? rtrim($this->options['path'], '/') : $this->options['path'];

        $this->simple = $simple;
        $this->listRows = $listRows;

        if (!$items instanceof CollectionInterface) {
            $items = Collection::make($items);
        }

        if ($simple) {
            $this->currentPage = $this->setCurrentPage($currentPage);
            $this->hasMore = count($items) > ($this->listRows);
            $items = $items->slice(0, $this->listRows);
        } else {
            $this->total = $total;
            $this->lastPage = (int)ceil($total / $listRows);
            $this->currentPage = $this->setCurrentPage($currentPage);
            $this->hasMore = $this->currentPage < $this->lastPage;
        }
        $this->items = $items;
    }

    /**
     * @param mixed $items
     * @param int $listRows
     * @param int $currentPage
     * @param int|null $total
     * @param bool $simple
     * @param array $options
     * @return Paginator
     */
    public static function make(array|CollectionInterface $items, int $listRows, int $currentPage = 1, ?int $total = null, bool $simple = false, array $options = []): PaginatorInterface
    {
        if (isset(static::$maker)) {
            return call_user_func(static::$maker, $items, $listRows, $currentPage, $total, $simple, $options);
        }

        return new Bootstrap($items, $listRows, $currentPage, $total, $simple, $options);
    }

    public static function maker(Closure $resolver): void
    {
        static::$maker = $resolver;
    }

    protected function setCurrentPage(int $currentPage): int
    {
        if (!$this->simple && $currentPage > $this->lastPage) {
            return $this->lastPage > 0 ? $this->lastPage : 1;
        }

        return $currentPage;
    }

    /**
     * 获取页码对应的链接
     *
     * @access protected
     * @param int $page
     * @return string
     */
    protected function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        if (!str_contains($this->options['path'], '[PAGE]')) {
            $parameters = [$this->options['var_page'] => $page];
            $path = $this->options['path'];
        } else {
            $parameters = [];
            $path = str_replace('[PAGE]', (string)$page, $this->options['path']);
        }

        if (count($this->options['query']) > 0) {
            $parameters = array_merge($this->options['query'], $parameters);
        }

        $url = $path;
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        }

        return $url . $this->buildFragment();
    }

    /**
     * 自动获取当前页码
     * @param string $varPage
     * @param int $default
     * @return int
     */
    public static function getCurrentPage(string $varPage = 'page', int $default = 1): int
    {
        if (isset(static::$currentPageResolver)) {
            return call_user_func(static::$currentPageResolver, $varPage);
        }

        return $default;
    }

    /**
     * 设置获取当前页码闭包
     * @param Closure $resolver
     */
    public static function currentPageResolver(Closure $resolver): void
    {
        static::$currentPageResolver = $resolver;
    }

    /**
     * 自动获取当前的path
     * @param string $default
     * @return string
     */
    public static function getCurrentPath(string $default = '/'): string
    {
        if (isset(static::$currentPathResolver)) {
            return call_user_func(static::$currentPathResolver);
        }

        return $default;
    }

    /**
     * 设置获取当前路径闭包
     * @param Closure $resolver
     */
    public static function currentPathResolver(Closure $resolver): void
    {
        static::$currentPathResolver = $resolver;
    }

    /**
     * 获取数据总条数
     * @return int
     */
    public function total(): int
    {
        if ($this->simple) {
            throw new DomainException('not support total');
        }

        return $this->total;
    }

    /**
     * 获取每页数量
     * @return int
     */
    public function listRows(): int
    {
        return $this->listRows;
    }

    /**
     * 获取当前页页码
     * @return int
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * 获取最后一页页码
     * @return int
     */
    public function lastPage(): int
    {
        if ($this->simple) {
            throw new DomainException('not support last');
        }

        return $this->lastPage;
    }

    /**
     * 数据是否足够分页
     * @return bool
     */
    public function hasPages(): bool
    {
        return !(1 == $this->currentPage && !$this->hasMore);
    }

    /**
     * 创建一组分页链接
     *
     * @param int $start
     * @param int $end
     * @return array
     */
    public function getUrlRange(int $start, int $end): array
    {
        $urls = [];

        for ($page = $start; $page <= $end; $page++) {
            $urls[$page] = $this->url($page);
        }

        return $urls;
    }

    /**
     * 设置URL锚点
     *
     * @param string|null $fragment
     * @return PaginatorInterface
     */
    public function fragment(string $fragment = null): PaginatorInterface
    {
        $this->options['fragment'] = $fragment;

        return $this;
    }

    /**
     * 添加URL参数
     *
     * @param array $append
     * @return PaginatorInterface
     */
    public function appends(array $append): PaginatorInterface
    {
        foreach ($append as $k => $v) {
            if ($k !== $this->options['var_page']) {
                $this->options['query'][$k] = $v;
            }
        }

        return $this;
    }

    /**
     * 构造锚点字符串
     *
     * @return string
     */
    protected function buildFragment(): string
    {
        return $this->options['fragment'] ? '#' . $this->options['fragment'] : '';
    }

    /**
     * @return array
     */
    public function items(): array
    {
        return $this->items->all();
    }

    /**
     * 获取数据集
     *
     * @return CollectionInterface
     */
    public function getCollection(): CollectionInterface
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * 给每个元素执行个回调
     *
     * @param callable $callback
     * @return PaginatorInterface
     */
    public function each(callable $callback): PaginatorInterface
    {
        foreach ($this->items as $key => $item) {
            $result = $callback($item, $key);

            if (false === $result) {
                break;
            } elseif (!is_object($item)) {
                $this->items[$key] = $result;
            }
        }

        return $this;
    }

    /**
     * Retrieve an external iterator
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items->all());
    }

    /**
     * Whether offset exists
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->items->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items->offsetGet($offset);
    }

    /**
     * Offset to set
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items->offsetSet($offset, $value);
    }

    /**
     * Offset to unset
     * @param mixed $offset
     * @return void
     * @since  5.0.0
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void
    {
        $this->items->offsetUnset($offset);
    }

    /**
     * 统计数据集条数
     * @return int
     */
    public function count(): int
    {
        return $this->items->count();
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * 转换为数组
     * @return array
     */
    public function toArray(): array
    {
        try {
            $total = $this->total();
        } catch (DomainException $e) {
            $total = null;
        }

        return [
            'total' => $total,
            'per_page' => $this->listRows(),
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage,
            'data' => $this->items->toArray(),
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this->items, $name], $arguments);

        if ($result instanceof CollectionInterface) {
            $this->items = $result;
            return $this;
        }

        return $result;
    }
}