<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use PDO;

/**
 * Adapter for PDO database
 * It supports only bcrypt hash password for security reason
 */
class PdoDatabase implements UserRepositoryInterface
{
    use UserTrait;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $config;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(string $credential, string $password = null) : ?UserInterface
    {
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s = :username",
            $this->config['field']['password'],
            $this->config['table'],
            $this->config['field']['username']
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username', $credential);

        if (! $stmt->execute()) {
            return null;
        }

        $result = $stmt->fetchObject();

        return password_verify($password, $result->{$this->config['field']['password']})
            ? $this->generateUser($credential, $this->getRolesFromUser($credential))
            : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getRolesFromUser(string $username) : array
    {
        if (! isset($this->config['sql_get_roles'])) {
            return [];
        }

        if (false === strpos($this->config['sql_get_roles'], ':username')) {
            throw new Exception\InvalidConfigException(
                'The sql_get_roles configuration setting must include a :username parameter'
            );
        }

        $stmt = $this->pdo->prepare($this->config['sql_get_roles']);
        $stmt->bindParam(':username', $username);

        if (! $stmt->execute()) {
            return [];
        }

        $roles = [];
        foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $role) {
            $roles[] = $role[0];
        }
        return $roles;
    }
}
