<?php

namespace Src\Entity;

use CoreDB;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\TableMapper;
use Src\Form\Widget\InputWidget;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

/**
 * Object relation with table files
 * @author murat
 */

class File extends TableMapper
{
    /**
    * File uploaded but not saved.
    * Temporary files must clear via a cronjob frequently.
    */
    public const STATUS_TEMPORARY = "temporary";
    /**
    * File uploaded and saved.
    */
    public const STATUS_PERMANENT = "permanent";

    public ShortText $file_name;
    public ShortText $file_path;
    public ShortText $file_size;
    public ShortText $mime_type;
    public ShortText $extension;
    
    /**
    * @var EnumaratedList $status
    * File is temporary or permanent.
    */
    public EnumaratedList $status;

    public bool $isImage;
    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "files";
    }

    public function map(array $array, bool $isConstructor = false)
    {
        if (@$array["file"]) {
            $file = File::get($array["file"]);
            $this->map($file->toArray());
            if (!$this->ID->getValue()) {
                $this->ID->setValue($file->ID->getValue());
            } else {
                \CoreDB::database()->delete(static::getTableName())
                ->condition("ID", $file->ID->getValue())
                ->execute();
            }
            $this->status->setValue(self::STATUS_PERMANENT);
        } else {
            parent::map($array);
            if (strpos($this->mime_type, "image/") !== false) {
                $this->isImage = true;
            }
        }
    }

    public function delete(): bool
    {
        if (parent::delete()) {
            $this->unlinkFile();
            return true;
        } else {
            return false;
        }
    }

    public static function clear()
    {
        return parent::clearTable(self::getTableName()) && CoreDB::cleanDirectory(getcwd() . "/files/uploaded");
    }

    public function unlinkFile()
    {
        $file_url = $this->getFilePath();
        if (is_file($file_url)) {
            unlink($file_url);
        }
    }

    public function getFilePath()
    {
        return getcwd() . "/files/uploaded/{$this->file_path}";
    }

    public function storeUploadedFile($table, $field_name, $fileInfo): bool
    {
        $this->file_name->setValue($fileInfo["name"]);
        $this->mime_type->setValue($fileInfo["type"]);
        $this->file_size->setValue($fileInfo["size"]);
        $this->extension->setValue(pathinfo($fileInfo["name"], PATHINFO_EXTENSION));
        if (strpos($this->mime_type, "image/")) {
            $this->isImage = true;
        }

        $file_url = getcwd() . "/files/uploaded/$table/$field_name/";
        is_dir($file_url) ?: mkdir($file_url, 0776, true);
        $file_path = "$table/$field_name/" . md5($fileInfo["tmp_name"] . \CoreDB::currentDate());
        $this->file_path->setValue($file_path);
        if (move_uploaded_file($fileInfo["tmp_name"], getcwd() . "/files/uploaded/{$this->file_path}")) {
            $this->save();
            return true;
        } else {
            return false;
        }
    }

    public function getUrl(): string
    {
        return BASE_URL . "/files/uploaded/{$this->file_path}";
    }

    /**
     * @inheritdoc
     */
    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        unset($headers["ID"], $headers["last_updated"]);
        unset($headers["file_path"], $headers["extension"]);
        return $headers;
    }
    /**
     * @inheritdoc
     */
    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $fields = parent::getSearchFormFields($translateLabel);
        unset($fields["ID"], $fields["last_updated"]);
        return $fields;
    }

    public function postProcessRow(&$row): void
    {
        parent::postProcessRow($row);
        $row["file_name"] = ViewGroup::create("a", "")
            ->addField(
                TextElement::create($row["file_name"])
            )->addAttribute("href", BASE_URL . "/files/uploaded/{$row["file_path"]}")
            ->addAttribute("target", "_blank");
        $row["status"] = Translation::getTranslation($row["status"]);
        $row["file_size"] = $this->sizeConvertToString($row["file_size"]);
        unset($row["ID"], $row["file_path"], $row["last_updated"], $row["extension"]);
    }

    public function sizeConvertToString($bytes)
    {
        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = ['B', 'KB', 'MB', 'GB'];
            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
        return $bytes;
    }

    public function getFormFields($name, bool $translateLabel = true): array
    {
        return [
            InputWidget::create("{$name}[file]")
            ->addFileKey($this->entityName, $this->ID->getValue(), "file")
            ->setType("file")
            ->setValue($this->ID)
            ->addClass("p-1 asyncronous")
        ];
    }
}
