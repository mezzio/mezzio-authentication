<?php

declare(strict_types=1);

namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use PDO;
use PDOException;
use Webmozart\Assert\Assert;

use function password_verify;
use function sprintf;
use function strpos;

/**
 * Adapter for PDO database
 *
 * It supports only bcrypt password hashing for security reasons.
 */
class PdoDatabase implements UserRepositoryInterface
{
    private PDO $pdo;

    /** @psalm-var array<string, mixed> */
    private $config;

    /**
     * @var callable
     * @psalm-var callable(string, array<int|string, string>, array<string, mixed>): UserInterface
     */
    private $userFactory;

    /**
     * @psalm-param array<string, mixed> $config
     * @psalm-param callable(string, array<int|string, string>, array<string, mixed>): UserInterface $userFactory
     */
    public function __construct(
        PDO $pdo,
        array $config,
        callable $userFactory
    ) {
        $this->pdo    = $pdo;
        $this->config = $config;

        // Provide type safety for the composed user factory.
        $this->userFactory = static function (
            string $identity,
            array $roles = [],
            array $details = []
        ) use ($userFactory): UserInterface {
            Assert::allString($roles);
            Assert::isMap($details);

            return $userFactory($identity, $roles, $details);
        };
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        $fields = $this->config['field'];
        Assert::isMap($fields);

        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s = :identity",
            (string) $fields['password'],
            (string) $this->config['table'],
            (string) $fields['identity']
        );

        $stmt = $this->pdo->prepare($sql);
        if (false === $stmt) {
            throw new Exception\RuntimeException(
                'An error occurred when preparing to fetch user details from '
                . 'the repository; please verify your configuration'
            );
        }
        $stmt->bindParam(':identity', $credential);
        $stmt->execute();

        $result = $stmt->fetchObject();
        if (! $result) {
            return null;
        }

        Assert::string($credential);
        $passwordHash = (string) ($result->{$fields['password']} ?? '');

        if (password_verify($password ?? '', $passwordHash)) {
            return ($this->userFactory)(
                $credential,
                $this->getUserRoles($credential),
                $this->getUserDetails($credential)
            );
        }
        return null;
    }

    /**
     * Get the user roles if present.
     *
     * @psalm-return list<string>
     */
    protected function getUserRoles(string $identity): array
    {
        if (! isset($this->config['sql_get_roles'])) {
            return [];
        }

        Assert::string($this->config['sql_get_roles']);

        if (false === strpos($this->config['sql_get_roles'], ':identity')) {
            throw new Exception\InvalidConfigException(
                'The sql_get_roles configuration setting must include an :identity parameter'
            );
        }

        try {
            $stmt = $this->pdo->prepare($this->config['sql_get_roles']);
        } catch (PDOException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Error preparing retrieval of user roles: %s',
                $e->getMessage()
            ));
        }
        if (false === $stmt) {
            throw new Exception\RuntimeException(sprintf(
                'Error preparing retrieval of user roles: unknown error'
            ));
        }
        $stmt->bindParam(':identity', $identity);

        if (! $stmt->execute()) {
            return [];
        }

        $roles = [];
        foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $role) {
            Assert::isList($role);
            $roles[] = (string) $role[0];
        }
        return $roles;
    }

    /**
     * Get the user details if present.
     *
     * @psalm-return array<string, mixed>
     */
    protected function getUserDetails(string $identity): array
    {
        if (! isset($this->config['sql_get_details'])) {
            return [];
        }

        Assert::string($this->config['sql_get_details']);

        if (false === strpos($this->config['sql_get_details'], ':identity')) {
            throw new Exception\InvalidConfigException(
                'The sql_get_details configuration setting must include a :identity parameter'
            );
        }

        try {
            $stmt = $this->pdo->prepare($this->config['sql_get_details']);
        } catch (PDOException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Error preparing retrieval of user details: %s',
                $e->getMessage()
            ));
        }
        if (false === $stmt) {
            throw new Exception\RuntimeException(sprintf(
                'Error preparing retrieval of user details: unknown error'
            ));
        }
        $stmt->bindParam(':identity', $identity);

        if (! $stmt->execute()) {
            return [];
        }

        $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        Assert::isMap($userDetails);

        return $userDetails;
    }
}
