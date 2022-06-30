$(function () {
    $(document).on("click", ".multiple-file-delete", function (e) {
        e.preventDefault();
        let button = $(this);
        alert({
            message: _t("record_remove_accept_field", [
                button.data("field-name")
            ]),
            okLabel: _t("yes"),
            callback: function () {
                $.ajax({
                    url: root + "/ajax/entityDelete",
                    method: "post",
                    data: { key: button.data("key") },
                    success: function () {
                        button.parents("tr").fadeOut(500).delay(5000, function () {
                            $(this).remove();
                        });
                    }
                })
            }
        });
    }).on("change", ".multiple-file-input input", function () {
        let inputButton = $(this).closest(".multiple-file-input");
        let name = inputButton.data("name");
        let entity = inputButton.data("entity");
        let fieldName = inputButton.data("field-name");
        let table = $(`table.multiple-files-content[data-name='${inputButton.data("name")}']`);
        for (file of this.files) {
            var form = $(this).closest("form");
            var formData = new FormData();
            formData.append('file', file);
            formData.append("form_build_id", form.find("#input_form_build_id").val());
            formData.append("form_id", form.find("#input_form_id").val());
            formData.append("key", $(this).data("key"));
            $.ajax({
                url: root + "/ajax/uploadFile",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function (response) {
                    let preview = "";
                    if (response.data.is_image) {
                        preview = `<div class="mw-50px">
                                <a href="#" class="image-preview" data-field-name="${fieldName}">
                                    <img src="${root + "/files/uploaded/" + response.data.file_path}" alt="${response.data.file_name}" class="img-thumbnail ms-2">
                                </a>
                            </div>`
                    } else {
                        preview = `<div class="mw-50px">
                            <span class="${response.data.icon_class} fs-2x ms-2 text-primary">
                            </span>
                        </div>`
                    };
                    table.append(`<tr>
                        <td>
                            ${preview}
                            <input type='hidden' name='${name}[${entity}]' value='${response.data.ID}'/>
                        </td>
                        <td>
                            <a href="${root + "/files/uploaded/" + response.data.file_path}">
                                ${response.data.file_name}
                            </a>
                        </td>
                        <td>
                            ${response.data.file_size}
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-light-danger multiple-file-delete" data-key="${response.data.remove_key}" data-field-name="${fieldName}">
                                <span class="">
                                    <span class="fa fa-trash"></span>
                                </span>
                            </a>
                        </td>
                    </tr>`);
                }
            });
        }
    })
})