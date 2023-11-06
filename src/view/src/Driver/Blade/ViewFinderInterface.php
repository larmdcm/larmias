<?php

namespace Larmias\View\Driver\Blade;

interface ViewFinderInterface
{
    /**
     * Hint path delimiter value.
     *
     * @var string
     */
    const HINT_PATH_DELIMITER = '::';

    /**
     * Get the fully qualified location of the view.
     *
     * @param string $view
     * @return string
     */
    public function find($view);

    /**
     * Add a location to the finder.
     *
     * @param string $location
     * @return void
     */
    public function addLocation(string $location): void;

    /**
     * Add a namespace hint to the finder.
     *
     * @param string $namespace
     * @param string|array $hints
     * @return void
     */
    public function addNamespace(string $namespace, string|array $hints): void;

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param string $namespace
     * @param string|array $hints
     * @return void
     */
    public function prependNamespace($namespace, $hints);

    /**
     * Add a valid view extension to the finder.
     *
     * @param string $extension
     * @return void
     */
    public function addExtension($extension);
}
