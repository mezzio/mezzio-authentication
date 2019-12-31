<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication;

use Laminas\Diactoros\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

trait ResponsePrototypeTrait
{
    /**
     * Return a ResponseInterface service if present or Laminas\Diactoros\Response
     *
     * @throws Exception\InvalidConfigException
     */
    protected function getResponsePrototype(ContainerInterface $container) : ResponseInterface
    {
        if (! $container->has(ResponseInterface::class)
            && ! class_exists(Response::class)
        ) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s service; dependency %s is missing. Either define the service, '
                . 'or install laminas/laminas-diactoros',
                static::class,
                ResponseInterface::class
            ));
        }
        return $container->has(ResponseInterface::class)
            ? $container->get(ResponseInterface::class)
            : new Response();
    }
}
