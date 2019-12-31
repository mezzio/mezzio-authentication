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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PdoDatabaseTest extends TestCase
{
    protected function setUp()
    {
        $this->userFactory = function ($identity, $roles, $details) {
            return new DefaultUser($identity, $roles, $details);
        };
    }

    public function testConstructor()
    {
        $pdoDatabase = new PdoDatabase(
            new PDO('sqlite::memory:'),
            [],
            $this->userFactory
        );
        $this->assertInstanceOf(UserRepositoryInterface::class, $pdoDatabase);
    }

    public function getConfig()
    {
        return [
            'table' => 'user',
            'field' => [
                'identity' => 'username',
                'password' => 'password',
            ]
        ];
    }

    public function testAuthenticate()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase(
            $pdo,
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
    }

    public function testAuthenticationError()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $config = $this->getConfig();
        $config['field']['identity'] = 'foo'; // mistake in the configuration

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );
        $this->expectException(RuntimeException::class);
        $user = $pdoDatabase->authenticate('test', 'password');
    }

    public function testAuthenticateInvalidUserPassword()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase(
            $pdo,
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('test', 'foo');
        $this->assertNull($user);
    }

    public function testAuthenticateInvalidUsername()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase(
            $pdo,
            $this->getConfig(),
            $this->userFactory
        );

        $user = $pdoDatabase->authenticate('invalidusername', 'password');
        $this->assertNull($user);
    }

    public function testAuthenticateWithRole()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config = $this->getConfig();
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

    public function testAuthenticateWithRoles()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user_role WHERE username = :identity';


        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );
        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(['user', 'admin'], $user->getRoles());
    }

    public function testAuthenticateWithDetails()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config = $this->getConfig();
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

    public function testAuthenticateWithRolesAndDetails()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user_role WHERE username = :identity';
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

    public function testAuthenticateWithRoleRuntimeError()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config = $this->getConfig();
        // add a mistake in the configuration
        $config['sql_get_roles'] = 'SELECT role FROM user WHERE foo = :identity';
        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $this->expectException(RuntimeException::class);
        $user = $pdoDatabase->authenticate('test', 'password');
    }

    public function testAuthenticateWithEmptySql()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );
        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
    }

    public function testAuthenticateWithNoIdentityParam()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user_role';

        $pdoDatabase = new PdoDatabase(
            $pdo,
            $config,
            $this->userFactory
        );

        $this->expectException(InvalidConfigException::class);
        $user = $pdoDatabase->authenticate('test', 'password');
    }
}
