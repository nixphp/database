<?php

use NixPHP\Database\Core\Database;
use function NixPHP\app;
use function NixPHP\config;

app()->container()->set('database', function() {

    $config = config('database');
    if (!$config) return null;

    $database = new Database($config);
    return $database->getConnection();

});