<?php

declare(strict_types=1);

namespace NixPHP\Database\Commands;

use PDO;
use NixPHP\CLI\Core\Input;
use NixPHP\CLI\Core\Output;
use NixPHP\CLI\Exception\ConsoleException;
use NixPHP\CLI\Core\AbstractCommand;
use function NixPHP\app;
use function NixPHP\config;
use function NixPHP\Database\database;

class MigrateCommand extends AbstractCommand
{

    public const string NAME = 'db:migrate';

    private int $executedMigrations = 0;

    protected function configure(): void
    {
        $this
            ->setTitle('Execute Migrations')
            ->setDescription('Execute migrations in either direction.')
            ->addArgument('direction')
            ->addOption('name', 'n');
            //->addOption('force', 'f');
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int
     * @throws ConsoleException
     */
    public function run(Input $input, Output $output): int
    {
        $direction = $input->getArgument('direction');

        if (false === \in_array($direction, ['up', 'down'])) {
            throw new ConsoleException('Invalid direction given.');
        }

        $connection = database();

        if (null === $connection) {
            $output->writeLine('Database connection not found.');
            return static::ERROR;
        }

        $this->ensureMigrationTrackingIntegrity($connection, $output);
        $migrationsPath = app()->getBasePath() . '/app/Migrations';

        if (!is_dir($migrationsPath)) {
            $output->writeLine('Migrations directory does not exist.');
            return static::ERROR;
        }

        $files = array_diff(scandir($migrationsPath), ['.', '..']);

        $existingMigrations = $connection
            ->query('SELECT `name` from `migrations`')
            ->fetchAll(\PDO::FETCH_COLUMN);

        // Override files with a migration file from an argument option when given
        if ($input->getOption('name')) {
            $name  = $input->getOption('name')[0] . '.php';
            $files = [$name];
        }

        foreach ($files as $file) {
            $className = substr($file, 0, -4);
            $namespace = sprintf('\App\Migrations\%s', $className);

            if (
                ($direction !== 'down' && \in_array($className, $existingMigrations, true))
                || ($direction === 'down' && !\in_array($className, $existingMigrations, true))
                || false === class_exists($namespace)
            ) {
                continue;
            }

            $result = $this->executeMigration($namespace, $direction, $output);

            if (false === $result) {
                continue;
            }

            $output->writeLine(sprintf('✔ %s %s executed', $className, $direction));
        }

        if (0 === $this->executedMigrations) {
            $output->writeLine('No migrations executed.', 'warning');
        } else {
            $output->writeEmptyLine();
            $output->writeLine(
                sprintf(
                    '%d migration(s) successfully executed.',
                    $this->executedMigrations
                ),
                'ok'
            );
        }

        return self::SUCCESS;
    }

    /**
     * @param string $migration
     * @param string $direction
     * @param Output $output
     * @return bool
     */
    private function executeMigration(string $migration, string $direction, Output $output): bool
    {
        $connection = database();

        if (false === $connection instanceof PDO) {
            return false;
        }

        /** @var AbstractCommand $object */
        $object = new $migration();

        try {
            $object->$direction($connection);
        } catch (\PDOException $t) {
            $output->writeLine($t->getMessage() . ' in ' . $migration, 'error');
            throw $t;
        }


        ++$this->executedMigrations;

        $name = substr($migration, strrpos($migration, '\\') + 1);

        switch ($direction) {
            case 'up':
                $stmt = $connection->prepare("INSERT INTO `migrations` (`name`) VALUES (?)");
                $stmt->execute([$name]);
                break;
            case 'down':
                $stmt = $connection->prepare("DELETE FROM `migrations` WHERE `name` = ?");
                $stmt->execute([$name]);
        }

        return true;
    }

    private function ensureMigrationTrackingIntegrity(\PDO $connection, Output $output): void
    {
        try {
            $query = $connection->query('SELECT * FROM `migrations`');
            $query->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            $output->writeLine('(!) Creating migration table as it does not exist.', 'warning');
            $output->writeEmptyLine();

            if (config('database:driver') === 'sqlite') {

                $connection->exec(<<<SQL
                CREATE TABLE `migrations` (
                    id INTEGER,
                    name VARCHAR(32) NOT NULL,
                    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    executedAt DATETIME NULL,
                    CONSTRAINT
                        migration_pk
                        PRIMARY KEY (id)
                )
                SQL
                );

            } else {

                $connection->exec(<<<SQL
                CREATE TABLE `migrations` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(32) NOT NULL,
                    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    executedAt DATETIME NULL
                )
                SQL);

            }
        }

    }
}