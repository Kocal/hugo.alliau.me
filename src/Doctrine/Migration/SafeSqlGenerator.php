<?php

declare(strict_types=1);

namespace App\Doctrine\Migration;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Generator\SqlGenerator;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;

use function array_unshift;
use function count;
use function get_class;
use function implode;
use function preg_replace;
use function sprintf;
use function str_repeat;
use function stripos;
use function strlen;
use function var_export;

/**
 * Extends the standard SqlGenerator to add safety checks (hasTable, hasColumn)
 * before executing SQL statements in migrations.
 */
final class SafeSqlGenerator extends SqlGenerator
{
    private SqlFormatter|null $formatter = null;

    public function __construct(
        private readonly SqlParser $parser,
        Configuration $configuration,
        AbstractPlatform $platform,
    ) {
        parent::__construct($configuration, $platform);
    }

    /** @param string[] $sql */
    public function generate(
        array $sql,
        bool $formatted = false,
        bool|null $nowdocOutput = null,
        int $lineLength = 120,
        bool $checkDbPlatform = true,
    ): string {
        $code = [];

        // Access parent's private properties via reflection
        $reflection = new \ReflectionClass(parent::class);
        
        $configurationProperty = $reflection->getProperty('configuration');
        $configuration = $configurationProperty->getValue($this);
        
        $platformProperty = $reflection->getProperty('platform');
        $platform = $platformProperty->getValue($this);

        $storageConfiguration = $configuration->getMetadataStorageConfiguration();
        
        foreach ($sql as $query) {
            // Skip metadata storage table queries
            if (
                $storageConfiguration instanceof TableMetadataStorageConfiguration
                && stripos($query, $storageConfiguration->getTableName()) !== false
            ) {
                continue;
            }

            $parsed = $this->parser->parse($query);

            if ($parsed !== null) {
                $code[] = $this->generateSafeCode($parsed, $formatted, $nowdocOutput, $lineLength);
            } else {
                // Fallback: use standard generation for unparseable queries
                if ($formatted && strlen($query) > ($lineLength - 18 - 8)) {
                    $query = $this->formatQuery($query);
                }
                
                if ($nowdocOutput === true || ($nowdocOutput !== false && $formatted && strlen($query) > ($lineLength - 18 - 8))) {
                    $code[] = sprintf(
                        "\$this->addSql(<<<'SQL'\n%s\nSQL);",
                        preg_replace('/^/m', str_repeat(' ', 4), $query),
                    );
                } else {
                    $code[] = sprintf('$this->addSql(%s);', var_export($query, true));
                }
            }
        }

        if (count($code) !== 0 && $checkDbPlatform && $configuration->isDatabasePlatformChecked()) {
            $currentPlatform = '\\' . get_class($platform);

            array_unshift(
                $code,
                sprintf(
                    <<<'PHP'
$this->abortIf(
    !$this->connection->getDatabasePlatform() instanceof %s,
    "Migration can only be executed safely on '%s'."
);
PHP
                    ,
                    $currentPlatform,
                    $currentPlatform,
                ),
                '',
            );
        }

        return implode("\n", $code);
    }

    private function generateSafeCode(ParsedQuery $parsed, bool $formatted, ?bool $nowdocOutput, int $lineLength): string
    {
        $sqlStatement = $this->formatSqlStatement($parsed->originalSql, $formatted, $nowdocOutput, $lineLength);
        $indent = '    ';

        return match ($parsed->type) {
            'ALTER_ADD' => $this->generateAlterAddCode($parsed, $sqlStatement, $indent),
            'ALTER_DROP' => $this->generateAlterDropCode($parsed, $sqlStatement, $indent),
            'ALTER_COLUMN' => $this->generateAlterColumnCode($parsed, $sqlStatement, $indent),
            'ALTER_RENAME' => $this->generateAlterRenameCode($parsed, $sqlStatement, $indent),
            'CREATE_TABLE' => $this->generateCreateTableCode($parsed, $sqlStatement, $indent),
            'DROP_TABLE' => $this->generateDropTableCode($parsed, $sqlStatement, $indent),
            'CREATE_INDEX' => $this->generateCreateIndexCode($parsed, $sqlStatement, $indent),
            'DROP_INDEX' => $this->generateDropIndexCode($parsed, $sqlStatement, $indent),
            default => $sqlStatement,
        };
    }

