<?php

use NixPHP\Database\Core\Database;
use function NixPHP\config;

$this->container->set('database', function() {

    $config = config('database');
    if (!$config) return null;

    $database = new Database($config);
    return $database->getConnection();

});