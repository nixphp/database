<?php

namespace PHPico\Database;

use PHPico\Database\Core\Database;
use function PHPico\app;

function database():? Database
{
    return app()->container()->get('database');
}