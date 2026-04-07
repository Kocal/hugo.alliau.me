<?php

declare(strict_types=1);

namespace App\Doctrine\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;

/**
 * Factory to create SafeSqlGenerator instances for the Doctrine Migrations DependencyFactory.
 */
final class SafeSqlGeneratorFactory
{
    public function __construct(
        private readonly SqlParser $parser,
        private readonly Configuration $configuration,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(): SafeSqlGenerator
    {
        return new SafeSqlGenerator(
            $this->parser,
            $this->configuration,
            $this->connection->getDatabasePlatform(),
        );
    }
}
