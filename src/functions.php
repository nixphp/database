<?php

declare(strict_types=1);

namespace NixPHP\Database;

use NixPHP\Database\Core\Database;
use function NixPHP\app;

function database():? \PDO
{
    return app()->container()->get(Database::class)?->getConnection();
}