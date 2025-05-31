<?php

use NixPHP\Database\Commands\MigrateCommand;
use NixPHP\Database\Commands\MigrationCreateCommand;
use NixPHP\Database\Core\Database;
use function NixPHP\app;
use function NixPHP\config;

app()->container()->set('database', function() {

    $config = config('database');
    if (!$config) return null;

    $database = new Database($config);
    return $database->getConnection();

});

$commandRegistry = app()->container()->get('commandRegistry');
$commandRegistry->add(MigrateCommand::class);
$commandRegistry->add(MigrationCreateCommand::class);