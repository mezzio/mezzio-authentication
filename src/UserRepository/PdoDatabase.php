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
     * Constructor
     *
     * @param PDO $pdo
     * @param array $config
     */
    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(string $credential, string $password = null): ?UserInterface
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = :username",
            $this->config['table'],
            $this->config['field']['username']
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username', $credential);
        if (! $stmt->execute()) {
            return null;
        }
        $result = $stmt->fetchObject();

        return password_verify($password, $result->{$this->config['field']['password']}) ?
               $this->generateUser($credential, $this->config['field']['role'] ?? '') :
               null;
    }
}
