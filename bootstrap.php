<?php

declare(strict_types=1);

use NixPHP\CLI\Support\CommandRegistry;
use NixPHP\Database\Commands\MigrateCommand;
use NixPHP\Database\Commands\MigrationCreateCommand;
use NixPHP\Database\Core\Database;
use function NixPHP\app;
use function NixPHP\config;

app()->container()->set(Database::class, function() {
    $config = config('database');
    if (!$config) return null;
    $database = new Database($config);

    return $database->getConnection();
});

if (app()->hasPlugin('nixphp/cli')) {
    $commandRegistry = app()->container()->get(CommandRegistry::class);
    $commandRegistry->add(MigrateCommand::class);
    $commandRegistry->add(MigrationCreateCommand::class);
}