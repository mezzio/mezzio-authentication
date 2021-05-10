<?php

declare(strict_types=1);

namespace Mezzio\Authentication\Exception;

use RuntimeException as SplRuntimeException;

class RuntimeException extends SplRuntimeException implements ExceptionInterface
{
}
