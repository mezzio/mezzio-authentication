<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Laminas\Diactoros\Response;
use Mezzio\Authentication\ResponsePrototypeTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionObject;

class ResponsePrototypeTraitTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testContainerHasDefinedResponseFactory()
    {
        $this->container->has(ResponseInterface::class)->willReturn(true);
        $this->container->get(ResponseInterface::class)->willReturn(function () {
            return $this->prophesize(ResponseInterface::class)->reveal();
        });

        $response = $this->getResponsePrototype();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotInstanceOf(Response::class, $response);
    }

    public function testContainerDoesNotHaveResponseFactoryButDiactorosIsInstalled()
    {
        $this->container->has(ResponseInterface::class)->willReturn(false);

        $response = $this->getResponsePrototype();
        $this->assertInstanceOf(Response::class, $response);
    }

    private function getResponsePrototype()
    {
        $class = new class () {
            use ResponsePrototypeTrait;
        };

        $r = new ReflectionObject($class);
        $m = $r->getMethod('getResponsePrototype');
        $m->setAccessible(true);

        return $m->invoke($class, $this->container->reveal());
    }
}
