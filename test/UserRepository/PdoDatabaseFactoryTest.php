<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\PdoDatabase;
use Mezzio\Authentication\UserRepository\PdoDatabaseFactory;
use PDO;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

use function array_key_exists;

class PdoDatabaseFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @psalm-var ObjectProphecy<ContainerInterface> */
    private $container;

    /** @psalm-var ObjectProphecy<UserInterface> */
    private $user;

    /** @psalm-var ObjectProphecy<PDO> */
    private $pdo;

    /** @var PdoDatabaseFactory */
    private $factory;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->user      = $this->prophesize(UserInterface::class);
        $this->pdo       = $this->prophesize(PDO::class);
        $this->factory   = new PdoDatabaseFactory();
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

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('PDO values are missing in authentication config');

        ($this->factory)($this->container->reveal());
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

        $this->pdo->getAttribute(PDO::ATTR_ERRMODE)->willReturn(PDO::ERRMODE_SILENT);

        $this->container->get('config')->willReturn([
            'authentication' => ['pdo' => $pdoConfig],
        ]);
        $this->container->has(PDO::class)->willReturn(true);
        $this->container->get(PDO::class)->willReturn($this->pdo->reveal());
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
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
        $this->pdo->getAttribute(PDO::ATTR_ERRMODE)->willReturn(PDO::ERRMODE_SILENT);

        $this->container->get('config')->willReturn([
            'authentication' => ['pdo' => $pdoConfig],
        ]);
        $this->container->has(PDO::class)->willReturn(true);
        $this->container->get(PDO::class)->willReturn($this->pdo->reveal());
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );
        $pdoDatabase = ($this->factory)($this->container->reveal());
        $this->assertEquals(new PdoDatabase(
            array_key_exists('dsn', $pdoConfig) ? new PDO((string) $pdoConfig['dsn']) : $this->pdo->reveal(),
            $pdoConfig,
            function () {
                return $this->user->reveal();
            }
        ), $pdoDatabase);
    }
}
