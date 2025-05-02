<?php

use NixPHP\Database\Core\Database;
use function NixPHP\config;

include_once __DIR__ . '/vendor/autoload.php';

$this->container->set('database', function() {

    $config = config('database');
    if (!$config) return null;

    $database = new Database($config);
    return $database->getConnection();

});