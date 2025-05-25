<?php

namespace NixPHP\Database;

use function NixPHP\app;

function database():? \PDO
{
    return app()->container()->get('database');
}