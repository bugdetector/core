<?php

namespace Src\Command;

use Src\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationImportCommand extends Command{
    protected static $defaultName = "config:import";

    protected function configure()
    {
        $this->setDescription(
            Translation::getTranslation("config_import")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \CoreDB::config()->importTableConfiguration();
        Translation::importTranslations();
        $output->writeln(Translation::getTranslation("import_success"));
        return Command::SUCCESS;
    }
}