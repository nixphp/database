<?php

declare(strict_types=1);

namespace Tests\Unit;

use NixPHP\Database\Core\AbstractMigration;
use Tests\NixPHPTestCase;

class AbstractMigrationTest extends NixPHPTestCase
{
    public function testDefaultShouldRunReturnsTrue(): void
    {
        $migration = new class extends AbstractMigration {
            public function up(\PDO $connection): void
            {
            }

            public function down(\PDO $connection): void
            {
            }
        };

        $this->assertTrue($migration->shouldRun());
    }

    public function testCanOverrideShouldRun(): void
    {
        $migration = new class extends AbstractMigration {
            public function up(\PDO $connection): void
            {
            }

            public function down(\PDO $connection): void
            {
            }

            public function shouldRun(): bool
            {
                return false;
            }
        };

        $this->assertFalse($migration->shouldRun());
    }
}
