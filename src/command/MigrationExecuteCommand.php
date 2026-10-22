<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dioufmareme:migration:execute', description: 'Crée la migration puis l\'exécute')]
class MigrationExecuteCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->getApplication();

        $make = new ArrayInput([]);
        $make->setInteractive(false);
        if ($app->find('make:migration')->run($make, $output) !== Command::SUCCESS) {
            return Command::FAILURE;
        }

        $migrate = new ArrayInput([]);
        $migrate->setInteractive(false);

        return $app->find('doctrine:migrations:migrate')->run($migrate, $output);
    }
}