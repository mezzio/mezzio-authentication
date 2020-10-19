<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\ConfigProvider;
use Mezzio\Authentication\UserRepository;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testProviderDefinesExpectedFactoryServices(): void
    {
        $config = $this->provider->getDependencies();
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
        $this->assertArrayHasKey('aliases', $config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);
    }
}
