<?php

class MultipleFileInput extends FieldControl {
    private $objects = [];
    private $file_field_name;
    private $file_name_field_name;

    private $wrapper_class = "col-sm-3";
    /**
     * $file_field_name name for frontend
     * $objects DBObject array
     */
    public function __construct(string $file_field_name, string $file_name_field_name,array $objects = NULL)
    {
        $this->file_field_name = $file_field_name;
        $this->file_name_field_name = $file_name_field_name;
        $this->objects = $objects;

        $this->removeClass("form-control");
        $this->addClass("multiple-file-input-section");
    }

    public function setWrapperClass(string $class){
        $this->wrapper_class = $class;
    }

    public function renderField() : string {
        $render = "<div class='".$this->renderClasses()."' ".$this->renderAttributes().">";
        $render .= "<h3 class='col-sm-12'>{$this->label}</h3>";
        $render .= "<div class='col-sm-12 file-list'>";
        $index = 0;
        foreach($this->objects as $index => $object){
            $file_name = $object->{$this->file_name_field_name};
            $render .= "<div class='{$this->wrapper_class}'>";
            $render .= $this->getFileIcon($file_name);
            $render .= "<a href='".$object->get_file_url_for_field($this->file_field_name)."' download='$file_name'>$file_name</a>";
            $render .= "</div>";
        }
        $render .= "</div>";
        $render .= "<div class='col-sm-6 multiple-file-field' data-file-index='".($index+1)."' data-name='{$this->name}' data-wrapper='{$this->wrapper_class}'>
                        <div class='btn btn-success file-field'>
                            "._t(111)."
                        </div>
                        <input type='file' name='{$this->name}[".($index+1)."]' style='display: none;'/>
                    </div>
                </div>";
        return $render;
    }

    public function getFileIcon(string $file_name){
        $fsize = 25; //icon px width in output
        switch (@pathinfo($file_name)['extension']) {
            case 'pdf':
                $img = 'http://cdn1.iconfinder.com/data/icons/CrystalClear/128x128/mimetypes/pdf.png';
                break;
            case 'doc':
            case 'docx':
                $img = 'http://cdn2.iconfinder.com/data/icons/sleekxp/Microsoft%20Office%202007%20Word.png';
                break;
            case 'txt':
                $img = 'http://cdn1.iconfinder.com/data/icons/CrystalClear/128x128/mimetypes/txt2.png';
                break;
            case 'xls':
            case 'xlsx':
            case 'xlsm':
                $img = 'http://cdn2.iconfinder.com/data/icons/sleekxp/Microsoft%20Office%202007%20Excel.png';
                break;
            case 'ppt':
            case 'pptx':
                $img = 'http://cdn2.iconfinder.com/data/icons/sleekxp/Microsoft%20Office%202007%20PowerPoint.png';
                break;
            case 'mp3':
                $img = 'http://cdn2.iconfinder.com/data/icons/oxygen/128x128/mimetypes/audio-x-pn-realaudio-plugin.png';
                break;
            case 'wmv':
            case 'mp4':
            case 'mpeg':
                $img = 'http://cdn4.iconfinder.com/data/icons/Pretty_office_icon_part_2/128/video-file.png';
                break;
            case 'html':
                $img = 'http://cdn1.iconfinder.com/data/icons/nuove/128x128/mimetypes/html.png';
                break;
            case "jpg":
            case "jpeg":
            case "png":
                $img = "https://cdn2.iconfinder.com/data/icons/pittogrammi/142/32-256.png";
                break;
            default:
                $img = 'https://cdn0.iconfinder.com/data/icons/documents-50/32/undefined-document-256.png';
                break;
        }   
        
        return "<img src='$img' width='$fsize' />";
    }
}