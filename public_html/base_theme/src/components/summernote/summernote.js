import {$} from "../jquery/jquery";
import "bootstrap/js/src/tooltip";
import 'summernote/dist/summernote-bs4';
import 'summernote/dist/summernote-bs4.css';
import "./summernote.scss";
$(function ($) {
    $('.summernote').summernote({
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear', "fontsize", "fontname", "color"]],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', "undo", 'codeview', 'help']],
          ],
    });
})