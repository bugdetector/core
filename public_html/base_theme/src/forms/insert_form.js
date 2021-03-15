$(document).on("click", ".remove_accept", function (e) {
    e.preventDefault();
    alert({
        message: _t("record_remove_accept"),
        okLabel: _t("yes"),
        callback: function () {
            $("input[name='delete']").click();
        }
    });
}).on("click", ".entitydelete", function(e){
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
                data: {key: button.data("key")},
                success: function(){
                    button.parent().fadeOut();
                }
            })
        }
    });
}).on("click", ".image-preview", function(e){
    e.preventDefault();
    bootbox.dialog({
        title: $(this).data("field-name"),
        message: $(this).children().clone(),
        size: "xl",
        closeButton: false,
        buttons: {
            close : {
                label: _t("close"),
                className: "btn-primary"
            }
        }
    });
})