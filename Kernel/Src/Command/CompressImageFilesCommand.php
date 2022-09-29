<?php

namespace Src\Command;

use Exception;
use Src\Entity\File;
use Src\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompressImageFilesCommand extends Command
{
    protected static $defaultName = "image:compress";

    protected function configure()
    {
        $this->setDescription(
            Translation::getTranslation("compress_all_image_files")
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imageFileIds = \CoreDB::database()->select(File::getTableName(), "f")
        ->condition("f.mime_type", 'image%', "LIKE")
        ->select("f", ["ID"])
        ->execute()->fetchAll(\PDO::FETCH_COLUMN);
        $count = 0;
        $fail = 0;
        foreach ($imageFileIds as $fileId) {
            /** @var File */
            $file = File::get($fileId);
            if (in_array($file->mime_type->getValue(), ["image/png", "image/jpeg"])) {
                try {
                    $filePath = "public_html/files/uploaded/{$file->file_path}";
                    if ($file->mime_type->getValue() == 'image/jpeg') {
                        $image = imagecreatefromjpeg($filePath);
                        if (!$image) {
                            throw new Exception("Image create failed.");
                        }
                        imagejpeg($image, $filePath, 35);
                    } elseif ($file->mime_type->getValue() == 'image/png') {
                        $image = imagecreatefrompng($filePath);
                        if (!$image) {
                            throw new Exception("Image create failed.");
                        }
                        if (filesize($filePath) > 204800) { // 20 kB
                            imagejpeg($image, $filePath, 35);
                            $file->mime_type->setValue("image/jpeg");
                        } else {
                            imagesavealpha($image, true);
                            imagepng($image, $filePath, 9);
                        }
                    }
                    $file->file_size->setValue(filesize($filePath));
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
