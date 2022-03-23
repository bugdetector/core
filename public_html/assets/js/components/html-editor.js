$(function(){
    window.loadHtmlEditor = function(element){
        return tinymce.init({
            target: element,
            toolbar: ['styleselect fontselect fontsizeselect',
                'undo redo | cut copy paste | bold italic forecolor backcolor | link image | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | blockquote | code'],
            plugins : 'link image lists code'
          });
    }
    $('.html-editor').each(function(i,el){
        loadHtmlEditor(el);
    })
})