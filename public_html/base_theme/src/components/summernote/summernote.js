import {$} from "../jquery/jquery";
import 'summernote/dist/summernote-lite';
import 'summernote/dist/summernote-lite.css';
import "./summernote.scss";
$(function () {
    window.summernote = function(element){
        $(element).summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'strikethrough', 'clear', "fontsize", "fontname", "color"]],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['view', ['fullscreen', "undo", 'redo', 'codeview', 'help']],
            ],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36'],
        });
    }
    summernote($('.summernote'));
})