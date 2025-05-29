<?php

namespace NixPHP\Database\Commands;

use PDO;
use NixPHP\Cli\Core\Input;
use NixPHP\Cli\Core\Output;
use NixPHP\Cli\Exception\ConsoleException;
use NixPHP\Cli\Core\AbstractCommand;
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
            ->addOption('name', 'n')
            ->addOption('force', 'f');
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

        $this->ensureMigrationTrackingIntegrity($output);

        $connection     = database();
        $migrationsPath = realpath(__DIR__ . '/../Migrations');

        if (false === $migrationsPath) {
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

        $isForce = $input->getOption('force') !== null;

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
                $connection->exec("INSERT INTO `migrations` (`name`) VALUES ('" . $name . "')");
                break;
            case 'down':
                $connection->exec("DELETE FROM `migrations` WHERE `name`= '" . $name . "'");
        }

        return true;
    }

    private function ensureMigrationTrackingIntegrity(Output $output): void
    {
        $connection = database();
        try {
            $query = $connection->query('SELECT * FROM `migrations`');
            $query->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            $output->writeLine('(!) Creating migration table as it does not exist.', 'warning');
            $output->writeEmptyLine();

            $connection->exec(<<<SQL
            CREATE TABLE `migrations`(
                id INTEGER,
                name VARCHAR(32) NOT NULL,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                executedAt DATETIME NULL,
                CONSTRAINT
                    migration_pk
                    PRIMARY KEY (id)
            )
            SQL);
        }

    }
}