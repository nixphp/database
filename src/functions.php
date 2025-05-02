<?php

use PHPico\Database\Database;
use function PHPico\app;

function database(): Database
{
    return app()->container()->get('database');
}