<?php

declare(strict_types=1);

namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use PDO;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class PdoDatabaseFactory
{
    /**
     * @throws Exception\InvalidConfigException
     */
    public function __invoke(ContainerInterface $container): PdoDatabase
    {
        $config = $container->get('config');
        Assert::isArrayAccessible($config);
        $authConfig = $config['authentication'] ?? [];
        Assert::isArrayAccessible($authConfig);
        $pdo = $authConfig['pdo'] ?? null;

        if (null === $pdo) {
            throw new Exception\InvalidConfigException(
                'PDO values are missing in authentication config'
            );
        }

        Assert::isMap($pdo);

        if (! isset($pdo['table'])) {
            throw new Exception\InvalidConfigException(
                'The PDO table name is missing in the configuration'
            );
        }
        if (! isset($pdo['field']['identity'])) {
            throw new Exception\InvalidConfigException(
                'The PDO identity field is missing in the configuration'
            );
        }
        if (! isset($pdo['field']['password'])) {
            throw new Exception\InvalidConfigException(
                'The PDO password field is missing in the configuration'
            );
        }

        $user = $container->get(UserInterface::class);
        Assert::isCallable($user);

        if (isset($pdo['service']) && $container->has((string) $pdo['service'])) {
            $pdoService = $container->get((string) $pdo['service']);
            Assert::isInstanceOf($pdoService, PDO::class);

            /** @psalm-suppress MixedArgumentTypeCoercion */
            return new PdoDatabase(
                $pdoService,
                $pdo,
                $user
            );
        }

        if (! isset($pdo['dsn'])) {
            throw new Exception\InvalidConfigException(
                'The PDO DSN value is missing in the configuration'
            );
        }

        $connection = new PDO(
            (string) $pdo['dsn'],
            (string) ($pdo['username'] ?? null),
            (string) ($pdo['password'] ?? null)
        );
        /** @deprecated {@see https://wiki.php.net/rfc/pdo_default_errmode} */
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        /** @psalm-suppress MixedArgumentTypeCoercion */
        return new PdoDatabase($connection, $pdo, $user);
    }
}
