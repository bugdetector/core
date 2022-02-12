$(function(){
    window.loadHtmlEditor = function(element){
        let textarea = $(element);
        let div = $("<div class='quill-editor'>").html(textarea.val());
        textarea.hide().before(div);
        let editor = new Quill(div[0], {
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike', 'image', 'link'],        // toggled buttons
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                    [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean'],                                         // remove formatting button,
                    ['blockquote', 'code-block']
                ]
            },
            theme: 'snow' // or 'bubble'
        });
        editor.on('text-change', function(delta, oldDelta, source) {
            textarea.val(editor.getText());
        })
        return editor;
    }
    $('.html-editor').each(function(i,el){
        loadHtmlEditor(el);
    })
})