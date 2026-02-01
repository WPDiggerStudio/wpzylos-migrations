<?php

/**
 * PHPUnit bootstrap for migrations package.
 *
 * @phpcs:disable PSR1.Files.SideEffects
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define ABSPATH for dbDelta
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/');
}

// Mock dbDelta
if (!function_exists('dbDelta')) {
    function dbDelta(string $sql): array
    {
        return ['created' => true];
    }
}

// Define wpdb class stub if not exists
if (!class_exists('wpdb')) {
    class wpdb
    {
        public string $prefix = 'wp_';
        public int $insert_id = 0;
        public string $last_error = '';

        public function prepare(string $query, ...$args): string
        {
            return vsprintf(str_replace('%s', "'%s'", $query), $args);
        }

        public function get_var(string $query): ?string
        {
            return null;
        }

        public function query(string $query): bool
        {
            return true;
        }

        public function get_charset_collate(): string
        {
            return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        public function get_row(string $query): ?object
        {
            return null;
        }

        public function get_results(string $query): array
        {
            return [];
        }

        public function insert(string $table, array $data, ?array $format = null): bool
        {
            return true;
        }

        public function update(string $table, array $data, array $where, ?array $format = null, ?array $whereFormat = null): int
        {
            return 1;
        }

        public function delete(string $table, array $where, ?array $whereFormat = null): int
        {
            return 1;
        }
    }
}

// Set global wpdb instance
$GLOBALS['wpdb'] = new wpdb();
