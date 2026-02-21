<?php

declare(strict_types=1);

use NixPHP\CLI\Support\CommandRegistry;
use NixPHP\Database\Commands\MigrateCommand;
use NixPHP\Database\Commands\MigrationCreateCommand;
use NixPHP\Database\Core\Database;
use NixPHP\Database\Support\MigrationRegistry;
use function NixPHP\app;
use function NixPHP\config;

app()->container()->set(Database::class, function() {
    $config = config('database');
    if (!$config) return null;
    return new Database($config);
});

MigrationRegistry::addPath(app()->getBasePath() . '/app/Migrations');

/** @var mixed $migrationPaths */
$migrationPaths = config('database.migration_paths');

if (is_array($migrationPaths)) {
    foreach ($migrationPaths as $path) {
        MigrationRegistry::addPath($path);
    }
}

if (app()->hasPlugin('nixphp/cli')) {
    $commandRegistry = app()->container()->get(CommandRegistry::class);
    $commandRegistry->add(MigrateCommand::class);
    $commandRegistry->add(MigrationCreateCommand::class);
}