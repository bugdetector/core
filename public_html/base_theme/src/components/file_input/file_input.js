import "./file_input.scss";
$(function(){
    $(document).on("change", "input[type='file'].asyncronous", function(e){
        var form = $(this).closest("form");
        var fileId = $( document.getElementById($(this).data("file-for")) );
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
    })
})