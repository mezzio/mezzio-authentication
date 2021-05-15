<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use ArrayAccess;
use ArrayObject;
use Generator;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\Htpasswd;
use Mezzio\Authentication\UserRepository\HtpasswdFactory;
use MezzioTest\Authentication\UserRepository\HtpasswdFactoryTest\ConfigImplementingArrayAccess;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class HtpasswdFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @psalm-var ObjectProphecy<ContainerInterface> */
    private $container;

    /** @psalm-var ObjectProphecy<UserInterface> */
    private $user;

    /** @var HtpasswdFactory */
    private $factory;

    /**
     * @psalm-return Generator<string,array{0:mixed,1:non-empty-string}>
     */
    public function validConfigs(): Generator
    {
        $filename = __DIR__ . '/../TestAssets/htpasswd';
        $config   = [
            'authentication' => [
                'htpasswd' => $filename,
            ],
        ];

        yield 'array' => [
            $config,
            $filename,
        ];

        yield ArrayObject::class => [
            new ArrayObject($config),
            $filename,
        ];

        yield ArrayAccess::class => [
            new ConfigImplementingArrayAccess($config),
            $filename,
        ];
    }

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->user      = $this->prophesize(UserInterface::class);
        $this->factory   = new HtpasswdFactory();
    }

    public function testInvokeWithMissingConfig(): void
    {
        // We cannot throw a ContainerExceptionInterface directly; this
        // approach simply mimics `get()` throwing _any_ exception, which is
        // what will happen if `config` is not defined.
        $this->container->get('config')->willThrow(new InvalidConfigException());

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithEmptyConfig(): void
    {
        $this->container->get('config')->willReturn([]);
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithInvalidConfig(): void
    {
        $this->container->get('config')->willReturn([
            'authentication' => [
                'htpasswd' => 'foo',
            ],
        ]);
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
    }

    /**
     * @psalm-param mixed $validConfig
     * @psalm-param non-empty-string $filename
     * @dataProvider validConfigs
     */
    public function testInvokeWithValidConfig($validConfig, string $filename): void
    {
        $this->container->get('config')->willReturn($validConfig);
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );

        $htpasswd = ($this->factory)($this->container->reveal());
        $this->assertEquals(new Htpasswd(
            $filename,
            function () {
                return $this->user->reveal();
            }
        ), $htpasswd);
    }
}
