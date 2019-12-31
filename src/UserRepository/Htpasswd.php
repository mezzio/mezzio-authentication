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

/**
 * Adapter for Apache htpasswd file
 * It supports only bcrypt hash password for security reason
 * @see https://httpd.apache.org/docs/2.4/programs/htpasswd.html
 */
class Htpasswd implements UserRepositoryInterface
{
    use UserTrait;

    /**
     * Constructor
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        if (! file_exists($filename)) {
            throw new Exception\InvalidConfigException(sprintf(
                "I cannot access the htpasswd file %s",
                $filename
            ));
        }
        $this->filename = $filename;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(string $credential, string $password = null): ?UserInterface
    {
        if (! $handle = fopen($this->filename, "r")) {
            return null;
        }
        $found = false;
        while (! $found && ($line = fgets($handle)) !== false) {
            [$name, $hash] = explode(':', $line);
            if ($credential !== $name) {
                continue;
            }
            $hash = trim($hash);
            $this->checkBcryptHash($hash);
            $found = true;
        }
        fclose($handle);

        return $found && password_verify($password, $hash) ?
               $this->generateUser($credential, '') :
               null;
    }

    /**
     * Check bcrypt usage for security reason
     *
     * @param string $hash
     * @return void
     */
    protected function checkBcryptHash(string $hash): void
    {
        if ('$2y$' !== substr($hash, 0, 4)) {
            throw new Exception\RuntimeException(
                'The htpasswd file uses not secure hash algorithm. Please use bcrypt.'
            );
        }
    }
}
