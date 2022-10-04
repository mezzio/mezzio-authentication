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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/** @covers \Mezzio\Authentication\UserRepository\HtpasswdFactory */
final class HtpasswdFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    /** @var UserInterface&MockObject */
    private UserInterface $user;

    private HtpasswdFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->user      = $this->createMock(UserInterface::class);

        $this->factory = new HtpasswdFactory();
    }

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

    public function testInvokeWithMissingConfig(): void
    {
        // We cannot throw a ContainerExceptionInterface directly; this
        // approach simply mimics `get()` throwing _any_ exception, which is
        // what will happen if `config` is not defined.
        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException(new InvalidConfigException());

        $this->expectException(InvalidConfigException::class);

        ($this->factory)($this->container);
    }

    public function testInvokeWithEmptyConfig(): void
    {
        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn([]);

        $this->expectException(InvalidConfigException::class);

        ($this->factory)($this->container);
    }

    public function testInvokeWithInvalidConfig(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                ['config'],
                [UserInterface::class],
            )
            ->willReturn(
                [
                    'authentication' => [
                        'htpasswd' => 'foo',
                    ],
                ],
                fn (): UserInterface => $this->user
            );

                $this->expectException(InvalidConfigException::class);

                ($this->factory)($this->container);
    }

    /**
     * @psalm-param mixed $validConfig
     * @psalm-param non-empty-string $filename
     * @dataProvider validConfigs
     */
    public function testInvokeWithValidConfig($validConfig, string $filename): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                ['config'],
                [UserInterface::class],
            )
            ->willReturn(
                $validConfig,
                fn (): UserInterface => $this->user
            );

        $htpasswd = ($this->factory)($this->container);

        self::assertEquals(
            new Htpasswd($filename, fn (): UserInterface => $this->user),
            $htpasswd
        );
    }
}
