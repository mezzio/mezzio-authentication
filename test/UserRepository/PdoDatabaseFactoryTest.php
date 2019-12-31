<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\UserRepository\PdoDatabase;
use Mezzio\Authentication\UserRepository\PdoDatabaseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class PdoDatabaseFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new PdoDatabaseFactory();
    }

    /**
     * @expectedException Mezzio\Authentication\Exception\InvalidConfigException
     */
    public function testInvokeWithEmptyConfig()
    {
        $this->container->get('config')->willReturn([]);
        $pdoDatabase = ($this->factory)($this->container->reveal());
    }

    public function getPdoConfig()
    {
        return [
            [[]],
            [[
                'dsn' => 'mysql:dbname=testdb;host=127.0.0.1'
            ]],
            [[
                'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
                'table' => 'test'
            ]],
            [[
                'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
                'table' => 'test',
                'field' => []
            ]],
            [[
                'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
                'table' => 'test',
                'field' => [
                    'username' => 'email'
                ]
            ]]
        ];
    }

    /**
     * @dataProvider getPdoConfig
     * @expectedException Mezzio\Authentication\Exception\InvalidConfigException
     */
    public function testInvokeWithInvalidConfig($pdoConfig)
    {
        $this->container->get('config')->willReturn([
            'authentication' => [ 'pdo' => $pdoConfig ]
        ]);
        $pdoDatabase = ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithValidConfig()
    {
        $this->container->get('config')->willReturn([
            'authentication' => [
                'pdo' => [
                    'dsn' => 'sqlite:'. __DIR__ . '/../TestAssets/pdo.sqlite',
                    'table' => 'user',
                    'field' => [
                        'username' => 'username',
                        'password' => 'password'
                    ]
                ]
            ]
        ]);
        $pdoDatabase = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(PdoDatabase::class, $pdoDatabase);
    }
}
