$(function(){
    window.loadHtmlEditor = function(element){
        return tinymce.init({
            target: element,
            toolbar: ['styleselect fontselect fontsizeselect',
                'undo redo | cut copy paste | bold italic forecolor backcolor | link image | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | blockquote | code'],
            plugins : 'autosave link image lists code',
            branding: false,
            resize: true,
            min_height: 500,
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