<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF;

use Larmias\Http\CSRF\Contracts\CsrfManagerInterface;
use Larmias\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function sprintf;

/**
 * @return string
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function csrf_token(): string
{
    /** @var CsrfManagerInterface $manager */
    $manager = ApplicationContext::getContainer()->get(CsrfManagerInterface::class);
    return $manager->getToken();
}

/**
 * @return string
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function csrf_token_name(): string
{
    /** @var CsrfManagerInterface $manager */
    $manager = ApplicationContext::getContainer()->get(CsrfManagerInterface::class);
    return $manager->getTokenName();
}

/**
 * @return string
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function csrf_field(): string
{
    return sprintf('<input type="hidden" name="%s" value="%s"/>', csrf_token_name(), csrf_token());
}