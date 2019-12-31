# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - 2019-03-05

### Added

- [zendframework/zend-expressive-authentication#41](https://github.com/zendframework/zend-expressive-authentication/pull/46) allows users to provide an application-specific PDO service name to use
  with the `PdoDatabase` user repository implementation, instead of connection
  parameters.  This allows re-use of an existing PDO connection. To configure it:

  ```php
  return [
      'authentication' => [
          'pdo' => [
              'service' => 'name-of-existing-PDO-service',
              'table' => 'name-of-table-to-use',
              'field' => [
                  'identity' => 'name-of-field-containing-identity',
                  'password' => 'name-of-field-containing-password-hash',
              ],
          ],
      ],
  ];
  ```

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.2 - 2019-03-05

### Added

- [zendframework/zend-expressive-authentication#43](https://github.com/zendframework/zend-expressive-authentication/pull/43) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication#40](https://github.com/zendframework/zend-expressive-authentication/pull/45) corrects the name of a configuration parameter name referenced when
  raising an exception while invoking `Mezzio\Authentication\UserRepositoryPdoDatabaseFactory`.

## 1.0.1 - 2018-09-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication#37](https://github.com/zendframework/zend-expressive-authentication/pull/37) handles null values when verifying password in `PdoDatabase`

## 1.0.0 - 2018-08-27

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication#27](https://github.com/zendframework/zend-expressive-authentication/pull/27) `Mezzio\Authentication\UserInterface::getRoles()` returns an [iterable](http://php.net/manual/en/language.types.iterable.php) instead of array.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.0 - 2018-05-23

### Added

- [zendframework/zend-expressive-authentication#28](https://github.com/zendframework/zend-expressive-authentication/pull/28) adds the final class `DefaultUser`, which provides an immutable version of `UserInterface`
  that can be used in most situations.

- [zendframework/zend-expressive-authentication#28](https://github.com/zendframework/zend-expressive-authentication/pull/28) adds the service factory `DefaultUserFactory`, which returns a PHP `callable`
  capable of producing a `DefaultUser` instance from the provided `$identity`,
  `$roles`, and `$details` arguments.

### Changed

- [zendframework/zend-expressive-authentication#28](https://github.com/zendframework/zend-expressive-authentication/pull/28) updates the `PdoDatabase` user repository to accept an additional
  configuration item, `sql_get_details`. This value should be a SQL statement
  that may be used to retrieve additional user details to provide in the
  `UserInterface` instance returned by the repository on successful
  authentication.

- [zendframework/zend-expressive-authentication#28](https://github.com/zendframework/zend-expressive-authentication/pull/28) updates `UserRepositoryInterface` to remove the method `getRolesFromUser()`;
  this method is not needed, as `UserInterface` already provides access to user roles.

- [zendframework/zend-expressive-authentication#28](https://github.com/zendframework/zend-expressive-authentication/pull/28) modifies each of the `Htpasswd` and `PdoDatabase` user repository
  implementations to accept a new constructor argument, a callable
  `$userFactory`. This factory should implement the following signature:

  ```php
  function (string $identity, array $roles = [], array $details = []) : UserInterface
  ```

  This factory will be called by the repository in order to produce a
  `UserInterface` instance on successful authentication. You may provide the
  factory via the service `Mezzio\Authentication\UserInterface` if you
  wish to use one other than the one returned by the provided
  `DefaultUserFactory` class.

- [zendframework/zend-expressive-authentication#28](https://github.com/zendframework/zend-expressive-authentication/pull/28) modifies `UserInterface` as follows:
  - Renames `getUserRoles()` to `getRoles()`
  - Adds `getDetail(string $name, mixed $default)`
  - Adds `getDetails() : array`

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-authentication#28](https://github.com/zendframework/zend-expressive-authentication/pull/28) removes `UserTrait` in favor of the `DefaultUser` implementation.

### Fixed

- Nothing.

## 0.4.0 - 2018-03-15

### Added

- [zendframework/zend-expressive-authentication#15](https://github.com/zendframework/zend-expressive-authentication/pull/15)
  adds support for PSR-15.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-authentication#15](https://github.com/zendframework/zend-expressive-authentication/pull/15) and
  [zendframework/zend-expressive-authentication#3](https://github.com/zendframework/zend-expressive-authentication/pull/3)
  remove support for http-interop/http-middleware and
  http-interop/http-server-middleware.

- [zendframework/zend-expressive-authentication#19](https://github.com/zendframework/zend-expressive-authentication/pull/19)
  removes `Mezzio\Authentication\ResponsePrototypeTrait`; the approach
  was flawed, and the various adapters will be updated to compose response
  factories instead of instances.

### Fixed

- [zendframework/zend-expressive-authentication#18](https://github.com/zendframework/zend-expressive-authentication/pull/18)
  uses the `ResponseInterface` as a factory. This was recently changed in
  [mezzio#561](https://github.com/zendframework/zend-expressive/pull/561).

## 0.3.1 - 2018-03-12

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication#22](https://github.com/zendframework/zend-expressive-authentication/issues/22)
  updates the `ResponsePrototypeTrait` to allow callable `ResponseInterface`
  services (instead of those directly returning a `ResponseInterface`).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2018-01-24

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication#14](https://github.com/zendframework/zend-expressive-authentication/issues/14)
  renames the method `UserInterface::getUsername()` to
  `UserInterface::getIdentity()`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication#13](https://github.com/zendframework/zend-expressive-authentication/pull/13)
  fixes an issue whereby fetching a record by an unknown username resulted in a
  "Trying to get property of non-object" error when using the `PdoDatabase` user
  repository implementation.

## 0.2.0 - 2017-11-27

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication#4](https://github.com/zendframework/zend-expressive-authentication/pull/4)
  renames the method `UserInterface::getUserRole()` to
  `UserInterface::getUserRoles()`. The method MUST return an array of string
  role names.

- [zendframework/zend-expressive-authentication#4](https://github.com/zendframework/zend-expressive-authentication/pull/4)
  renames the method `UserRepositoryInterface::getRoleFromUser()` to
  `UserRepositoryInterface::getRolesFromUser()`. The method MUST return an array
  of string role names.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2017-11-08

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
