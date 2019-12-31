# Authentication adapters

The authentication adapters for `mezzio-authentication` implement the
interface `Mezzio\Authentication\AuthenticationInterface`:

```php
namespace Mezzio\Authentication;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface AuthenticationInterface
{
    /**
     * Authenticate the PSR-7 request and return a valid user,
     * or null if not authenticated
     *
     * @param ServerRequestInterface $request
     * @return UserInterface|null
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface;

    /**
     * Generate the unauthorized response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface;
}
```

This interface contains two method: `authenticate()` to check if a PSR-7
request contains a valid credential, and `unauthorizedResponse()` to generate
and return an unauthorized response.

We provide 4 authentication adapters:

- [mezzio-authentication-basic](https://github.com/mezzio/mezzio-authentication-basic),
  for [Basic Access Authentication](https://en.wikipedia.org/wiki/Basic_access_authentication),
  supporting only `bcrypt` as the password hashing algorithm to ensure best
  security.
- [mezzio-authentication-session](https://github.com/mezzio/mezzio-authentication-session),
  for authenticating username/password credential pairs and persisting them
  between requests via PHP sessions.
- [mezzio-authentication-laminasauthentication](https://github.com/mezzio/mezzio-authentication-laminasauthentication),
  supporting the [laminas-authentication](https://github.com/laminas/laminas-authentication)
  component.
- [mezzio-authentication-oauth2](https://github.com/mezzio/mezzio-authentication-oauth2),
  supporting the [OAuth2](https://oauth.net/2/) authentication framework via the
  [league/oauth2-server](https://oauth2.thephpleague.com/) package.
