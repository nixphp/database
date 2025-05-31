<?php

namespace NixPHP\Database\Commands;

use NixPHP\Cli\Core\AbstractCommand;
use NixPHP\Cli\Core\Input;
use NixPHP\Cli\Core\Output;
use function NixPHP\app;

class MigrationCreateCommand extends AbstractCommand
{

    public const string NAME = 'db:migration:create';

    protected function configure(): void
    {
        $this
            ->setTitle('Create Migration')
            ->setDescription('Create a skeleton migration in your application.');
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int
     */
    public function run(Input $input, Output $output): int
    {
        $timestamp = time();
        $template  = $this->buildTemplate($timestamp);

        $directory = app()->getBasePath() . '/app/Migrations';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);;
        }

        $migrationsDir = realpath($directory);
        $filename      = sprintf('Migration%s.php', $timestamp);

        file_put_contents(sprintf('%s/%s', $migrationsDir, $filename), $template);

        $output->writeLine('Generated migration file successfully.', 'ok');

        return AbstractCommand::SUCCESS;
    }

    /**
     * @param int $timestamp
     * @return string
     */
    private function buildTemplate(int $timestamp): string
    {
        return <<<PHP
<?php

namespace App\Migrations;

use \PDO;

class Migration{$timestamp}
{

    /**
     * @param PDO \$connection
     */
    public function up(PDO \$connection): void
    {
        
    }

    /**
     * @param PDO \$connection
     */
    public function down(PDO \$connection): void
    {
        
    }
}

PHP;
    }

}