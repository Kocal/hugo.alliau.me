<?php

declare(strict_types=1);

namespace App\Doctrine\Migration;

/**
 * Represents a parsed SQL query with extracted metadata.
 */
final readonly class ParsedQuery
{
    public function __construct(
        public string $type,
        public string $table,
        public ?string $column,
        public ?string $index,
        public string $originalSql,
    ) {
    }
}
