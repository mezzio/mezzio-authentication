<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\ConfigProvider;
use Mezzio\Authentication\UserRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ConfigProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @var ConfigProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testProviderDefinesExpectedFactoryServices(): void
    {
        $config    = $this->provider->getDependencies();
        $factories = $config['factories'];

        $this->assertArrayHasKey(AuthenticationMiddleware::class, $factories);
        $this->assertArrayHasKey(UserRepository\Htpasswd::class, $factories);
        $this->assertArrayHasKey(UserRepository\PdoDatabase::class, $factories);
    }

    public function testInvocationReturnsArrayWithDependencies(): void
    {
        $config = ($this->provider)();

        $this->assertArrayHasKey('authentication', $config);
        $this->assertIsArray($config['authentication']);

        $this->assertArrayHasKey('dependencies', $config);
        $this->assertIsArray($config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);
    }
}
