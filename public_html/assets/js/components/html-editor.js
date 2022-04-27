$(function(){
    window.loadHtmlEditor = function(element){
        return tinymce.init({
            target: element,
            toolbar: ['styleselect fontselect fontsizeselect',
                'undo redo | cut copy paste | bold italic forecolor backcolor | link image | alignleft aligncenter alignright alignjustify | bullist numlist | table | outdent indent | blockquote | code'],
            plugins : 'autosave link image table lists code',
            contextmenu: 'cut copy paste',
            branding: false,
            resize: true,
            min_height: 500,
            relative_urls: false,
            remove_script_host: false,
            forced_root_block: false,
            setup: function(editor) {
                editor.on('change keyup input', function () {
                    editor.save();
                });
            }
          });
    }
    $('.html-editor').each(function(i,el){
        loadHtmlEditor(el);
    })
})