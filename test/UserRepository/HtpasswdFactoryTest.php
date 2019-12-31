<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\UserRepository\Htpasswd;
use Mezzio\Authentication\UserRepository\HtpasswdFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class HtpasswdFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new HtpasswdFactory();
    }

    /**
     * @expectedException Mezzio\Authentication\Exception\InvalidConfigException
     */
    public function testInvokeWithEmptyConfig()
    {
        $this->container->get('config')->willReturn([]);
        $htpasswd = ($this->factory)($this->container->reveal());
    }

    /**
     * @expectedException Mezzio\Authentication\Exception\InvalidConfigException
     */
    public function testInvokeWithInvalidConfig()
    {
        $this->container->get('config')->willReturn([
            'authentication' => [
                'htpasswd' => 'foo'
            ]
        ]);
        $htpasswd = ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithValidConfig()
    {
        $this->container->get('config')->willReturn([
            'authentication' => [
                'htpasswd' => __DIR__ . '/../TestAssets/htpasswd'
            ]
        ]);
        $htpasswd = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(Htpasswd::class, $htpasswd);
    }
}
