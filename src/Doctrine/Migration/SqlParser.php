<?php

declare(strict_types=1);

namespace App\Doctrine\Migration;

use function preg_match;
use function str_replace;
use function strtoupper;
use function trim;

/**
 * Parses SQL queries to extract table names, column names, and operation types.
 * Optimized for PostgreSQL syntax.
 */
final class SqlParser
{
    private const PATTERN_ALTER_TABLE_ADD = '/ALTER\s+TABLE\s+(?:IF\s+EXISTS\s+)?(?:"?(\w+)"?\.)?\"?(\w+)\"?\s+ADD\s+(?:COLUMN\s+)?\"?(\w+)\"?/i';
    private const PATTERN_ALTER_TABLE_DROP = '/ALTER\s+TABLE\s+(?:IF\s+EXISTS\s+)?(?:"?(\w+)"?\.)?\"?(\w+)\"?\s+DROP\s+(?:COLUMN\s+)?\"?(\w+)\"?/i';
    private const PATTERN_ALTER_TABLE_ALTER = '/ALTER\s+TABLE\s+(?:IF\s+EXISTS\s+)?(?:"?(\w+)"?\.)?\"?(\w+)\"?\s+ALTER\s+(?:COLUMN\s+)?\"?(\w+)\"?/i';
    private const PATTERN_ALTER_TABLE_RENAME = '/ALTER\s+TABLE\s+(?:IF\s+EXISTS\s+)?(?:"?(\w+)"?\.)?\"?(\w+)\"?\s+RENAME\s+(?:COLUMN\s+)?\"?(\w+)\"?\s+TO/i';
    private const PATTERN_CREATE_TABLE = '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:"?(\w+)"?\.)?\"?(\w+)\"?/i';
    private const PATTERN_DROP_TABLE = '/DROP\s+TABLE\s+(?:IF\s+EXISTS\s+)?(?:"?(\w+)"?\.)?\"?(\w+)\"?/i';
    private const PATTERN_CREATE_INDEX = '/CREATE\s+(?:UNIQUE\s+)?INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?\"?(\w+)\"?\s+ON\s+(?:"?(\w+)"?\.)?\"?(\w+)\"?/i';
    private const PATTERN_DROP_INDEX = '/DROP\s+INDEX\s+(?:IF\s+EXISTS\s+)?(?:(?:"?(\w+)"?\.)?\"?(\w+)\"?|\"?(\w+)\"?)/i';

    public function parse(string $sql): ?ParsedQuery
    {
        $sql = $this->normalizeSql($sql);

        // Try to match ALTER TABLE ADD COLUMN
        if (preg_match(self::PATTERN_ALTER_TABLE_ADD, $sql, $matches)) {
            $table = $matches[2];
            $column = $matches[3];
            return new ParsedQuery('ALTER_ADD', $table, $column, null, $sql);
        }

        // Try to match ALTER TABLE DROP COLUMN
        if (preg_match(self::PATTERN_ALTER_TABLE_DROP, $sql, $matches)) {
            $table = $matches[2];
            $column = $matches[3];
            return new ParsedQuery('ALTER_DROP', $table, $column, null, $sql);
        }

        // Try to match ALTER TABLE ALTER COLUMN
        if (preg_match(self::PATTERN_ALTER_TABLE_ALTER, $sql, $matches)) {
            $table = $matches[2];
            $column = $matches[3];
            return new ParsedQuery('ALTER_COLUMN', $table, $column, null, $sql);
        }

        // Try to match ALTER TABLE RENAME COLUMN
        if (preg_match(self::PATTERN_ALTER_TABLE_RENAME, $sql, $matches)) {
            $table = $matches[2];
            $column = $matches[3];
            return new ParsedQuery('ALTER_RENAME', $table, $column, null, $sql);
        }

        // Try to match CREATE TABLE
        if (preg_match(self::PATTERN_CREATE_TABLE, $sql, $matches)) {
            $table = $matches[2];
            return new ParsedQuery('CREATE_TABLE', $table, null, null, $sql);
        }

        // Try to match DROP TABLE
        if (preg_match(self::PATTERN_DROP_TABLE, $sql, $matches)) {
            $table = $matches[2];
            return new ParsedQuery('DROP_TABLE', $table, null, null, $sql);
        }

        // Try to match CREATE INDEX
        if (preg_match(self::PATTERN_CREATE_INDEX, $sql, $matches)) {
            $indexName = $matches[1];
            $table = $matches[3];
            return new ParsedQuery('CREATE_INDEX', $table, null, $indexName, $sql);
        }

        // Try to match DROP INDEX
        if (preg_match(self::PATTERN_DROP_INDEX, $sql, $matches)) {
            // DROP INDEX can have different formats
            $indexName = $matches[3] ?? $matches[2] ?? $matches[1];
            // For DROP INDEX, we might not know the table name from the SQL
            return new ParsedQuery('DROP_INDEX', '', null, $indexName, $sql);
        }

        // Unable to parse
        return null;
    }

    private function normalizeSql(string $sql): string
    {
        // Replace multiple spaces with single space
        $sql = preg_replace('/\s+/', ' ', $sql);
        
        // Trim whitespace
        return trim($sql);
    }
}
