<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\Exception\RuntimeException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\PdoDatabase;
use Mezzio\Authentication\UserRepositoryInterface;
use PDO;
use PHPUnit\Framework\TestCase;

class PdoDatabaseTest extends TestCase
{
    public function testConstructor()
    {
        $pdoDatabase = new PdoDatabase(new PDO('sqlite::memory:'), []);
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
        $pdoDatabase = new PdoDatabase($pdo, $this->getConfig());

        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
    }

    public function testAuthenticationError()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $config = $this->getConfig();
        $config['field']['identity'] = 'foo'; // mistake in the configuration

        $pdoDatabase = new PdoDatabase($pdo, $config);
        $this->expectException(RuntimeException::class);
        $user = $pdoDatabase->authenticate('test', 'password');
    }

    public function testAuthenticateInvalidUserPassword()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase($pdo, $this->getConfig());

        $user = $pdoDatabase->authenticate('test', 'foo');
        $this->assertNull($user);
    }

    public function testAuthenticateInvalidUsername()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase($pdo, $this->getConfig());

        $user = $pdoDatabase->authenticate('invalidusername', 'password');
        $this->assertNull($user);
    }

    public function testAuthenticateWithRole()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user WHERE username = :identity';
        $pdoDatabase = new PdoDatabase($pdo, $config);

        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
        $this->assertEquals(['admin'], $user->getUserRoles());
    }

    public function testAuthenticateWithRoles()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user_role WHERE username = :identity';
        $pdoDatabase = new PdoDatabase($pdo, $config);

        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
        $this->assertEquals(['user', 'admin'], $user->getUserRoles());
    }

    public function testAuthenticateWithRoleRuntimeError()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $config = $this->getConfig();
        // add a mistake in the configuration
        $config['sql_get_roles'] = 'SELECT role FROM user WHERE foo = :identity';
        $pdoDatabase = new PdoDatabase($pdo, $config);

        $this->expectException(RuntimeException::class);
        $user = $pdoDatabase->authenticate('test', 'password');
    }

    public function testGetRolesFromUserWithEmptySql()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();

        $pdoDatabase = new PdoDatabase($pdo, $config);
        $roles = $pdoDatabase->getRolesFromUser('foo');
        $this->assertEmpty($roles);
    }

    public function testGetRolesFromUserWithNoIdentityParam()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $config = $this->getConfig();
        $config['sql_get_roles'] = 'SELECT role FROM user_role';

        $pdoDatabase = new PdoDatabase($pdo, $config);

        $this->expectException(InvalidConfigException::class);
        $roles = $pdoDatabase->getRolesFromUser('foo');
    }
}
