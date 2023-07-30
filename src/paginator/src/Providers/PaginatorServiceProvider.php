<?php

declare(strict_types=1);

namespace Larmias\Paginator\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\PaginatorInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Paginator\Paginator;
use Psr\Http\Message\ServerRequestInterface;
use function array_merge;
use function filter_var;
use const FILTER_VALIDATE_INT;

class PaginatorServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     */
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context)
    {
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(PaginatorInterface::class, Paginator::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        Paginator::currentPathResolver(function (string $varPage) {
            if (!$this->context->has(ServerRequestInterface::class)) {
                return 1;
            }
            /** @var ServerRequestInterface $request */
            $request = $this->context->get(ServerRequestInterface::class);
            $inputData = array_merge($request->getQueryParams(), (array)$request->getParsedBody());
            $page = $inputData[$varPage] ?? 1;
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int)$page >= 1) {
                return (int)$page;
            }
            return 1;
        });
    }
}