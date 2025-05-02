<?php

namespace NixPHP\Database;

use NixPHP\Database\Core\Database;
use function NixPHP\app;

function database():? Database
{
    return app()->container()->get('database');
}