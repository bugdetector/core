<?php

namespace Src\Controller;

use CoreDB\Kernel\ServiceController;
use Src\Entity\File;

class FilesController extends ServiceController
{
    public function checkAccess(): bool
    {
        return true;
    }
    
    public function uploaded()
    {
        $this->response_type = self::RESPONSE_TYPE_RAW;

        $filePathArray = $this->arguments;
        array_shift($filePathArray);
        $filePath = implode("/", $filePathArray);
        /** @var File */
        $file = File::get(["file_path" => $filePath]);
        if (!$file) {
            header('HTTP/1.0 404 Not Found');
            return;
        }
        // Set the content-type header
        header('Content-Type: ' . $file->mime_type);
        header("Content-Disposition: inline; filename={$file->file_name}");
        header('Content-Length: ' . filesize($file->getFilePath()));

        // Handle caching
        $fileModificationTime = gmdate('D, d M Y H:i:s', strtotime($file->last_updated)) . ' GMT';
        $headers = getallheaders();
        if (isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $fileModificationTime) {
            header('HTTP/1.1 304 Not Modified');
            return;
        }
        header('Last-Modified: ' . $fileModificationTime);

        // Read the file
        readfile($file->getFilePath());
        return null;
    }
}
