<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface CollectionInterface
{
    /**
     * @param null|iterable<TKey,TValue>|Jsonable|JsonSerializable $items
     * @return static<TKey, TValue>
     */
    public function fill($items = []);

    /**
     * Wrap the given value in a collection if applicable.
     *
     * @template TWrapKey of array-key
     * @template TWrapValue
     *
     * @param iterable<TWrapKey, TWrapValue> $value
     * @return static<TWrapKey, TWrapValue>
     */
    public static function wrap($value): self;

    /**
     * Get the underlying items from the given collection if applicable.
     *
     * @template TUnwrapKey of array-key
     * @template TUnwrapValue
     *
     * @param array<TUnwrapKey, TUnwrapValue>|static<TUnwrapKey, TUnwrapValue> $value
     * @return array<TUnwrapKey, TUnwrapValue>
     */
    public static function unwrap($value): array;

    /**
     * Create a new collection by invoking the callback a given amount of times.
     *
     * @template TTimesValue
     *
     * @param (callable(int): TTimesValue)|null $callback
     * @return static<int, TTimesValue>
     */
    public static function times(int $number, callable $callback = null): self;

    /**
     * Get all of the items in the collection.
     *
     * @return array<TKey, TValue>
     */
    public function all(): array;

    /**
     * Get the average value of a given key.
     *
     * @param (callable(TValue): float|int)|string|null $callback
     */
    public function avg($callback = null);

    /**
     * Alias for the "avg" method.
     *
     * @param (callable(TValue): float|int)|string|null $callback
     * @return null|float|int
     */
    public function average($callback = null);

    /**
     * Get the median of a given key.
     *
     * @param null|array<array-key, string>|string $key
     */
    public function median($key = null);

    /**
     * Get the mode of a given key.
     *
     * @param null|array<array-key, string>|string $key
     * @return null|array<int, float|int>
     */
    public function mode($key = null);

    /**
     * Collapse the collection of items into a single array.
     *
     * @return static<int, mixed>
     */
    public function collapse(): self;

    /**
     * Determine if an item exists in the collection.
     *
     * @param null|mixed $operator
     * @param null|mixed $value
     * @param (callable(TValue): bool)|TValue|string $key
     */
    public function contains($key, $operator = null, $value = null): bool;

    /**
     * Determine if an item exists in the collection using strict comparison.
     *
     * @param null|TValue $value
     * @param callable|TKey|TValue $key
     */
    public function containsStrict($key, $value = null): bool;

    /**
     * Cross join with the given lists, returning all possible permutations.
     */
    public function crossJoin(...$lists): CollectionInterface;

    /**
     * Dump the collection and end the script.
     */
    public function dd(...$args): void;

    /**
     * Dump the collection.
     */
    public function dump(): CollectionInterface;

    /**
     * Get the items in the collection that are not present in the given items.
     *
     * @param Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
     * @return static<TKey, TValue>
     */
    public function diff($items): CollectionInterface;

    /**
     * Get the items in the collection that are not present in the given items.
     *
     * @param Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
     * @param callable(TValue): int $callback
     * @return static<TKey, TValue>
     */
    public function diffUsing($items, callable $callback): CollectionInterface;

    /**
     * Get the items in the collection whose keys and values are not present in the given items.
     *
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function diffAssoc($items): CollectionInterface;

    /**
     * Get the items in the collection whose keys and values are not present in the given items.
     *
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @param callable(TKey): int $callback
     * @return static<TKey, TValue>
     */
    public function diffAssocUsing($items, callable $callback): CollectionInterface;

    /**
     * Get the items in the collection whose keys are not present in the given items.
     *
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function diffKeys($items): CollectionInterface;

    /**
     * Get the items in the collection whose keys are not present in the given items.
     *
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @param callable(TKey): int $callback
     * @return static<TKey, TValue>
     */
    public function diffKeysUsing($items, callable $callback): self;

    /**
     * Execute a callback over each item.
     * @param callable(TValue,TKey): mixed $callback
     */
    public function each(callable $callback): CollectionInterface;

    /**
     * Execute a callback over each nested chunk of items.
     * @param callable(...mixed): mixed  $callback
     * @return static<TKey, TValue>
     */
    public function eachSpread(callable $callback): CollectionInterface;

    /**
     * Determine if all items in the collection pass the given test.
     *
     * @param (callable(TValue, TKey): bool)|TValue|string $key
     * @param mixed $operator
     * @param mixed $value
     */
    public function every($key, $operator = null, $value = null): bool;

    /**
     * Get all items except for those with the specified keys.
     *
     * @param array<array-key, TKey>|static<array-key, TKey> $keys
     * @return static<TKey, TValue>
     */
    public function except($keys): CollectionInterface;

    /**
     * Run a filter over each of the items.
     *
     * @param callable(TValue, TKey): bool|null $callback
     * @return static<TKey, TValue>
     */
    public function filter(callable $callback = null): CollectionInterface;

    /**
     * Apply the callback if the value is truthy.
     *
     * @param callable($this): $this $callback
     * @param callable($this): $this $default
     * @return CollectionInterface
     */
    public function when(bool $value, callable $callback, callable $default = null): self;

    /**
     * Apply the callback if the value is falsy.
     *
     * @param callable($this): $this $callback
     * @param callable($this): $this|null $default
     * @return CollectionInterface
     */
    public function unless(bool $value, callable $callback, callable $default = null): CollectionInterface;

    /**
     * Filter items by the given key value pair.
     *
     * @param mixed $operator
     * @param mixed $value
     * @return static<TKey, TValue>
     */
    public function where(string $key, $operator = null, $value = null): CollectionInterface;

    /**
     * Filter items by the given key value pair using strict comparison.
     *
     * @param mixed $value
     * @return static<TKey, TValue>
     */
    public function whereStrict(string $key, $value): CollectionInterface;

    /**
     * Filter items by the given key value pair.
     *
     * @param Arrayable|iterable $values
     * @return static<TKey, TValue>
     */
    public function whereIn(string $key, $values, bool $strict = false): CollectionInterface;

    /**
     * Filter items by the given key value pair using strict comparison.
     *
     * @param Arrayable|iterable $values
     * @return static<TKey, TValue>
     */
    public function whereInStrict(string $key, $values): CollectionInterface;

    /**
     * Filter items by the given key value pair.
     *
     * @param Arrayable|iterable $values
     * @return static<TKey, TValue>
     */
    public function whereNotIn(string $key, $values, bool $strict = false): CollectionInterface;

    /**
     * Filter items by the given key value pair using strict comparison.
     *
     * @param Arrayable|iterable $values
     * @return static<TKey, TValue>
     */
    public function whereNotInStrict(string $key, $values): CollectionInterface;

    /**
     * Filter the items, removing any items that don't match the given type.
     *
     * @param class-string $type
     * @return static<TKey, TValue>
     */
    public function whereInstanceOf(string $type): CollectionInterface;

    /**
     * Get the first item from the collection.
     *
     * @template TFirstDefault
     *
     * @param callable(TValue, TKey): bool|null $callback
     * @param TFirstDefault|callable(): TFirstDefault $default
     * @return TFirstDefault|TValue
     */
    public function first(callable $callback = null, $default = null);

    /**
     * Get the first item by the given key value pair.
     *
     * @param mixed $operator
     * @param mixed $value
     * @return null|TValue
     */
    public function firstWhere(string $key, $operator, $value = null);

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param float|int $depth
     * @return static<int, mixed>
     */
    public function flatten($depth = INF): CollectionInterface;

    /**
     * Flip the items in the collection.
     *
     * @return static<TKey, TValue>
     */
    public function flip(): self;

    /**
     * Remove an item from the collection by key.
     *
     * @param array<array-key, TKey>|TKey $keys
     * @return $this
     */
    public function forget($keys): self;

    /**
     * Get an item from the collection by key.
     *
     * @template TGetDefault
     *
     * @param TKey $key
     * @param TGetDefault|(\Closure(): TGetDefault) $default
     * @return TGetDefault|TValue
     */
    public function get($key, $default = null);

    /**
     * Group an associative array by a field or using a callback.
     * @param mixed $groupBy
     */
    public function groupBy($groupBy, bool $preserveKeys = false): CollectionInterface;

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param (callable(TValue, TKey): array-key)|array|string $keyBy
     * @return static<TKey, array<TKey, TValue>>
     */
    public function keyBy($keyBy): CollectionInterface;

    /**
     * Determine if an item exists in the collection by key.
     * @param array<array-key, TKey>|TKey $key
     */
    public function has($key): bool;

    /**
     * Concatenate values of a given key as a string.
     */
    public function implode(string $value, string $glue = null): string;

    /**
     * Intersect the collection with the given items.
     *
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function intersect($items): CollectionInterface;

    /**
     * Intersect the collection with the given items by key.
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function intersectByKeys($items): CollectionInterface;

    /**
     * Determine if the collection is empty or not.
     */
    public function isEmpty(): bool;

    /**
     * Determine if the collection is not empty.
     */
    public function isNotEmpty(): bool;

    /**
     * Get the keys of the collection items.
     * @return static<int, TKey>
     */
    public function keys(): CollectionInterface;

    /**
     * Get the last item from the collection.
     *
     * @template TLastDefault
     *
     * @param (callable(TValue, TKey): bool)|null $callback
     * @param TLastDefault|(\Closure(): TLastDefault) $default
     * @return TLastDefault|TValue
     */
    public function last(callable $callback = null, $default = null);

    /**
     * Get the values of a given key.
     *
     * @param array<array-key, string>|string $value
     * @return static<int, mixed>
     */
    public function pluck($value, ?string $key = null): CollectionInterface;

    /**
     * Run a map over each of the items.
     *
     * @template TMapValue
     *
     * @param callable(TValue, TKey): TMapValue $callback
     * @return static<TKey, TMapValue>
     */
    public function map(callable $callback): CollectionInterface;

    /**
     * Run a map over each nested chunk of items.
     *
     * @template TMapSpreadValue
     *
     * @param callable(mixed): TMapSpreadValue $callback
     * @return static<TKey, TMapSpreadValue>
     */
    public function mapSpread(callable $callback): CollectionInterface;

    /**
     * Run a dictionary map over the items.
     * The callback should return an associative array with a single key/value pair.
     *
     * @template TMapToDictionaryKey of array-key
     * @template TMapToDictionaryValue
     *
     * @param callable(TValue, TKey): array<TMapToDictionaryKey, TMapToDictionaryValue> $callback
     * @return static<TMapToDictionaryKey, array<int, TMapToDictionaryValue>>
     */
    public function mapToDictionary(callable $callback): CollectionInterface;

    /**
     * Run a grouping map over the items.
     * The callback should return an associative array with a single key/value pair.
     */
    public function mapToGroups(callable $callback): CollectionInterface;

    /**
     * Run an associative map over each of the items.
     * The callback should return an associative array with a single key/value pair.
     *
     * @template TMapWithKeysKey of array-key
     * @template TMapWithKeysValue
     *
     * @param callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue> $callback
     * @return static<TMapWithKeysKey, TMapWithKeysValue>
     */
    public function mapWithKeys(callable $callback): CollectionInterface;

    /**
     * Map a collection and flatten the result by a single level.
     *
     * @param callable(TValue, TKey): mixed $callback
     * @return static<int, mixed>
     */
    public function flatMap(callable $callback): CollectionInterface;

    /**
     * Map the values into a new class.
     *
     * @param class-string $class
     * @return static<TKey, mixed>
     */
    public function mapInto(string $class): CollectionInterface;

    /**
     * Get the max value of a given key.
     *
     * @param (callable(TValue):mixed)|string|null $callback
     * @return TValue
     */
    public function max($callback = null);

    /**
     * Merge the collection with the given items.
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function merge($items): CollectionInterface;

    /**
     * Create a collection by using this collection for keys and another for its values.
     *
     * @template TCombineValue
     *
     * @param Arrayable<array-key, TCombineValue>|iterable<array-key, TCombineValue> $values
     * @return static<TKey, TCombineValue>
     */
    public function combine($values): CollectionInterface;

    /**
     * Union the collection with the given items.
     *
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function union($items): CollectionInterface;

    /**
     * Get the min value of a given key.
     *
     * @param (callable(TValue):mixed)|string|null $callback
     * @return TValue
     */
    public function min($callback = null);

    /**
     * Create a new collection consisting of every n-th element.
     *
     * @return static<TKey, TValue>
     */
    public function nth(int $step, int $offset = 0): CollectionInterface;

    /**
     * Get the items with the specified keys.
     *
     * @param null|array<array-key, TKey>|static<array-key, TKey>|string $keys
     * @return static<TKey, TValue>
     */
    public function only($keys): CollectionInterface;

    /**
     * "Paginate" the collection by slicing it into a smaller collection.
     */
    public function forPage(int $page, int $perPage): CollectionInterface;

    /**
     * Partition the collection into two arrays using the given callback or key.
     *
     * @param callable(TValue, TKey) bool)|TValue|string  $key
     * @param null|string|TValue $operator
     * @param null|TValue $value
     * @return static<int, static<TKey, TValue>>
     */
    public function partition($key, $operator = null, $value = null): CollectionInterface;

    /**
     * Pass the collection to the given callback and return the result.
     *
     * @template TPipeReturnType
     *
     * @param callable($this): TPipeReturnType $callback
     * @return TPipeReturnType
     */
    public function pipe(callable $callback);

    /**
     * Get and remove the last item from the collection.
     */
    public function pop();

    /**
     * Push an item onto the beginning of the collection.
     *
     * @param TValue $value
     * @param null|TKey $key
     * @return CollectionInterface
     */
    public function prepend($value, $key = null): CollectionInterface;

    /**
     * Push an item onto the end of the collection.
     *
     * @param TValue $value
     * @return CollectionInterface
     */
    public function push($value): CollectionInterface;

    /**
     * Push all of the given items onto the collection.
     *
     * @param iterable<array-key, TValue> $source
     * @return static<TKey, TValue>
     */
    public function concat($source): CollectionInterface;

    /**
     * Get and remove an item from the collection.
     *
     * @template TPullDefault
     *
     * @param TKey $key
     * @param TPullDefault|(\Closure(): TPullDefault) $default
     * @return TPullDefault|TValue
     */
    public function pull($key, $default = null);

    /**
     * Put an item in the collection by key.
     *
     * @param TKey $key
     * @param TValue $value
     * @return CollectionInterface
     */
    public function put($key, $value): CollectionInterface;

    /**
     * Get one or a specified number of items randomly from the collection.
     *
     * @return static<int, TValue>|TValue
     * @throws \InvalidArgumentException
     */
    public function random(int $number = null);

    /**
     * Reduce the collection to a single value.
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param callable(TReduceInitial|TReduceReturnType, TValue): TReduceReturnType $callback
     * @param TReduceInitial $initial
     * @return TReduceInitial|TReduceReturnType
     */
    public function reduce(callable $callback, $initial = null);

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param callable(TValue, TKey): bool|bool $callback
     * @return static<TKey, TValue>
     */
    public function reject($callback): CollectionInterface;

    /**
     * Reverse items order.
     *
     * @return static<TKey, TValue>
     */
    public function reverse(): CollectionInterface;

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     *
     * @param TValue|(callable(TValue,TKey): bool) $value
     * @return bool|TKey
     */
    public function search($value, bool $strict = false);

    /**
     * Get and remove the first item from the collection.
     *
     * @return null|TValue
     */
    public function shift();

    /**
     * Shuffle the items in the collection.
     *
     * @return static<TKey, TValue>
     */
    public function shuffle(int $seed = null): CollectionInterface;

    /**
     * Slice the underlying collection array.
     *
     * @return static<TKey, TValue>
     */
    public function slice(int $offset, int $length = null): CollectionInterface;

    /**
     * Split a collection into a certain number of groups.
     *
     * @return static<int, static<TKey, TValue>>
     */
    public function split(int $numberOfGroups): CollectionInterface;

    /**
     * Chunk the underlying collection array.
     *
     * @return static<int, static<TKey, TValue>>
     */
    public function chunk(int $size): CollectionInterface;

    /**
     * Sort through each item with a callback.
     *
     * @param callable(TValue, TValue): int $callback
     * @return static<TKey, TValue>
     */
    public function sort(callable $callback = null): CollectionInterface;

    /**
     * Sort the collection using the given callback.
     *
     * @param (callable(TValue, TKey): mixed)|string|array $callback
     * @return static<TKey, TValue>
     */
    public function sortBy($callback, int $options = SORT_REGULAR, bool $descending = false): CollectionInterface;

    /**
     * Sort the collection in descending order using the given callback.
     *
     * @param (callable(TValue, TKey): mixed)|string $callback
     * @return static<TKey, TValue>
     */
    public function sortByDesc($callback, int $options = SORT_REGULAR): CollectionInterface;

    /**
     * Sort the collection keys.
     *
     * @return static<TKey, TValue>
     */
    public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): CollectionInterface;

    /**
     * Sort the collection keys in descending order.
     *
     * @return static<TKey, TValue>
     */
    public function sortKeysDesc(int $options = SORT_REGULAR): CollectionInterface;

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param array<array-key, TValue> $replacement
     * @return static<TKey, TValue>
     */
    public function splice(int $offset, int $length = null, $replacement = []): CollectionInterface;

    /**
     * Get the sum of the given values.
     *
     * @param (callable(TValue): mixed)|string|null $callback
     * @return mixed
     */
    public function sum($callback = null): mixed;

    /**
     * Take the first or last {$limit} items.
     *
     * @return static<TKey, TValue>
     */
    public function take(int $limit): CollectionInterface;

    /**
     * Pass the collection to the given callback and then return it.
     *
     * @param callable(static<TKey,TValue>): mixed $callback
     * @return CollectionInterface
     */
    public function tap(callable $callback): CollectionInterface;

    /**
     * Transform each item in the collection using a callback.
     *
     * @param callable(TValue, TKey): TValue $callback
     * @return CollectionInterface
     */
    public function transform(callable $callback): CollectionInterface;

    /**
     * Return only unique items from the collection array.
     *
     * @param (callable(TValue, TKey): bool)|string|null $key
     * @return static<TKey, TValue>
     */
    public function unique($key = null, bool $strict = false): CollectionInterface;

    /**
     * Return only unique items from the collection array using strict comparison.
     *
     * @param (callable(TValue, TKey): bool)|string|null $key
     * @return static<TKey, TValue>
     */
    public function uniqueStrict($key = null): CollectionInterface;

    /**
     * Reset the keys on the underlying array.
     *
     * @return static<TKey, TValue>
     */
    public function values(): CollectionInterface;

    /**
     * Zip the collection together with one or more arrays.
     * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
     *      => [[1, 4], [2, 5], [3, 6]].
     *
     * @template TZipValue
     *
     * @param Arrayable<array-key, TZipValue>|iterable<array-key, TZipValue> ...$items
     * @return static<int, static<int, TValue|TZipValue>>
     */
    public function zip($items): CollectionInterface;

    /**
     * Pad collection to the specified length with a value.
     *
     * @template TPadValue
     *
     * @param TPadValue $value
     * @return static<int, TPadValue|TValue>
     */
    public function pad(int $size, $value): CollectionInterface;

    /**
     * Get the collection of items as a plain array.
     *
     * @return array<TKey, mixed>
     */
    public function toArray(): array;

    /**
     * Get the collection of items as JSON.
     */
    public function toJson(int $options = 0): string;

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator<TKey, TValue>
     */
    public function getIterator(): \ArrayIterator;

    /**
     * Get a CachingIterator instance.
     */
    public function getCachingIterator(int $flags = CachingIterator::CALL_TOSTRING): \CachingIterator;

    /**
     * Count the number of items in the collection.
     */
    public function count(): int;
}