<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dioufmareme:entity:create', description: 'Crée ou met à jour une entité')]
class EntityCreateCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'Nom de l\'entité');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = [];
        if ($input->getArgument('name')) {
            $arguments['name'] = $input->getArgument('name');
        }

        return $this->getApplication()
            ->find('make:entity')
            ->run(new ArrayInput($arguments), $output);
    }
}