    private function generateAlterAddCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        return sprintf(
            <<<'PHP'
if ($schema->hasTable(%s)) {
%s$table = $schema->getTable(%s);
%sif (!$table->hasColumn(%s)) {
%s%s%s
%s}
}
PHP
            ,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->column, true),
            $indent,
            $indent,
            $sqlStatement,
            $indent,
        );
    }

    private function generateAlterDropCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        return sprintf(
            <<<'PHP'
if ($schema->hasTable(%s)) {
%s$table = $schema->getTable(%s);
%sif ($table->hasColumn(%s)) {
%s%s%s
%s}
}
PHP
            ,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->column, true),
            $indent,
            $indent,
            $sqlStatement,
            $indent,
        );
    }

    private function generateAlterColumnCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        return sprintf(
            <<<'PHP'
if ($schema->hasTable(%s)) {
%s$table = $schema->getTable(%s);
%sif ($table->hasColumn(%s)) {
%s%s%s
%s}
}
PHP
            ,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->column, true),
            $indent,
            $indent,
            $sqlStatement,
            $indent,
        );
    }

    private function generateAlterRenameCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        return sprintf(
            <<<'PHP'
if ($schema->hasTable(%s)) {
%s$table = $schema->getTable(%s);
%sif ($table->hasColumn(%s)) {
%s%s%s
%s}
}
PHP
            ,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->column, true),
            $indent,
            $indent,
            $sqlStatement,
            $indent,
        );
    }

    private function generateCreateTableCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        return sprintf(
            <<<'PHP'
if (!$schema->hasTable(%s)) {
%s%s
}
PHP
            ,
            var_export($parsed->table, true),
            $indent,
            $sqlStatement,
        );
    }

    private function generateDropTableCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        return sprintf(
            <<<'PHP'
if ($schema->hasTable(%s)) {
%s%s
}
PHP
            ,
            var_export($parsed->table, true),
            $indent,
            $sqlStatement,
        );
    }

    private function generateCreateIndexCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        return sprintf(
            <<<'PHP'
if ($schema->hasTable(%s)) {
%s$table = $schema->getTable(%s);
%sif (!$table->hasIndex(%s)) {
%s%s%s
%s}
}
PHP
            ,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->table, true),
            $indent,
            var_export($parsed->index, true),
            $indent,
            $indent,
            $sqlStatement,
            $indent,
        );
    }

    private function generateDropIndexCode(ParsedQuery $parsed, string $sqlStatement, string $indent): string
    {
        // For DROP INDEX, we might not have table information
        // So we generate a simpler check or fallback to standard generation
        if ($parsed->table !== '') {
            return sprintf(
                <<<'PHP'
if ($schema->hasTable(%s)) {
%s$table = $schema->getTable(%s);
%sif ($table->hasIndex(%s)) {
%s%s%s
%s}
}
PHP
                ,
                var_export($parsed->table, true),
                $indent,
                var_export($parsed->table, true),
                $indent,
                var_export($parsed->index, true),
                $indent,
                $indent,
                $sqlStatement,
                $indent,
            );
        }

        // Fallback: no table information available
        return $sqlStatement;
    }

    private function formatSqlStatement(string $sql, bool $formatted, ?bool $nowdocOutput, int $lineLength): string
    {
        $maxLength = $lineLength - 18 - 8; // max - php code length - indentation
        
        // Format SQL if requested and it's long enough
        if ($formatted && strlen($sql) > $maxLength) {
            $sql = $this->formatQuery($sql);
        }
        
        if ($nowdocOutput === true || ($nowdocOutput !== false && $formatted && strlen($sql) > $maxLength)) {
            return sprintf(
                "\$this->addSql(<<<'SQL'\n%s\nSQL);",
                preg_replace('/^/m', str_repeat(' ', 4), $sql),
            );
        }

        return sprintf('$this->addSql(%s);', var_export($sql, true));
    }

    private function formatQuery(string $query): string
    {
        $this->formatter ??= new SqlFormatter(new NullHighlighter());

        return $this->formatter->format($query);
    }
}
