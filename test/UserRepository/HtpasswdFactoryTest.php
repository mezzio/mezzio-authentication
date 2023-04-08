<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use ArrayAccess;
use ArrayObject;
use Generator;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\Htpasswd;
use Mezzio\Authentication\UserRepository\HtpasswdFactory;
use MezzioTest\Authentication\InMemoryContainer;
use MezzioTest\Authentication\UserRepository\HtpasswdFactoryTest\ConfigImplementingArrayAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

#[CoversClass(HtpasswdFactory::class)]
final class HtpasswdFactoryTest extends TestCase
{
    private InMemoryContainer $container;

    /** @var UserInterface&MockObject */
    private UserInterface $user;

    private HtpasswdFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new InMemoryContainer();
        $this->user      = $this->createMock(UserInterface::class);

        $this->factory = new HtpasswdFactory();
    }

    /**
     * @psalm-return Generator<string,array{0:mixed,1:non-empty-string}>
     */
    public static function validConfigs(): Generator
    {
        $filename = __DIR__ . '/../TestAssets/htpasswd';
        $config   = [
            'authentication' => [
                'htpasswd' => $filename,
            ],
        ];

        yield 'array' => [
            $config,
            $filename,
        ];

        yield ArrayObject::class => [
            new ArrayObject($config),
            $filename,
        ];

        yield ArrayAccess::class => [
            new ConfigImplementingArrayAccess($config),
            $filename,
        ];
    }

    public function testInvokeWithMissingConfig(): void
    {
        $this->expectException(ContainerExceptionInterface::class);

        ($this->factory)($this->container);
    }

    public function testInvokeWithEmptyConfig(): void
    {
        $this->container->set('config', []);

        $this->expectException(InvalidConfigException::class);

        ($this->factory)($this->container);
    }

    public function testInvokeWithInvalidConfig(): void
    {
        $this->container->set('config', [
            'authentication' => [
                'htpasswd' => 'foo',
            ],
        ]);
        $this->container->set(UserInterface::class, fn (): UserInterface => $this->user);

        $this->expectException(InvalidConfigException::class);

        ($this->factory)($this->container);
    }

    /**
     * @psalm-param mixed $validConfig
     * @psalm-param non-empty-string $filename
     */
    #[DataProvider('validConfigs')]
    public function testInvokeWithValidConfig($validConfig, string $filename): void
    {
        $this->container->set('config', $validConfig);
        $this->container->set(UserInterface::class, fn (): UserInterface => $this->user);
        $htpasswd = ($this->factory)($this->container);

        self::assertEquals(
            new Htpasswd($filename, fn (): UserInterface => $this->user),
            $htpasswd
        );
    }
}
