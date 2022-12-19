<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;

use function basename;
use function glob;
use function is_a;
use function strrpos;
use function substr;

final class ExceptionTest extends TestCase
{
    /**
     * @psalm-return iterable<string, array{0: string}>
     */
    public function exception(): iterable
    {
        $namespace = substr(ExceptionInterface::class, 0, (int) strrpos(ExceptionInterface::class, '\\') + 1);

        $exceptions = glob(__DIR__ . '/../src/Exception/*.php');
        foreach ($exceptions as $exception) {
            $class = substr(basename($exception), 0, -4);

            yield $class => [$namespace . $class];
        }
    }

    /**
     * @dataProvider exception
     */
    public function testExceptionIsInstanceOfExceptionInterface(string $exception): void
    {
        self::assertStringContainsString('Exception', $exception);
        self::assertTrue(is_a($exception, ExceptionInterface::class, true));
    }
}
