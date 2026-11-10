<?php

namespace Src\Command;

use Src\Entity\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "config:import")]
class ConfigurationImportCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription(
            Translation::getTranslation("config_import")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \CoreDB::config()->importTableConfiguration();
        Translation::importTranslations();
        $output->writeln(Translation::getTranslation("import_success"));
        return Command::SUCCESS;
    }
}
