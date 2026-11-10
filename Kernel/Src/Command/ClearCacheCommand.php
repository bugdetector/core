<?php

namespace Src\Command;

use Exception;
use Src\Entity\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "clear:cache")]
class ClearCacheCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription(
            Translation::getTranslation("clear_cache")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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
