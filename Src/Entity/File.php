<?php

namespace Src\Entity;

use CoreDB;
use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table blocked_ips
 * @author murat
 */

class File extends TableMapper
{
    const TABLE = "files";
    public $ID;
    public $file_name;
    public $file_path;
    public $file_size;
    public $mime_type;
    public $extension;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function get(array $filter) : ?File
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function getAll(array $filter) : array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear()
    {
        return parent::clearTable(self::TABLE) && CoreDB::cleanDirectory(getcwd()."/files/uploaded");
    }

    public function delete(): bool
    {
        return parent::delete();
    }

    public function unlinkFile(){
        $file_url = getcwd()."/files/uploaded/{$this->file_path}";
        if (is_file($file_url)) {
            unlink($file_url);
        }
    }

    public function storeUploadedFile($table, $field_name, $fileInfo) : bool
    {
        $this->file_name = $fileInfo["name"];
        $this->mime_type = $fileInfo["type"];
        $this->file_size = $fileInfo["size"];
        $this->extension = pathinfo($fileInfo["name"], PATHINFO_EXTENSION);
        
        $file_url = getcwd()."/files/uploaded/$table/$field_name/";
        is_dir($file_url) ?: mkdir($file_url, 0777, true);
        $this->file_path = "$table/$field_name/".md5($fileInfo["tmp_name"].\CoreDB::get_current_date());
        if(move_uploaded_file($fileInfo["tmp_name"], getcwd()."/files/uploaded/{$this->file_path}")){
            $this->save();
            return true;
        }else{
            return false;
        }
    }

    public function getUrl() : string{
        return BASE_URL."/files/uploaded/{$this->file_path}";
    }
}
