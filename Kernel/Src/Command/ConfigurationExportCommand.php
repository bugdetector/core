<?php

namespace Src\Command;

use Src\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationExportCommand extends Command{
    protected static $defaultName = "config:export";

    protected function configure()
    {
        $this->setDescription(
            Translation::getTranslation("config_export")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \CoreDB::config()->exportTableConfiguration();
        Translation::exportTranslations();
        $output->writeln(Translation::getTranslation("export_success"));
        return Command::SUCCESS;
    }
}