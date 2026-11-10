<?php

namespace Src\Command;

use Exception;
use Src\Entity\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "cdb:serve")]
class ServeCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription(
            Translation::getTranslation("Serve CoreDB")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            exec('php -S localhost:8000 -t public_html');
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }
}
