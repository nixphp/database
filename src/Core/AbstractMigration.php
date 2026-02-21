<?php

declare(strict_types=1);

namespace NixPHP\Database\Core;

abstract class AbstractMigration implements MigrationInterface
{
    abstract public function up(\PDO $connection): void;

    abstract public function down(\PDO $connection): void;

    public function shouldRun(): bool
    {
        return true;
    }
}
