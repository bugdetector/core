<?php

class FileField extends FieldControl {
    private $filename = "";
    private $fileurl = "";
    public function __construct(string $name, string $filename, string $file_url = "") {
        parent::__construct($name);
        $this->setFileName($filename);
        $this->setFileURL($file_url);
    }
    
    public function setFileName(string $filename) {
        $this->filename = $filename;
    }

    public function setFileURL(string $fileurl) {
        $this->fileurl = $fileurl;
    }

    public function renderField(): string {
        return (isset($this->label) ? "<label>{$this->label}</label>" : "").
        ($this->fileurl ? "<div><a class='file' href='$this->fileurl' target='_blank'>$this->filename</a></div>" : "").
                "<div class='row'>
                    <div class='btn btn-success col-sm-4 col-md-2 file-field mx-3'>
                        "._t(121)."
                    </div>
                    <input type='file' name='$this->name' style='display: none;'/>
                    <div class='col-sm-6 col-md-9'>
                        <input class='file-path form-control' type='text' value='$this->filename' placeholder='"._t(113)."'/>
                    </div>
                </div>";
    }

}
