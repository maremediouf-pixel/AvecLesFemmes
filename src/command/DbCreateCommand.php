<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dioufmareme:db:create', description: 'Supprime la base si elle existe, puis la recrée')]
class DbCreateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->getApplication();

        $drop = new ArrayInput(['--if-exists' => true, '--force' => true]);
        $drop->setInteractive(false);
        if ($app->find('doctrine:database:drop')->run($drop, $output) !== Command::SUCCESS) {
            return Command::FAILURE;
        }

        $create = new ArrayInput([]);
        $create->setInteractive(false);

        return $app->find('doctrine:database:create')->run($create, $output);
    }
}