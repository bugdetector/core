$(function ($) {
    $(document).on("click", ".add-new-entity", function (e) {
        e.preventDefault();
        let button = $(this);
        let entityName = button.data("entity");
        let name = button.data("name");
        let hiddenFields = button.data("hidden-fields");
        let index = $(`.collapsible-widget-group[data-entity='${entityName}'] > div > div`).length;
        let saveText = button.data("save-text") ? button.data("save-text") : button.text();
        $.ajax({
            url: root + "/ajax/getEntityCard",
            method: "post",
            data: { entity: entityName, name: name, index: index, hiddenFields: hiddenFields },
            success: function (response) {
                response = $(response);
                let [modal, modalContent] = openModal(
                    button.text(),
                    response.find(".card-body").html(),
                    `<button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">${_t("cancel")}</button>
                    <button type="button" class="btn btn-primary btn-sm save-entity">${saveText}</button>`,
                    "modal-lg"
                );
                modalContent.find(".save-entity").on("click", function(){
                    response.find(".card-body").html("");
                    response.find(".card-body").append(modalContent.find(".modal-body"));
                    $(`.collapsible-widget-group[data-entity='${entityName}'] > div`).append(response);
                    modal.hide();
                });
            }
        });
    }).on("click", ".remove-entity", function (e) {
        e.preventDefault();
        let button = $(this);
        alert({
            message: _t("record_remove_accept"),
            okLabel: _t("yes"),
            callback: function callback() {
                button.closest(".card").fadeOut(500).delay(500, function () {
                    $(this).remove();
                })
            }
        });
    })
})