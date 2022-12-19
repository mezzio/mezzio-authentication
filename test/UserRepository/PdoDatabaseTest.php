<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Closure;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\Exception\RuntimeException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\PdoDatabase;
use Mezzio\Authentication\UserRepositoryInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

/** @covers \Mezzio\Authentication\UserRepository\PdoDatabase */
final class PdoDatabaseTest extends TestCase
{
    /** @psalm-var Closure(string, array<int|string, string>, array<string, mixed>): UserInterface */
    private Closure $userFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userFactory = static function (string $identity, array $roles, array $details): UserInterface {
            Assert::allString($roles);
            Assert::isMap($details);

            return new DefaultUser($identity, $roles, $details);
        };
    }

    public function testConstructor(): void
    {
        $pdoDatabase = new PdoDatabase(
            new PDO('sqlite::memory:'),
            [],
            $this->userFactory
        );

        self::assertInstanceOf(UserRepositoryInterface::class, $pdoDatabase);
    }

    /**
     * @psalm-return array{table: string, field: array{identity: string, password: string}}
     */
    public function getConfig(): array
    {
        return [
            'table' => 'user',
            'field' => [
                'identity' => 'username',
                'password' => 'password',
            ],
        ];
    }

    public function testAuthenticate(): void
    {
        $pdo         = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase(
            $pdo,
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('test', 'password');

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertSame('test', $user->getIdentity());
    }

    public function testAuthenticationError(): void
    {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo.sqlite');
        /** @deprecated {@see https://wiki.php.net/rfc/pdo_default_errmode} */
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $config                      = $this->getConfig();
        $config['field']['identity'] = 'foo'; // mistake in the configuration

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $this->expectException(RuntimeException::class);

        $pdoDatabase->authenticate('test', 'password');
    }

    public function testAuthenticateInvalidUserPassword(): void
    {
        $pdo         = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase(
            $pdo,
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('test', 'foo');

        self::assertNull($user);
    }

    public function testAuthenticateInvalidUsername(): void
    {
        $pdo         = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase(
            $pdo,
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('invalidusername', 'password');

        self::assertNull($user);
    }

    public function testAuthenticateWithRole(): void
    {
        $pdo                     = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config                  = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user WHERE username = :identity';

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('test', 'password');

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertSame(['admin'], $user->getRoles());
    }

    public function testAuthenticateWithRoles(): void
    {
        $pdo                     = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config                  = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user_role WHERE username = :identity';

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );
        $user        = $pdoDatabase->authenticate('test', 'password');

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertSame(['user', 'admin'], $user->getRoles());
    }

    public function testAuthenticateWithDetails(): void
    {
        $pdo                       = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config                    = $this->getConfig();
        $config['sql_get_details'] = 'SELECT email FROM user WHERE username = :identity';

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('test', 'password');

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertSame(['email' => 'test@foo.com'], $user->getDetails());
        self::assertSame('test@foo.com', $user->getDetail('email'));
    }

    public function testAuthenticateWithRolesAndDetails(): void
    {
        $pdo                       = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config                    = $this->getConfig();
        $config['sql_get_roles']   = 'SELECT role FROM user_role WHERE username = :identity';
        $config['sql_get_details'] = 'SELECT email FROM user WHERE username = :identity';

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('test', 'password');

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertSame(['email' => 'test@foo.com'], $user->getDetails());
        self::assertSame('test@foo.com', $user->getDetail('email'));
        self::assertSame(['user', 'admin'], $user->getRoles());
    }

    public function testAuthenticateWithRoleRuntimeError(): void
    {
        $pdo    = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config = $this->getConfig();
        // add a mistake in the configuration
        $config['sql_get_roles'] = 'SELECT role FROM user WHERE foo = :identity';
        $pdoDatabase             = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $this->expectException(RuntimeException::class);

        $pdoDatabase->authenticate('test', 'password');
    }

    public function testAuthenticateWithEmptySql(): void
    {
        $pdo    = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );
        $user        = $pdoDatabase->authenticate('test', 'password');

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertSame('test', $user->getIdentity());
    }

    public function testAuthenticateWithNoIdentityParam(): void
    {
        $pdo                     = new PDO('sqlite:' . __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config                  = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user_role';

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $this->expectException(InvalidConfigException::class);

        $pdoDatabase->authenticate('test', 'password');
    }

    /**
     * @psalm-return list<array{0: string|null}>
     */
    public function getVoidPasswords(): array
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @dataProvider getVoidPasswords
     */
    public function testHandlesNullOrEmptyPassword(?string $password): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt
            ->expects(self::once())
            ->method('bindParam')
            ->withAnyParameters()
            ->willReturn(true);

        $stmt
            ->expects(self::once())
            ->method('execute')
            ->withAnyParameters()
            ->willReturn(true);

        $stmt
            ->expects(self::once())
            ->method('fetchObject')
            ->willReturn((object) ['password' => $password]);

        $pdo = $this->createMock(PDO::class);

        $pdo
            ->expects(self::once())
            ->method('prepare')
            ->withAnyParameters()
            ->willReturn($stmt);

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('null', $password);

        self::assertNull($user);
    }
}
