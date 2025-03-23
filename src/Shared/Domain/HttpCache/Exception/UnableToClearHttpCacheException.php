<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache\Exception;

final class UnableToClearHttpCacheException extends \Exception
{
    public function __construct(string $reason, ?\Throwable $previous = null)
    {
        parent::__construct('Unable to clear HTTP cache: ' . $reason, 0, $previous);
    }
}
