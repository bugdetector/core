$(function () {
    window.loadHtmlEditor = function (element) {
        let fileKey = $(element).data("key");
        return tinymce.init({
            target: element,
            toolbar: ['styleselect fontselect fontsizeselect',
                'undo redo | cut copy paste | bold italic forecolor backcolor | link image | alignleft aligncenter alignright alignjustify | bullist numlist | table codesample | outdent indent | blockquote | code visualblocks fullscreen'],
            plugins: 'autosave link image imagetools table lists code codesample visualblocks fullscreen',
            contextmenu: 'cut copy paste',
            branding: false,
            resize: true,
            min_height: 500,
            relative_urls: true,
            remove_script_host: false,
            convert_urls : false,
            forced_root_block: false,
            image_title: true,
            automatic_uploads: true,
            verify_html: false,
            force_p_newlines: true,
            images_upload_handler: function (blobInfo, success, failure) {
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append("key", fileKey);
                $.ajax({
                    url: root + "/ajax/uploadFileForTextarea",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    success: function (response) {
                        success(root + "/files/uploaded/" + response.data.file_path)
                    }
                });
            },
            setup: function (editor) {
                editor.on('change keyup input', function () {
                    editor.save();
                });
            }
        });
    }
    $('.html-editor').each(function (i, el) {
        loadHtmlEditor(el);
    })
})