<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\Htpasswd;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class HtpasswdTest extends TestCase
{
    /**
     * @expectedException Mezzio\Authentication\Exception\InvalidConfigException
     */
    public function testConstructorWithNoFile()
    {
        $htpasswd = new Htpasswd('foo');
    }

    public function testConstructor()
    {
        $htpasswd = new Htpasswd(__DIR__ . '/../TestAssets/htpasswd');
        $this->assertInstanceOf(UserRepositoryInterface::class, $htpasswd);
    }

    public function testAuthenticate()
    {
        $htpasswd = new Htpasswd(__DIR__ . '/../TestAssets/htpasswd');

        $user = $htpasswd->authenticate('test', 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('test', $user->getIdentity());
    }

    public function testAuthenticateInvalidUser()
    {
        $htpasswd = new Htpasswd(__DIR__ . '/../TestAssets/htpasswd');
        $this->assertNull($htpasswd->authenticate('test', 'foo'));
    }

    public function testAuthenticateWithoutPassword()
    {
        $htpasswd = new Htpasswd(__DIR__ . '/../TestAssets/htpasswd');
        $this->assertNull($htpasswd->authenticate('test', null));
    }

    /**
     * @expectedException Mezzio\Authentication\Exception\RuntimeException
     */
    public function testAuthenticateWithInsecureHash()
    {
        $htpasswd = new Htpasswd(__DIR__ . '/../TestAssets/htpasswd_insecure');
        $htpasswd->authenticate('test', 'password');
    }
}
