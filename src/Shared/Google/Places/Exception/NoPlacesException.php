<?php

namespace App\Shared\Google\Places\Exception;

final class NoPlacesException extends \RuntimeException implements Exception
{
    public function __construct(string $textQuery, \Throwable|null $previous = null)
    {
        parent::__construct(sprintf('No places found for text query "%s".', $textQuery), 0, $previous);
    }
}