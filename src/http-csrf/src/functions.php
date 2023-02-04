<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF;

use Larmias\Http\CSRF\Contracts\CsrfManagerInterface;
use Larmias\Utils\ApplicationContext;

/**
 * @return string
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function csrf_token(): string
{
    /** @var CsrfManagerInterface $manager */
    $manager = ApplicationContext::getContainer()->get(CsrfManagerInterface::class);
    return $manager->getToken();
}

/**
 * @return string
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function csrf_token_name(): string
{
    /** @var CsrfManagerInterface $manager */
    $manager = ApplicationContext::getContainer()->get(CsrfManagerInterface::class);
    return $manager->getTokenName();
}

/**
 * @return string
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function csrf_field(): string
{
    return \sprintf('<input type="hidden" name="%s" value="%s"/>', csrf_token_name(), csrf_token());
}