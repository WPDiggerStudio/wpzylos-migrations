<?php

declare(strict_types=1);

namespace WPZylos\Framework\Migrations;

use WPZylos\Framework\Database\Connection;

/**
 * Base migration class.
 *
 * All migrations should extend this class and implement up().
 *
 * @package WPZylos\Framework\Migrations
 */
abstract class Migration
{
    /**
     * @var Connection Database connection
     */
    protected Connection $db;

    /**
     * Set database connection.
     *
     * @param Connection $db Database connection
     * @return void
     */
    public function setConnection(Connection $db): void
    {
        $this->db = $db;
    }

    /**
     * Run the migration.
     *
     * @return void
     */
    abstract public function up(): void;

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        // Override in child class if rollback needed
    }

    /**
     * Run dbDelta to create/update a table.
     *
     * @param string $sql CREATE TABLE SQL
     * @return array<string, mixed> Results from dbDelta
     */
    protected function runDbDelta(string $sql): array
    {
        // Only require if dbDelta not already defined (allows testing with mocks)
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        return dbDelta($sql);
    }

    /**
     * Get charset collation.
     *
     * @return string
     */
    protected function charsetCollate(): string
    {
        return $this->db->charsetCollate();
    }

    /**
     * Drop a table.
     *
     * @param string $table Table name (without prefix)
     * @return bool
     */
    protected function dropTable(string $table): bool
    {
        $fullName = $this->db->wpdb()->prefix . $table;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
        return (bool) $this->db->query("DROP TABLE IF EXISTS `{$fullName}`");
    }

    /**
     * Create a table.
     *
     * Convenience method for creating tables with dbDelta.
     *
     * @param string $table Table name (without prefix)
     * @param array<string, string> $columns Column definitions
     * @param string[] $keys Key definitions (e.g., 'PRIMARY KEY (id)')
     * @return array<string, mixed> Results from dbDelta
     */
    protected function create(string $table, array $columns, array $keys = []): array
    {
        $fullName = $this->db->wpdb()->prefix . $table;
        $charset = $this->charsetCollate();

        $columnsSql = [];
        foreach ($columns as $name => $definition) {
            $columnsSql[] = "`{$name}` {$definition}";
        }

        $sql = "CREATE TABLE `{$fullName}` (\n";
        $sql .= implode(",\n", $columnsSql);

        if (!empty($keys)) {
            $sql .= ",\n" . implode(",\n", $keys);
        }

        $sql .= "\n) {$charset};";

        return $this->runDbDelta($sql);
    }

    /**
     * Drop a table (alias for dropTable).
     *
     * @param string $table Table name (without prefix)
     * @return bool
     */
    protected function drop(string $table): bool
    {
        return $this->dropTable($table);
    }
}
