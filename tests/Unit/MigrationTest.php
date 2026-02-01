<?php

declare(strict_types=1);

namespace WPZylos\Framework\Migrations\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Database\Connection;
use WPZylos\Framework\Migrations\Migration;

/**
 * Test migration class.
 */
class CreateTestTable extends Migration
{
    public function up(): void
    {
        $this->create('test_table', [
            'id' => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
            'name' => 'varchar(255) NOT NULL',
        ], ['PRIMARY KEY (id)']);
    }

    public function down(): void
    {
        $this->drop('test_table');
    }
}

/**
 * Tests for Migration class.
 */
class MigrationTest extends TestCase
{
    private function createMockConnection(): Connection
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('wpdb')->willReturn($GLOBALS['wpdb']);
        $connection->method('charsetCollate')->willReturn('DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $connection->method('query')->willReturn(true);

        return $connection;
    }

    public function testMigrationCanBeInstantiated(): void
    {
        $migration = new CreateTestTable();

        $this->assertInstanceOf(Migration::class, $migration);
    }

    public function testUpMethodCanBeCalled(): void
    {
        $migration = new CreateTestTable();
        $migration->setConnection($this->createMockConnection());

        // Should not throw
        $migration->up();
        $this->assertTrue(true);
    }

    public function testDownMethodCanBeCalled(): void
    {
        $migration = new CreateTestTable();
        $migration->setConnection($this->createMockConnection());

        // Should not throw
        $migration->down();
        $this->assertTrue(true);
    }
}
