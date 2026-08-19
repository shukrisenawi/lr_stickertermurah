<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DateTimeInterface;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    public function download(): StreamedResponse
    {
        $sql = $this->buildDump();
        $filename = 'database-backup-'.now()->format('Y-m-d-His').'.sql';

        return response()->streamDownload(
            static function () use ($sql): void {
                echo $sql;
            },
            $filename,
            [
                'Content-Type' => 'application/sql',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    private function buildDump(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (! in_array($driver, ['sqlite', 'mysql', 'mariadb'], true)) {
            throw new RuntimeException('Backup database hanya menyokong SQLite dan MySQL.');
        }

        $schema = $connection->getSchemaBuilder();
        $tables = [];

        $schemaTables = in_array($driver, ['mysql', 'mariadb'], true)
            ? $schema->getTables($connection->getDatabaseName())
            : $schema->getTables();

        foreach ($schemaTables as $table) {
            $tableName = (string) $table['name'];
            $createStatement = $this->createTableStatement($connection, $driver, $tableName);

            if ($createStatement === '') {
                continue;
            }

            $columns = $schema->getColumns($tableName);
            $tables[] = [
                'name' => $tableName,
                'create' => $createStatement,
                'columns' => $columns,
            ];
        }

        $lines = [
            '-- StickerTermurah database backup',
            '-- Generated: '.now()->toIso8601String(),
            '',
            $driver === 'sqlite' ? 'PRAGMA foreign_keys=OFF;' : 'SET FOREIGN_KEY_CHECKS=0;',
            $driver === 'sqlite' ? 'BEGIN TRANSACTION;' : 'START TRANSACTION;',
            '',
        ];

        foreach ($tables as $table) {
            $quotedTable = $this->quoteIdentifier($table['name'], $driver);
            $lines[] = 'DROP TABLE IF EXISTS '.$quotedTable.';';
            $lines[] = $this->terminateStatement($table['create']);

            $columnNames = array_map(
                static fn (array $column): string => (string) $column['name'],
                $table['columns'],
            );
            $binaryColumns = $this->binaryColumns($table['columns']);

            foreach ($connection->table($table['name'])->get() as $row) {
                $values = (array) $row;
                $sqlValues = array_map(
                    fn (string $column): string => $this->quoteValue(
                        $values[$column] ?? null,
                        $binaryColumns[$column] ?? false,
                        $connection,
                    ),
                    $columnNames,
                );

                $lines[] = 'INSERT INTO '.$quotedTable
                    .' ('.implode(', ', array_map(fn (string $column): string => $this->quoteIdentifier($column, $driver), $columnNames)).')'
                    .' VALUES ('.implode(', ', $sqlValues).');';
            }

            $lines[] = '';
        }

        if ($driver === 'sqlite') {
            foreach ($connection->select(
                "SELECT sql FROM sqlite_master WHERE type IN ('index', 'trigger') AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY type, name",
            ) as $object) {
                $lines[] = $this->terminateStatement((string) $object->sql);
            }
        }

        $lines[] = 'COMMIT;';
        $lines[] = $driver === 'sqlite' ? 'PRAGMA foreign_keys=ON;' : 'SET FOREIGN_KEY_CHECKS=1;';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function createTableStatement(Connection $connection, string $driver, string $table): string
    {
        if ($driver === 'sqlite') {
            $result = $connection->selectOne(
                "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?",
                [$table],
            );

            return trim((string) ($result?->sql ?? ''));
        }

        $result = $connection->selectOne('SHOW CREATE TABLE '.$this->quoteIdentifier($table, $driver));
        $values = $result ? (array) $result : [];
        $statement = $values['Create Table'] ?? null;

        if ($statement === null) {
            $statement = array_values($values)[1] ?? '';
        }

        return trim((string) $statement);
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<string, bool>
     */
    private function binaryColumns(array $columns): array
    {
        $binaryColumns = [];

        foreach ($columns as $column) {
            $type = strtolower((string) ($column['type_name'] ?? $column['type'] ?? ''));
            $binaryColumns[(string) $column['name']] = str_contains($type, 'blob')
                || str_contains($type, 'binary')
                || str_contains($type, 'bytea');
        }

        return $binaryColumns;
    }

    private function quoteValue(mixed $value, bool $binary, Connection $connection): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if ($binary) {
            $contents = is_resource($value) ? stream_get_contents($value) : (string) $value;

            return "X'".bin2hex($contents === false ? '' : $contents)."'";
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $quoted = $connection->getPdo()->quote((string) $value);

        if ($quoted === false) {
            throw new RuntimeException('Nilai database tidak dapat diproses untuk backup.');
        }

        return $quoted;
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return '`'.str_replace('`', '``', $identifier).'`';
        }

        return '"'.str_replace('"', '""', $identifier).'"';
    }

    private function terminateStatement(string $statement): string
    {
        $statement = trim($statement);

        return str_ends_with($statement, ';') ? $statement : $statement.';';
    }
}
