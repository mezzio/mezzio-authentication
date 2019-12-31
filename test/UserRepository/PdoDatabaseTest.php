<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace MezzioTest\Authentication\UserRepository;

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

    public function testAuthenticate()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase($pdo, [
            'table' => 'user',
            'field' => [
                'username' => 'username',
                'password' => 'password'
            ]
        ]);

        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getUsername());
    }

    public function testAuthenticateInvalidUser()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite');
        $pdoDatabase = new PdoDatabase($pdo, [
            'table' => 'user',
            'field' => [
                'username' => 'username',
                'password' => 'password'
            ]
        ]);

        $user = $pdoDatabase->authenticate('test', 'foo');
        $this->assertNull($user);
    }

    public function testAuthenticateWithRole()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_role.sqlite');
        $pdoDatabase = new PdoDatabase($pdo, [
            'table' => 'user',
            'field' => [
                'username' => 'username',
                'password' => 'password'
            ],
            'sql_get_roles' => 'SELECT role FROM user WHERE username = :username'
        ]);

        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getUsername());
        $this->assertEquals(['admin'], $user->getUserRoles());
    }

    public function testAuthenticateWithRoles()
    {
        $pdo = new PDO('sqlite:'. __DIR__ . '/../TestAssets/pdo_roles.sqlite');
        $pdoDatabase = new PdoDatabase($pdo, [
            'table' => 'user',
            'field' => [
                'username' => 'username',
                'password' => 'password'
            ],
            'sql_get_roles' => 'SELECT role FROM user_role WHERE username = :username'
        ]);

        $user = $pdoDatabase->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getUsername());
        $this->assertEquals(['user', 'admin'], $user->getUserRoles());
    }
}
