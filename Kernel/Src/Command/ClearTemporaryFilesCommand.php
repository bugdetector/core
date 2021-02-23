<?php

namespace Src\Command;

use Exception;
use Src\Entity\File;
use Src\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearTemporaryFilesCommand extends Command
{
    protected static $defaultName = "clear:temporary-files";

    protected function configure()
    {
        $this->setDescription(
            Translation::getTranslation("clear_temporary_files")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \CoreDB::database()->update(File::getTableName(), [
            "status" => File::STATUS_PERMANENT
        ])->condition("status", null, "IS")
        ->execute();
        $expiredTemporaryFiles = \CoreDB::database()->select(File::getTableName(), "f")
        ->condition("created_at", date("Y-m-d H:i:s", strtotime("1 hour ago")), "<=")
        ->condition("status", File::STATUS_TEMPORARY)
        ->select("f", ["ID"])
        ->execute()->fetchAll(\PDO::FETCH_COLUMN);
        try {
            foreach ($expiredTemporaryFiles as $fileId) {
                $file = File::get($fileId);
                $file->delete();
            }
            $output->writeln(Translation::getTranslation("files_deleted", [count($expiredTemporaryFiles)]));
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }
}
