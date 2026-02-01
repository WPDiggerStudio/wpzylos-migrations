<?php

declare(strict_types=1);

namespace WPZylos\Framework\Migrations;

use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * Migration repository.
 *
 * Tracks which migrations have been run using wp_options or wp_sitemeta.
 *
 * @package WPZylos\Framework\Migrations
 */
class MigrationRepository
{
    /**
     * @var ContextInterface Plugin context
     */
    private ContextInterface $context;

    /**
     * @var bool Whether to use network storage
     */
    private bool $networkActivated;

    /**
     * Create repository.
     *
     * @param ContextInterface $context Plugin context
     * @param bool $networkActivated Use network storage
     */
    public function __construct(ContextInterface $context, bool $networkActivated = false)
    {
        $this->context = $context;
        $this->networkActivated = $networkActivated;
    }

    /**
     * Get an option key for migration state.
     *
     * @return string
     */
    private function getOptionKey(): string
    {
        return $this->context->optionKey('migrations');
    }

    /**
     * Get all run migrations.
     *
     * @return string[]
     */
    public function getRan(): array
    {
        if ($this->networkActivated && is_multisite()) {
            $ran = get_site_option($this->getOptionKey(), []);
        } else {
            $ran = get_option($this->getOptionKey(), []);
        }

        return is_array($ran) ? $ran : [];
    }

    /**
     * Log that a migration was run.
     *
     * @param string $migration Migration name
     * @return void
     */
    public function log(string $migration): void
    {
        $ran = $this->getRan();
        $ran[] = $migration;
        $ran = array_unique($ran);

        if ($this->networkActivated && is_multisite()) {
            update_site_option($this->getOptionKey(), $ran);
        } else {
            update_option($this->getOptionKey(), $ran, false);
        }
    }

    /**
     * Remove a migration from the log.
     *
     * @param string $migration Migration name
     * @return void
     */
    public function remove(string $migration): void
    {
        $ran = $this->getRan();
        $ran = array_values(array_diff($ran, [$migration]));

        if ($this->networkActivated && is_multisite()) {
            update_site_option($this->getOptionKey(), $ran);
        } else {
            update_option($this->getOptionKey(), $ran, false);
        }
    }

    /**
     * Get the last batch number.
     *
     * @return int
     */
    public function getLastBatch(): int
    {
        $key = $this->context->optionKey('migrations_batch');

        if ($this->networkActivated && is_multisite()) {
            return (int) get_site_option($key, 0);
        }

        return (int) get_option($key, 0);
    }

    /**
     * Increment batch number.
     *
     * @return int New batch number
     */
    public function incrementBatch(): int
    {
        $batch = $this->getLastBatch() + 1;
        $key = $this->context->optionKey('migrations_batch');

        if ($this->networkActivated && is_multisite()) {
            update_site_option($key, $batch);
        } else {
            update_option($key, $batch, false);
        }

        return $batch;
    }

    /**
     * Clear all migration state.
     *
     * @return void
     */
    public function clear(): void
    {
        $optionKey = $this->getOptionKey();
        $batchKey = $this->context->optionKey('migrations_batch');

        if ($this->networkActivated && is_multisite()) {
            delete_site_option($optionKey);
            delete_site_option($batchKey);
        } else {
            delete_option($optionKey);
            delete_option($batchKey);
        }
    }
}
