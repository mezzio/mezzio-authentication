<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\AuthenticationMiddlewareFactory;
use Mezzio\Authentication\ConfigProvider;
use Mezzio\Authentication\DefaultUserFactory;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository;
use PHPUnit\Framework\TestCase;

/** @covers \Mezzio\Authentication\ConfigProvider */
final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new ConfigProvider();
    }

    public function testProviderDefinesExpectedFactoryServices(): void
    {
        self::assertSame([
            'factories' => [
                AuthenticationMiddleware::class   => AuthenticationMiddlewareFactory::class,
                UserRepository\Htpasswd::class    => UserRepository\HtpasswdFactory::class,
                UserRepository\PdoDatabase::class => UserRepository\PdoDatabaseFactory::class,
                UserInterface::class              => DefaultUserFactory::class,
            ],
        ], $this->provider->getDependencies());
    }

    public function testInvocationReturnsArrayWithDependencies(): void
    {
        self::assertSame([
            'authentication' => [],
            'dependencies'   => [
                'factories' => [
                    AuthenticationMiddleware::class   => AuthenticationMiddlewareFactory::class,
                    UserRepository\Htpasswd::class    => UserRepository\HtpasswdFactory::class,
                    UserRepository\PdoDatabase::class => UserRepository\PdoDatabaseFactory::class,
                    UserInterface::class              => DefaultUserFactory::class,
                ],
            ],
        ], ($this->provider)());
    }
}
