<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\PdoDatabase;
use Mezzio\Authentication\UserRepository\PdoDatabaseFactory;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function array_key_exists;

/** @covers \Mezzio\Authentication\UserRepository\PdoDatabaseFactory */
final class PdoDatabaseFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    /** @var UserInterface&MockObject */
    private UserInterface $user;

    /** @var PDO&MockObject */
    private PDO $pdo;

    private PdoDatabaseFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->user      = $this->createMock(UserInterface::class);
        $this->pdo       = $this->createMock(PDO::class);

        $this->factory = new PdoDatabaseFactory();
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
        $this->expectExceptionMessage('PDO values are missing in authentication config');

        ($this->factory)($this->container);
    }

    /**
     * @psalm-return list<list<array<string, array<string, string>|string>>>
     */
    public function getPdoInvalidConfig(): array
    {
        return [
            [[]],
            [
                [
                    'service' => PDO::class,
                ],
            ],
            [
                [
                    'service' => PDO::class,
                    'table'   => 'test',
                ],
            ],
            [
                [
                    'service' => PDO::class,
                    'table'   => 'test',
                    'field'   => [],
                ],
            ],
            [
                [
                    'service' => PDO::class,
                    'table'   => 'test',
                    'field'   => [
                        'identity' => 'email',
                    ],
                ],
            ],
            [
                [
                    'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
                ],
            ],
            [
                [
                    'dsn'   => 'mysql:dbname=testdb;host=127.0.0.1',
                    'table' => 'test',
                ],
            ],
            [
                [
                    'dsn'   => 'mysql:dbname=testdb;host=127.0.0.1',
                    'table' => 'test',
                    'field' => [],
                ],
            ],
            [
                [
                    'dsn'   => 'mysql:dbname=testdb;host=127.0.0.1',
                    'table' => 'test',
                    'field' => [
                        'identity' => 'email',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string,mixed> $pdoConfig
     * @dataProvider getPdoInvalidConfig
     */
    public function testInvokeWithInvalidConfig(array $pdoConfig): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->pdo
            ->expects(self::never())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $this->container
            ->expects(self::never())
            ->method('has')
            ->with(PDO::class);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['authentication' => ['pdo' => $pdoConfig]]);

        $this->expectException(InvalidConfigException::class);

        ($this->factory)($this->container);
    }

    /**
     * @psalm-return list<list<array<string, mixed>>>
     */
    public function getPdoValidConfig(): array
    {
        return [
            [
                [
                    'service' => PDO::class,
                    'table'   => 'user',
                    'field'   => [
                        'identity' => 'username',
                        'password' => 'password',
                    ],
                ],
            ],
            [
                [
                    'dsn'   => 'sqlite:' . __DIR__ . '/../TestAssets/pdo.sqlite',
                    'table' => 'user',
                    'field' => [
                        'identity' => 'username',
                        'password' => 'password',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getPdoValidConfig
     * @psalm-param array<string, mixed> $pdoConfig
     */
    public function testInvokeWithValidConfig(array $pdoConfig): void
    {
        $this->pdo
            ->expects(self::never())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $this->container
            ->expects(array_key_exists('dsn', $pdoConfig) ? self::never() : self::once())
            ->method('has')
            ->with(PDO::class)
            ->willReturn(true);

        $this->container
            ->expects(self::exactly(array_key_exists('dsn', $pdoConfig) ? 2 : 3))
            ->method('get')
            ->withConsecutive(
                ['config'],
                [UserInterface::class],
                [PDO::class],
            )
            ->willReturn(
                ['authentication' => ['pdo' => $pdoConfig]],
                fn (): UserInterface => $this->user,
                $this->pdo,
            );

        $pdoDatabase = ($this->factory)($this->container);

        self::assertEquals(new PdoDatabase(
            array_key_exists('dsn', $pdoConfig) ? new PDO((string) $pdoConfig['dsn']) : $this->pdo,
            $pdoConfig,
            fn (): UserInterface => $this->user
        ), $pdoDatabase);
    }
}
