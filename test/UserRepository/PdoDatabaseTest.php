<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\Exception\RuntimeException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\PdoDatabase;
use Mezzio\Authentication\UserRepositoryInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Webmozart\Assert\Assert;

class PdoDatabaseTest extends TestCase
{
    use ProphecyTrait;

    /** @psalm-var callable(string, array<int|string, string>, array<string, mixed>): UserInterface */
    private $userFactory;

    protected function setUp(): void
    {
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
        $this->assertInstanceOf(UserRepositoryInterface::class, $pdoDatabase);
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
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
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
        $this->assertNull($user);
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
        $this->assertNull($user);
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
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(['admin'], $user->getRoles());
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
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(['user', 'admin'], $user->getRoles());
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
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(['email' => 'test@foo.com'], $user->getDetails());
        $this->assertEquals('test@foo.com', $user->getDetail('email'));
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
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(['email' => 'test@foo.com'], $user->getDetails());
        $this->assertEquals('test@foo.com', $user->getDetail('email'));
        $this->assertEquals(['user', 'admin'], $user->getRoles());
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
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
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
     * @psalm-return list<list<string|null>>
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
        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->bindParam(Argument::any(), Argument::any())->willReturn();
        $stmt->execute(Argument::any())->willReturn();
        $stmt->fetchObject()->willReturn((object) ['password' => $password]);

        $pdo = $this->prophesize(PDO::class);
        $pdo->prepare(Argument::any())->willReturn($stmt->reveal());

        $pdoDatabase = new PdoDatabase(
            $pdo->reveal(),
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('null', $password);
        $this->assertNull($user);
    }
}
