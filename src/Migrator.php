<?php

declare(strict_types=1);

namespace WPZylos\Framework\Migrations;

use RuntimeException;
use WPZylos\Framework\Core\Contracts\ContextInterface;
use WPZylos\Framework\Database\Connection;

/**
 * Migration runner.
 *
 * Runs, rolls back, and tracks migrations.
 *
 * @package WPZylos\Framework\Migrations
 */
class Migrator
{
    /**
     * @var Connection Database connection
     */
    private Connection $db;

    /**
     * @var MigrationRepository Migration repository
     */
    private MigrationRepository $repository;

    /**
     * @var string Migrations directory
     */
    private string $migrationsPath;

    /**
     * Create migrator.
     *
     * @param ContextInterface $context Plugin context
     * @param Connection $db Database connection
     * @param MigrationRepository $repository Migration repository
     * @param string|null $migrationsPath Migrations directory
     */
    public function __construct(
        ContextInterface $context,
        Connection $db,
        MigrationRepository $repository,
        ?string $migrationsPath = null
    ) {
        $this->db = $db;
        $this->repository = $repository;
        $this->migrationsPath = $migrationsPath ?? $context->path('database/migrations');
    }

    /**
     * Run all pending migrations.
     *
     * @return string[] Names of migrations that ran
     */
    public function run(): array
    {
        $pending = $this->getPending();

        if (empty($pending)) {
            return [];
        }

        $this->repository->incrementBatch();
        $ran = [];

        foreach ($pending as $migration) {
            $this->runMigration($migration);
            $ran[] = $migration;
        }

        return $ran;
    }

    /**
     * Rollback last batch of migrations.
     *
     * @param int $steps Number of migrations to rollback
     * @return string[] Names of migrations rolled back
     */
    public function rollback(int $steps = 1): array
    {
        $ranMigrations = $this->repository->getRan();

        if (empty($ranMigrations)) {
            return [];
        }

        // Get last N migrations
        $toRollback = array_slice(array_reverse($ranMigrations), 0, $steps);
        $rolledBack = [];

        foreach ($toRollback as $migration) {
            $this->rollbackMigration($migration);
            $rolledBack[] = $migration;
        }

        return $rolledBack;
    }

    /**
     * Get migration status.
     *
     * @return array<array{name: string, status: string}>
     */
    public function status(): array
    {
        $allMigrations = $this->getMigrationFiles();
        $ran = $this->repository->getRan();
        $status = [];

        foreach ($allMigrations as $name => $file) {
            $status[] = [
                'name' => $name,
                'status' => in_array($name, $ran, true) ? 'Ran' : 'Pending',
            ];
        }

        return $status;
    }

    /**
     * Get pending migrations.
     *
     * @return string[]
     */
    public function getPending(): array
    {
        $files = $this->getMigrationFiles();
        $ran = $this->repository->getRan();

        return array_diff(array_keys($files), $ran);
    }

    /**
     * Run a single migration.
     *
     * @param string $migration Migration name
     * @return void
     */
    private function runMigration(string $migration): void
    {
        $instance = $this->resolveMigration($migration);
        $instance->setConnection($this->db);
        $instance->up();
        $this->repository->log($migration);
    }

    /**
     * Roll back a single migration.
     *
     * @param string $migration Migration name
     * @return void
     */
    private function rollbackMigration(string $migration): void
    {
        $instance = $this->resolveMigration($migration);
        $instance->setConnection($this->db);
        $instance->down();
        $this->repository->remove($migration);
    }

    /**
     * Resolve a migration to its instance.
     *
     * @param string $migration Migration name
     * @return Migration
     */
    private function resolveMigration(string $migration): Migration
    {
        $files = $this->getMigrationFiles();

        if (!isset($files[$migration])) {
            throw new RuntimeException("Migration not found: {$migration}");
        }

        require_once $files[$migration];

        // Convert filename to class name
        // E.g., 2024_01_01_000000_create_users_table -> CreateUsersTable
        $className = $this->getClassName($migration);

        if (!class_exists($className)) {
            throw new RuntimeException("Migration class not found: {$className}");
        }

        return new $className();
    }

    /**
     * Get all migration files.
     *
     * @return array<string, string> name => path
     */
    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');

        if ($files === false) {
            return [];
        }

        $migrations = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $migrations[$name] = $file;
        }

        ksort($migrations);

        return $migrations;
    }

    /**
     * Convert migration filename to class name.
     *
     * @param string $migration Migration name
     * @return string Class name
     */
    private function getClassName(string $migration): string
    {
        // Remove date prefix (2024_01_01_000000_)
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migration);

        // Convert to StudlyCase
        $name = str_replace('_', ' ', $name ?? $migration);
        $name = ucwords($name);

        return str_replace(' ', '', $name);
    }
}
