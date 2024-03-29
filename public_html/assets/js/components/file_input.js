$(function(){
    $(document).on("change", "input[type='file'].asyncronous", function(e){
        var form = $(this).closest("form");
        var fileId = $(`[id='${$(this).data("file-for")}']`);
        var formData = new FormData();
        formData.append('file', this.files[0]);
        formData.append("form_build_id", form.find("#input_form_build_id").val());
        formData.append("form_id", form.find("#input_form_id").val());
        formData.append("key", fileId.data("key"));
        formData.append("label", fileId.data("label"));
        formData.append("name", fileId.attr("name"));
        $.ajax({
            url : root + "/ajax/saveFile",
            type : 'POST',
            data : formData,
            processData: false,
            contentType: false,
            success : function(response) {
                fileId.closest(".input_widget").replaceWith(response);
            }
     });
    }).on("click", ".image-preview", function(e){
        e.preventDefault();
        let img = $(this).children("img");
        let imageSource = "";
        if(img.length == 0){
            imageSource = $(this).attr("href");
        } else {
            imageSource = img.attr("src");
        }
        var lightbox = new FsLightbox();
        lightbox.props.sources = [imageSource];
        lightbox.open();
    })
})