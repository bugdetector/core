<?php

namespace Src\Command;

use Exception;
use Src\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends Command
{
    protected static $defaultName = "clear:cache";

    protected function configure()
    {
        $this->setDescription(
            Translation::getTranslation("clear_cache")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            \CoreDB::config()->clearCache();
            $output->writeln(Translation::getTranslation("cache_cleared"));
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }
}
