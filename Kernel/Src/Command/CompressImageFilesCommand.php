<?php

namespace Src\Command;

use Exception;
use Src\Entity\File;
use Src\Entity\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "image:compress")]
class CompressImageFilesCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription(
            Translation::getTranslation("compress_all_image_files")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = \CoreDB::database()->select(File::getTableName(), "f")
            ->condition("f.mime_type", ["image/png", "image/jpeg", "image/jpg"])
            ->select("f", ["ID"]);
        $compressedCondition = \CoreDB::database()->condition($query)
            ->condition("f.is_compressed", 0)
            ->condition("f.is_compressed", null, "IS", "OR");
        $query->condition($compressedCondition);
        $imageFileIds = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
        $count = 0;
        $fail = 0;
        foreach ($imageFileIds as $fileId) {
            /** @var File */
            $file = File::get($fileId);
            if (in_array($file->mime_type->getValue(), ["image/png", "image/jpeg", "image/jpg"])) {
                try {
                    $filePath = "public_html/files/uploaded/{$file->file_path}";
                    if (!is_file($filePath)) {
                        throw new Exception("File not found.");
                    }
                    if (
                        $file->mime_type->getValue() == 'image/jpeg' ||
                        $file->mime_type->getValue() == 'image/jpg'
                    ) {
                        $image = imagecreatefromjpeg($filePath);
                        if (!$image) {
                            throw new Exception("Image create failed.");
                        }
                        if (!imagejpeg($image, $filePath)) {
                            throw new Exception("Image save failed.");
                        }
                    } elseif ($file->mime_type->getValue() == 'image/png') {
                        $image = imagecreatefrompng($filePath);
                        if (!$image) {
                            throw new Exception("Image create failed.");
                        }
                        imagesavealpha($image, true);
                        if (!imagepng($image, $filePath, 9)) {
                            throw new Exception("Image save failed.");
                        }
                    }
                    $file->file_size->setValue(filesize($filePath));
                    $file->is_compressed->setValue(true);
                    $file->save();
                    $count++;
                } catch (Exception $ex) {
                    $output->writeln("<error>"
                        . Translation::getTranslation("an_error_occured") . " :" . $ex->getMessage() . "\n" .
                        json_encode(
                            $file->toArray()
                        ) .
                    "</error>");
                    $fail++;
                }
            }
        }
        $output->writeln("<info>"
        . Translation::getTranslation("_files_compressed", [$count]) .
        "</info>");
        return Command::SUCCESS;
    }
}
