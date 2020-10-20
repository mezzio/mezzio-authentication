<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication;

use Zend\Expressive\Authentication as Expressive;

class ConfigProvider
{
    /**
     * Return the configuration array.
     *
     * @psalm-return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'authentication' => $this->getAuthenticationConfig(),
            'dependencies'   => $this->getDependencies(),
        ];
    }

    /**
     * @psalm-return array<empty>
     */
    public function getAuthenticationConfig(): array
    {
        return [
            /*
             * Values will depend on user repository and/or adapter.
             *
             * Example: using htpasswd UserRepositoryInterface implementation:
             *
             * [
             *     'htpasswd' => 'insert the path to htpasswd file',
             *     'pdo' => [
             *         'dsn' => 'DSN for connection',
             *         'username' => 'username for database connection, if needed',
             *         'password' => 'password for database connection, if needed',
             *         'table' => 'user table name',
             *         'field' => [
             *             'identity' => 'identity field name',
             *             'password' => 'password field name',
             *         ],
             *         'sql_get_roles'   => 'SQL to retrieve user roles by :identity',
             *         'sql_get_details' => 'SQL to retrieve user details by :identity',
             *     ],
             * ]
             */
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @psalm-return array<string, array<string, class-string>>
     */
    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                // Provide an alias for the AuthenticationInterface based on the adapter you are using.
                // AuthenticationInterface::class => Basic\BasicAccess::class,
                // Provide an alias for the UserRepository adapter based on your application needs.
                // UserRepositoryInterface::class => UserRepository\Htpasswd::class,

                // Legacy Zend Framework aliases
                Expressive\AuthenticationMiddleware::class   => AuthenticationMiddleware::class,
                Expressive\UserRepository\Htpasswd::class    => UserRepository\Htpasswd::class,
                Expressive\UserRepository\PdoDatabase::class => UserRepository\PdoDatabase::class,
                Expressive\UserInterface::class              => UserInterface::class,
            ],
            'factories' => [
                AuthenticationMiddleware::class   => AuthenticationMiddlewareFactory::class,
                UserRepository\Htpasswd::class    => UserRepository\HtpasswdFactory::class,
                UserRepository\PdoDatabase::class => UserRepository\PdoDatabaseFactory::class,
                UserInterface::class              => DefaultUserFactory::class,
            ],
        ];
    }
}
