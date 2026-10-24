<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'dioufmareme:migration:execute', description: 'Crée la migration puis l\'exécute')]
class MigrationExecuteCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. Création de la migration (processus séparé)
        if (!$this->run(['make:migration', '--no-interaction'], $output)) {
            return Command::FAILURE;
        }

        // 2. Exécution de la migration (nouveau processus, il voit le nouveau fichier)
        if (!$this->run(['doctrine:migrations:migrate', '--no-interaction'], $output)) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function run(array $arguments, OutputInterface $output): bool
    {
        $process = new Process(
            array_merge([PHP_BINARY, 'bin/console'], $arguments),
            $this->projectDir
        );
        $process->setTimeout(null);
        $process->run(fn ($type, $buffer) => $output->write($buffer));

        return $process->isSuccessful();
    }
}