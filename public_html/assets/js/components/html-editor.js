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
        let toolbar = editor.getModule("toolbar");
        toolbar.addHandler("code-block", function(value){
            let htmlContent = $("<textarea>").addClass("form-control bg-dark text-light min-h-200px").val(editor.root.innerHTML);
            [modal, modalContent] = openModal(
                "HTML",
                htmlContent,
                `<button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">${_t("cancel")}</button>
                <button type="button" class="btn btn-primary btn-sm save-code-block">${_t("save")}</button>`,
                "modal-lg"
            );
            modalContent.on("click", ".save-code-block", function(){
                editor.root.innerHTML = htmlContent.val();
                textarea.val(editor.root.innerHTML);
                modal.hide();
            })
        });
        editor.on('text-change', function(delta, oldDelta, source) {
            textarea.val(editor.root.innerHTML);
        })
        return editor;
    }
    $('.html-editor').each(function(i,el){
        loadHtmlEditor(el);
    })
})