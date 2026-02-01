<?php

declare(strict_types=1);

namespace WPZylos\Framework\Migrations;

use WPZylos\Framework\Core\Contracts\ApplicationInterface;
use WPZylos\Framework\Core\ServiceProvider;
use WPZylos\Framework\Database\Connection;

/**
 * Migrations service provider.
 *
 * @package WPZylos\Framework\Migrations
 */
class MigrationsServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(ApplicationInterface $app): void
    {
        parent::register($app);

        $this->singleton(MigrationRepository::class, fn() => new MigrationRepository(
            $app->context(),
            is_multisite() && is_network_admin()
        ));

        $this->singleton(Migrator::class, fn() => new Migrator(
            $app->context(),
            $this->make(Connection::class),
            $this->make(MigrationRepository::class)
        ));
    }
}
