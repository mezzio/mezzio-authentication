<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Closure;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\PdoDatabaseFactory;
use MezzioTest\Authentication\InMemoryContainer;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use ReflectionProperty;

use function assert;

#[CoversClass(PdoDatabaseFactory::class)]
final class PdoDatabaseFactoryTest extends TestCase
{
    private InMemoryContainer $container;

    /** @var Closure(): UserInterface */
    private Closure $user;

    /** @var PDO&MockObject */
    private PDO $pdo;

    private PdoDatabaseFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new InMemoryContainer();
        $user            = $this->createMock(UserInterface::class);
        $this->user      = static function () use ($user): UserInterface {
            assert($user instanceof UserInterface);

            return $user;
        };

        $this->pdo = $this->createMock(PDO::class);

        $this->factory = new PdoDatabaseFactory();
    }

    public function testInvokeWithMissingConfig(): void
    {
        $this->expectException(ContainerExceptionInterface::class);

        ($this->factory)($this->container);
    }

    public function testInvokeWithEmptyConfig(): void
    {
        $this->container->set('config', []);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('PDO values are missing in authentication config');

        ($this->factory)($this->container);
    }

    /**
     * @psalm-return list<array{0: array<string, array<string, string>|string>}>
     */
    public static function getPdoInvalidConfig(): array
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
            [
                [
                    'service' => 'No service by this name',
                    'table'   => 'test',
                    'field'   => [
                        'identity' => 'email',
                        'password' => 'password',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string,mixed> $pdoConfig
     */
    #[DataProvider('getPdoInvalidConfig')]
    public function testInvokeWithInvalidConfig(array $pdoConfig): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->pdo
            ->expects(self::never())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $this->container->set('config', ['authentication' => ['pdo' => $pdoConfig]]);
        $this->container->set(UserInterface::class, $this->user);

        $this->expectException(InvalidConfigException::class);

        ($this->factory)($this->container);
    }

    /**
     * @psalm-return list<array{0: array<string, mixed>}>
     */
    public static function getPdoValidConfig(): array
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
     * @psalm-param array<string, mixed> $pdoConfig
     */
    #[DataProvider('getPdoValidConfig')]
    public function testInvokeWithValidConfig(array $pdoConfig): void
    {
        $this->pdo
            ->expects(self::never())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $this->container->set('config', ['authentication' => ['pdo' => $pdoConfig]]);
        $this->container->set(UserInterface::class, $this->user);
        $this->container->set(PDO::class, $this->pdo);

        $pdoDatabase = ($this->factory)($this->container);

        $prop = new ReflectionProperty($pdoDatabase, 'pdo');

        if (isset($pdoConfig['dsn'])) {
            self::assertNotSame($this->pdo, $prop->getValue($pdoDatabase));
        } else {
            self::assertSame($this->pdo, $prop->getValue($pdoDatabase));
        }
    }
}
