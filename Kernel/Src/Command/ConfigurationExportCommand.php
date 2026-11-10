<?php

namespace Src\Command;

use Src\Entity\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "config:export")]
class ConfigurationExportCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription(
            Translation::getTranslation("config_export")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \CoreDB::config()->exportTableConfiguration();
        Translation::exportTranslations();
        $output->writeln(Translation::getTranslation("export_success"));
        return Command::SUCCESS;
    }
}
